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
            self::Public => __('Public'),
            self::Team => __('Team only'),
            self::Unlisted => __('Unlisted'),
            self::Private => __('Private'),
        };
    }

    /**
     * Get a short description of who can see the thread.
     */
    public function description(): string
    {
        return match ($this) {
            self::Public => __('Anyone can find and view this thread.'),
            self::Team => __('Only members of this team can view this thread.'),
            self::Unlisted => __('Hidden from listings. Anyone with the link can view it.'),
            self::Private => __('Only you can view this thread.'),
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
