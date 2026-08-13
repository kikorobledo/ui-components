<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::livewire('notes', 'pages::meeting-notes')->name('notes');

Route::livewire('profile', 'pages::profile-settings')->name('profile');

Route::livewire('wizard', 'pages::product-wizard')->name('wizard');

Route::livewire('tag-input', 'pages::tag-input')->name('tag-input');

Route::livewire('infinite-scroll', 'pages::infinite-scroll')->name('infinite-scroll');

Route::livewire('notification-center', 'pages::notification-center')->name('notification-center');

Route::livewire('dynamic-search', 'pages::dynamic-search')->name('dynamic-search');

Route::livewire('kanban-board', 'pages::kanban-board')->name('kanban-board');
