<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->scopeBindings()
    ->group(function () {
        Route::livewire('dashboard', 'pages::boards.index')->name('dashboard');
        Route::livewire('boards', 'pages::boards.index')->name('boards.index');
        Route::livewire('boards/{board:slug}', 'pages::boards.show')->name('boards.show');
        Route::livewire('boards/{board:slug}/threads/{thread}', 'pages::threads.show')->name('threads.show');
    });

require __DIR__.'/settings.php';
