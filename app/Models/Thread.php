<?php

namespace App\Models;

use App\Enums\TeamPermission;
use App\Enums\ThreadVisibility;
use Database\Factories\ThreadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property int $id
 * @property int $board_id
 * @property string|null $subject
 * @property ThreadVisibility $visibility
 * @property string|null $share_token
 * @property Carbon $bumped_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Board $board
 * @property-read Post|null $openingPost
 * @property-read Collection<int, Post> $posts
 */
#[Fillable(['board_id', 'subject', 'visibility', 'share_token', 'bumped_at'])]
class Thread extends Model implements Feedable, Sitemapable
{
    /** @use HasFactory<ThreadFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'visibility' => ThreadVisibility::Team->value,
    ];

    /**
     * Get the board this thread belongs to.
     *
     * @return BelongsTo<Board, $this>
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the posts in this thread.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the opening post that started this thread.
     *
     * @return HasOne<Post, $this>
     */
    public function openingPost(): HasOne
    {
        return $this->hasOne(Post::class)->oldestOfMany();
    }

    /**
     * Determine whether the given user wrote the opening post.
     */
    public function isAuthoredBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->openingPost?->user_id === $user->id;
    }

    /**
     * Public URL for this thread.
     */
    public function url(): string
    {
        return route('threads.show', [
            'current_team' => $this->board->team,
            'board' => $this->board,
            'thread' => $this,
        ]);
    }

    /**
     * Sitemap entry for this thread.
     */
    public function toSitemapTag(): Url
    {
        return Url::create($this->url())
            ->setLastModificationDate($this->bumped_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
            ->setPriority(0.8);
    }

    /**
     * Feed entry for this thread.
     */
    public function toFeedItem(): FeedItem
    {
        $opening = $this->openingPost;
        $author = $opening?->author;

        return FeedItem::create()
            ->id($this->url())
            ->title($this->subject ?: __('No subject'))
            ->summary($opening === null ? '' : $opening->body)
            ->updated($this->bumped_at)
            ->link($this->url())
            ->authorName($author === null ? __('Unknown') : $author->name);
    }

    /**
     * Public threads for the site feed.
     *
     * @return Collection<int, static>
     */
    public static function getFeedItems(): Collection
    {
        return static::query()
            ->where('visibility', ThreadVisibility::Public)
            ->with(['board.team', 'openingPost.author'])
            ->latest('bumped_at')
            ->limit(50)
            ->get();
    }

    /**
     * Restrict the query to threads the viewer may see in a board catalog.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    #[Scope]
    protected function visibleInCatalogTo(Builder $query, ?User $user, Board $board): Builder
    {
        $isMember = $user !== null && $user->belongsToTeam($board->team);
        $isModerator = $isMember && $user->hasTeamPermission($board->team, TeamPermission::UpdateTeam);

        return $query->where(function (Builder $query) use ($user, $isMember, $isModerator) {
            $query->where('visibility', ThreadVisibility::Public);

            if ($isMember) {
                $query->orWhere('visibility', ThreadVisibility::Team);
            }

            if ($isModerator) {
                $query->orWhereIn('visibility', [
                    ThreadVisibility::Unlisted,
                    ThreadVisibility::Private,
                ]);
            } elseif ($user !== null) {
                $query->orWhere(function (Builder $query) use ($user) {
                    $query->whereIn('visibility', [
                        ThreadVisibility::Unlisted,
                        ThreadVisibility::Private,
                    ])->whereHas('openingPost', function (Builder $query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                });
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => ThreadVisibility::class,
            'bumped_at' => 'datetime',
        ];
    }
}
