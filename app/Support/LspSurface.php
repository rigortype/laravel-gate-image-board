<?php

namespace App\Support;

/**
 * Call-site probe for Laravel helper keys.
 *
 * Unknown keys stay in this class. Do not copy them onto HTTP paths.
 * The method is for static analysis and is not invoked at runtime.
 */
class LspSurface
{
    public function surface(): void
    {
        env('APP_NAME');
        env('');
        env('GATE_MISSING_KEY');

        route('home');
        route('explore');
        route('gate.missing');

        view('welcome');
        view('missing.page');

        config('app.name');
        config('gate.missing');

        __('boards.explore');
        __('boards.visibility.public');
        __('boards.explore_empty_board', ['name' => 'General']);
        __('boards.missing');
    }
}
