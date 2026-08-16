<?php

namespace App\Actions\Seo;

use App\Enums\ThreadVisibility;
use App\Models\Board;
use App\Models\Thread;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class BuildSitemap
{
    /**
     * Build a sitemap of public pages and public threads.
     */
    public function handle(): Sitemap
    {
        return Sitemap::create()
            ->add(Url::create(route('home'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0))
            ->add(Board::query()->with('team')->latest()->get())
            ->add(Thread::query()
                ->where('visibility', ThreadVisibility::Public)
                ->with('board.team')
                ->latest('bumped_at')
                ->get());
    }
}
