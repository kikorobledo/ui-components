<?php

use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tag Input with Autocomplete')] #[Layout('layouts::base')]
class extends Component {

    public string $search = '';
    public array $suggestions = [];
    public array $selectedTags = [];

    public function updatedSearch(): void
    {

        $this->loadSuggestions();

    }

    public function loadSuggestions(): void
    {

        if (strlen($this->search) < 1) {

            $this->suggestions = [];

            return;

        }

        $this->suggestions = Tag::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->whereNotIn('id', collect($this->selectedTags)->pluck('id')->toArray())
            ->limit(5)
            ->select('id', 'name')
            ->get()
            ->toArray();
    }

    public function addTag(int $id): void
    {

        $tag = Tag::find($id);

        if ($tag && ! collect($this->selectedTags)->contains('id', $id)) {

            $this->selectedTags[] = ['id' => $tag->id, 'name' => $tag->name];

        }

        $this->search = '';

        $this->suggestions = [];

    }

    public function removeTag(int $id): void
    {

        $this->selectedTags = collect($this->selectedTags)
            ->reject(fn ($tag) => $tag['id'] === $id)
            ->values()
            ->toArray();

    }

    public function createTag(): void
    {

        $name = ucfirst(trim($this->search));

        if (empty($name)) {

            return;

        }

        $existing = Tag::query()
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->first();

        if ($existing) {

            $this->addTag($existing->id);

            return;

        }

        $tag = Tag::create(['name' => $name]);

        $this->selectedTags[] = ['id' => $tag->id, 'name' => $tag->name];

        $this->search = '';

        $this->suggestions = [];

        $this->dispatch('toast', message: "Tag \"{$tag->name}\" created!", type: 'success');

    }

};
?>

<div class="min-h-screen bg-gray-50 py-12">

    <div class="max-w-xl mx-auto px-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            <h1 class="text-xl font-semibold text-gray-900 mb-1">Tag Input</h1>

            <p class="text-sm text-gray-500 mb-6">Add tags to categorize your content</p>

            <!-- Selected Tags -->
            @if(count($selectedTags) > 0)

                <div class="flex flex-wrap gap-2 mb-4">

                    @foreach($selectedTags as $tag)

                        <span
                            wire:key="tag-{{ $tag['id'] }}"
                            class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-sm font-medium px-3 py-1.5 rounded-full"
                        >

                            {{ $tag['name'] }}

                            <button
                                type="button"
                                wire:click="removeTag({{ $tag['id'] }})"
                                class="text-blue-400 hover:text-blue-600 transition-colors"
                            >

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>

                            </button>

                        </span>

                    @endforeach

                </div>

            @endif

            <div
                x-data="{
                    open: false,
                    highlightedIndex: -1,
                    moveUp() {
                        if (this.highlightedIndex > -1) {
                            this.highlightedIndex--
                        }
                    },
                    moveDown() {
                        const max = $wire.search && !$wire.suggestions.some(s => s.name === $wire.search)
                            ? $wire.suggestions.length
                            : $wire.suggestions.length - 1
                        if (this.highlightedIndex < max) {
                            this.highlightedIndex++
                        }
                    }
                }"
                x-init="$watch('$wire.search', (value) => {
                    open = value.length > 0
                    highlightedIndex = -1
                })"
                @click.outside="open = false"
            >
                <!-- Tag Input -->
                <div class="relative">

                    <input
                        type="text"
                        wire:model.live.debounce="search"
                        @keydown.escape="open = false"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.enter.prevent="
                            if (highlightedIndex < $wire.suggestions.length) {
                                $wire.addTag($wire.suggestions[highlightedIndex].id)
                            } else {
                                $wire.createTag()
                            }
                            open = false
                            highlightedIndex = -1"
                        @focus="open = $wire.search.length > 0"
                        placeholder="Type to search or create tags..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >

                    <!-- Dropdown -->
                    <div
                        x-show="open && $wire.search.length > 0"
                        class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">

                        <ul class="py-1">

                            @foreach($suggestions as $index => $suggestion)

                                <li
                                    wire:key="suggestion-{{ $suggestion['id'] }}"
                                    wire:click="addTag({{ $suggestion['id'] }})"
                                    @mouseenter="highlightedIndex = {{ $index }}"
                                    :class="highlightedIndex === {{ $index }} ? 'bg-blue-50 text-blue-700' : 'text-gray-700'"
                                    class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 hover:text-blue-700 transition-colors"
                                >
                                    {{ $suggestion['name'] }}
                                </li>

                            @endforeach

                        </ul>

                        @if($search && !collect($suggestions)->contains('name', $search))

                            <div
                                wire:click="createTag"
                                @mouseenter="highlightedIndex = $wire.suggestions.length"
                                :class="highlightedIndex === $wire.suggestions.length ? 'bg-blue-50 text-blue-700' : 'text-gray-600'"
                                class="px-4 py-2.5 cursor-pointer border-t border-gray-100 hover:bg-blue-50 hover:text-blue-700 transition-colors flex items-center gap-2"
                            >

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>

                                Create "<span class="font-medium text-gray-900">{{ $search }}</span>"

                            </div>

                        @endif

                    </div>

                </div>

            </div>

            <p class="text-xs text-gray-400 mt-3">
                Press <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500 font-mono">Enter</kbd> to select or
                create a tag
            </p>

        </div>

    </div>

    <x-toast />

</div>
