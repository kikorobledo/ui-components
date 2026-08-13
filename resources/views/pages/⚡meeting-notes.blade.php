<?php

use App\Models\Meeting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inline Editing')] #[Layout('layouts::base')]class extends Component
{

    public $meeting;
    public $title;
    public $notes;

    public function mount():void
    {

        $this->meeting = Meeting::first();

        $this->title = $this->meeting->title;

        $this->notes = $this->meeting->notes;

    }

    public function updated($property):void
    {

        if(in_array($property, ['title', 'notes'])){

            $this->meeting->update([$property => $this->$property]);

            $this->dispatch('notes-saved');

        }

    }

};
?>

<div>

    <div
        x-data="{saved: false, timeout: null}"
        @notes-saved.window="
            clearTimeout(timeout);
            saved = true;
            timeout = setTimeout(() => saved = false, 3000)"
        class="min-h-screen bg-white">

        {{-- Header --}}
        <div class="border-b border-gray-200">

            <div class="max-w-3xl mx-auto p-6">

                <textarea
                    rows="1"
                    x-data="{
                        resize(){
                            this.$el.style.height = 'auto';
                            this.$el.style.height = this.$el.scrollHeight + 'px';
                        }
                    }"
                    x-init="resize()"
                    @input="resize()"
                    wire.ignore.self
                    class="text-xl font-semibold text-gray-900 w-full border-0 focus:outline-none focus:ring-0 resize-none"
                    placeholder="Click here to add title"
                    wire:model.live.debounce="title">
                </textarea>

                <p class="text-sm text-gray-500 mt-1">{{ $meeting->date->format('F j , Y') }}</p>

            </div>

        </div>

        {{-- Notes --}}
        <div class="max-w-3xl mx-auto px-6 py-8">

            <div class="absolute right-6 text-sm">

                <div wire:loading.delay wire:target="title, notes">

                    <span class="flex items-center gap-1 text-gray-500">

                        <svg class="size-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>

                        Saving

                    </span>

                </div>

                <div
                    x-show="saved"
                    wire:loading.delay.remove
                    wire:target="title, notes"
                    class="flex items-center gap-1 text-green-600"
                    >

                    <span class="flex items-center gap-1 text-green-600">

                        <svg xmlns="<http://www.w3.org/2000/svg>" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" />
                        </svg>

                        Saved

                    </span>

                </div>

            </div>

            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide block">Notes</label>

            <textarea
                x-data="{
                    resize(){
                        this.$el.style.height = 'auto';
                        this.$el.style.height = this.$el.scrollHeight + 'px';
                    }
                }"
                x-init="resize()"
                @input="resize()"
                wire.ignore.self
                class="mt-3 text-gray-700 leading-relaxed -mx-2 px-2 py-1 w-full border-0 focus:outline-none focus:ring-0 resize-none field-sizing-content"
                placeholder="Click here to add notes"
                wire:model.live.debounce="notes">
            </textarea>

        </div>

    </div>

</div>
