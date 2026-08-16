<?php

namespace App\Enums;

enum ThreadVisibility: string
{
    case Public = 'public';
    case Team = 'team';
    case Unlisted = 'unlisted';
    case Private = 'private';

    /**
     * Get the display label for the visibility.
     */
    public function label(): string
    {
        return match ($this) {
            self::Public => __('boards.visibility.public'),
            self::Team => __('boards.visibility.team'),
            self::Unlisted => __('boards.visibility.unlisted'),
            self::Private => __('boards.visibility.private'),
        };
    }

    /**
     * Get a short description of who can see the thread.
     */
    public function description(): string
    {
        return match ($this) {
            self::Public => __('boards.visibility.public_help'),
            self::Team => __('boards.visibility.team_help'),
            self::Unlisted => __('boards.visibility.unlisted_help'),
            self::Private => __('boards.visibility.private_help'),
        };
    }

    /**
     * Flux badge color for this visibility.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Public => 'green',
            self::Team => 'blue',
            self::Unlisted => 'amber',
            self::Private => 'zinc',
        };
    }
}
