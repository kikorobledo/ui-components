<?php

use App\Models\UserSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Toast notification system')] #[Layout('layouts::base')]class extends Component
{

    public UserSetting $settings;
    public string $name = '';
    public string $theme = '';
    public string $language = '';

    public function mount():void
    {

        $this->settings = UserSetting::first();
        $this->name = $this->settings->name;
        $this->theme = $this->settings->theme;
        $this->language = $this->settings->language;


    }

    public function updateName(){

        $this->settings->update(['name' => $this->name]);

        $this->dispatch('toast', message:'Display name updated.', type:'success');

    }

    public function updatedTheme(){

        $this->settings->update(['theme' => $this->theme]);

        if ($this->theme === 'light') {
            $this->dispatch('toast',
                type: 'success',
                message: 'Theme changed to light mode'
            );
        } elseif ($this->theme === 'dark') {
            $this->dispatch('toast',
                type: 'warning',
                message: 'Dark mode is in beta'
            );
        } else {
            $this->dispatch('toast',
                type: 'info',
                message: 'Theme will match your system preferences'
            );
        }

    }

    public function updatedLanguage(){

        $this->settings->update(['language' => $this->language]);

         $this->dispatch('toast',
            type: 'info',
            message: 'Page will reload to apply language'
        );

    }

};
?>

<div class="min-h-screen bg-white">

    <!-- Header -->
    <div class="border-b border-gray-200">

        <div class="max-w-2xl mx-auto px-6 py-6">

            <h1 class="text-xl font-semibold text-gray-900">Profile Settings</h1>

            <p class="text-sm text-gray-500 mt-1">Manage your account preferences</p>

        </div>

    </div>

    <!-- Content -->
    <div class="max-w-2xl mx-auto px-6 py-8">
        <div class="space-y-8">

            <!-- Display Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Display Name
                </label>
                <div class="flex gap-3">
                    <input
                        type="text"
                        wire:model="name"
                        class="flex-1 text-sm px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <button
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                        wire:click="updateName"
                    >
                        Update
                    </button>
                </div>
            </div>

            <!-- Theme -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Theme
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="radio"
                            value="light"
                            wire:model.live="theme"
                            class="w-4 h-4 text-blue-600"
                        >
                        <span class="ml-2 text-sm text-gray-700">Light</span>
                    </label>

                    <label class="flex items-center cursor-pointer">
                        <input
                            type="radio"
                            value="dark"
                            wire:model.live="theme"
                            class="w-4 h-4 text-blue-600"
                        >
                        <span class="ml-2 text-sm text-gray-700">Dark</span>
                    </label>

                    <label class="flex items-center cursor-pointer">
                        <input
                            type="radio"
                            value="auto"
                            wire:model.live="theme"
                            class="w-4 h-4 text-blue-600"
                        >
                        <span class="ml-2 text-sm text-gray-700">Auto</span>
                    </label>
                </div>
            </div>

            <!-- Language -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Language
                </label>
                <select
                    wire:model.live="language"
                    class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="english">English</option>
                    <option value="spanish">Spanish</option>
                    <option value="french">French</option>
                    <option value="german">German</option>
                </select>
            </div>
        </div>
    </div>
    <x-toast />
</div>
