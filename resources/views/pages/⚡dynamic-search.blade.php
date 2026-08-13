<?php

use App\Helpers\Search;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dynamic Search')] #[Layout('layouts::base')]class extends Component
{

    public string $search = '';

    #[Session]
    public array $recentSearches = [];

    #[Computed]
    public function results(): Collection
    {

        if (strlen($this->search) < 2) {

            return collect();

        }

        return Post::query()
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhere('excerpt', 'like', "%{$this->search}%")
                    ->orWhere('content', 'like', "%{$this->search}%")
                    ->orWhere('author_name', 'like', "%{$this->search}%");
            })
            ->latest('published_at')
            ->limit(10)
            ->get();

    }

    #[Renderless]
    public function addToRecentSearches($term)
    {

        if (strlen($term) < 2) return;

        $this->recentSearches = collect([$term, ...$this->recentSearches])
            ->unique(fn($t) => strtolower($t))
            ->take(5)
            ->values()
            ->all();

    }

    #[Renderless]
    public function clearRecentSearches(): void
    {

        $this->recentSearches = [];

    }

    public function highlightMatch(string $text): string
    {

        return Search::highlightMatch($text, $this->search);

    }

    public function getSnippet(Post $post): string
    {

        if (stripos($post->excerpt, $this->search) !== false) {

            return Str::limit($post->excerpt, 100);

        }

        return Str::excerpt($post->content, $this->search, ['radius' => 50])
            ?? Str::limit($post->excerpt, 100);
    }

};
?>
<div
    class="min-h-screen bg-gray-50 py-12"
    x-data="{
        open: false,
        highlightedIndex: -1,

        init() {
            this.$watch('$wire.search', () => {
                this.highlightedIndex = -1
            })
        },

        moveUp() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--
                this.scrollToHighlighted()
            }
        },

        moveDown() {
            if (this.$refs.resultsList?.children[this.highlightedIndex + 1]) {
                this.highlightedIndex++
                this.scrollToHighlighted()
            }
        },

        scrollToHighlighted() {
            this.$nextTick(() => {
                this.$refs.resultsList?.children[this.highlightedIndex]
                    ?.scrollIntoView({ block: 'nearest' })
            })
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0) {
                const selected = this.$refs.resultsList?.children[this.highlightedIndex]
                $wire.addToRecentSearches($wire.search)
                this.open = false
            }
        },

        useRecentSearch(term) {
            $wire.set('search', term)
            {{-- $wire.addToRecentSearches(term) --}}
            this.open = true
        }
    }"
>

    <div class="max-w-xl mx-auto px-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            <h1 class="text-xl font-semibold text-gray-900 mb-1">Dynamic Search</h1>

            <p class="text-sm text-gray-500 mb-6">Search posts with real-time results</p>

            <!-- Search Input -->
            <div class="relative" @click.outside="open = false">

                <div class="relative">

                    <x-icon.search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"/>

                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        @focus="open = true"
                        @keydown.escape="open = false"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.enter.prevent="selectHighlighted()"
                        placeholder="Search posts..."
                        class="w-full pl-10 pr-20 py-2 border border-gray-300 rounded-lg text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >

                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">

                        <!-- Loading spinner -->
                        <div wire:loading wire:target="search">

                            <x-icon.spinner class="w-5 h-5 text-gray-400"/>

                        </div>

                        <!-- Clear button -->
                        <button
                            x-show="$wire.search.length > 0"
                            x-cloak
                            @click="$wire.set('search', ''); open = false"
                            type="button"
                            class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                        >

                            <x-icon.close/>

                        </button>

                    </div>

                </div>

                <!-- Dropdown -->
                <div
                    x-show="open"
                    x-transition
                    class="absolute z-10 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">

                    @if(strlen($search) < 2)

                        <!-- Recent Searches or empty state -->
                        <template x-if="$wire.recentSearches.length > 0">

                            <div>

                                <div class="flex items-center justify-between px-4 py-2 bg-gray-50 border-b border-gray-100">

                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recent Searches</span>

                                    <button
                                        @click="$wire.clearRecentSearches()"
                                        type="button"
                                        class="text-xs text-gray-400 hover:text-gray-600 transition-colors"
                                    >
                                        Clear
                                    </button>

                                </div>

                                <ul class="py-1">

                                    <template x-for="(term, index) in $wire.recentSearches" :key="index">

                                        <li
                                            @click="useRecentSearch(term)"
                                            class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors">

                                            <x-icon.clock class="text-gray-400" />

                                            <span class="text-gray-700 text-sm" x-text="term"></span>

                                        </li>

                                    </template>

                                </ul>

                            </div>

                        </template>

                        <template x-if="$wire.recentSearches.length === 0">

                            <div class="px-4 py-6 text-center">

                                <x-icon.search class="w-10 h-10 mx-auto text-gray-300 mb-2" />

                                <p class="text-sm text-gray-500">Type at least 2 characters to search</p>

                            </div>

                        </template>

                    @elseif($this->results->count() > 0)

                        <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">

                            <span class="text-xs font-medium text-gray-500">
                                {{ $this->results->count() }} {{ Str::plural('result', $this->results->count()) }}
                            </span>

                        </div>

                        <ul x-ref="resultsList" class="max-h-80 overflow-y-auto py-1">

                            @foreach($this->results as $post)

                                <li
                                    wire:key="result-{{ $post->id }}"
                                    @mouseenter="highlightedIndex = {{ $loop->index }}"
                                    @click="selectHighlighted()"
                                    class="px-4 py-3 cursor-pointer transition-colors border-b border-gray-100 last:border-0"
                                    :class="highlightedIndex === {{ $loop->index }} ? 'bg-blue-50' : 'hover:bg-gray-50'">

                                    <h3 class="font-medium text-gray-900 truncate">
                                        {!! $this->highlightMatch($post->title) !!}
                                    </h3>

                                    <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">
                                        {!! $this->highlightMatch($this->getSnippet($post)) !!}
                                    </p>

                                    <div class="flex items-center gap-2 mt-1.5">

                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">

                                            {{ $post->category }}
                                        </span>

                                        <span class="text-xs text-gray-400">
                                            by {!! $this->highlightMatch($post->author_name) !!}
                                        </span>

                                    </div>

                                </li>

                            @endforeach

                        </ul>

                        <!-- Footer with keyboard hints -->
                        <div class="flex items-center justify-between px-4 py-2 bg-gray-50 border-t border-gray-100 text-xs text-gray-500">

                            <div class="flex items-center gap-3">

                                <span class="flex items-center gap-1">

                                    <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded font-mono">↑</kbd>
                                    <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded font-mono">↓</kbd>
                                    navigate

                                </span>

                                <span class="flex items-center gap-1">

                                    <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded font-mono">↵</kbd>
                                    select

                                </span>

                            </div>

                            <span class="flex items-center gap-1">

                                <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded font-mono">esc</kbd>
                                close

                            </span>

                        </div>

                    @else

                        <div class="px-4 py-8 text-center">

                            <x-icon.emoji-sad class="mx-auto text-gray-300 mb-3"/>

                            <p class="text-gray-500">No results found for "{{ $search }}"</p>

                            <p class="text-sm text-gray-400 mt-1">Try searching for something else</p>

                        </div>

                    @endif

                </div>

            </div>

            <p class="text-xs text-gray-400 mt-3">
                Search across titles, excerpts, content, and authors
            </p>

        </div>

    </div>

</div>
