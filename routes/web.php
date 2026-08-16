<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::livewire('t/{thread:share_token}', 'pages::threads.show')->name('threads.shared');

Route::prefix('{current_team}')
    ->scopeBindings()
    ->group(function () {
        Route::middleware(['auth', 'verified', EnsureTeamMembership::class])->group(function () {
            Route::livewire('dashboard', 'pages::boards.index')->name('dashboard');
            Route::livewire('boards', 'pages::boards.index')->name('boards.index');
        });

        Route::livewire('boards/{board:slug}', 'pages::boards.show')->name('boards.show');
        Route::livewire('boards/{board:slug}/threads/{thread}', 'pages::threads.show')->name('threads.show');
    });

require __DIR__.'/settings.php';
