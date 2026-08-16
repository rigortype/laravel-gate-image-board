<?php

namespace App\Actions\Posts;

use App\Actions\Images\StorePostImage;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ReplyToThread
{
    public function __construct(private StorePostImage $storePostImage) {}

    /**
     * Add a reply to the thread and bump it.
     */
    public function handle(Thread $thread, User $user, string $body, ?UploadedFile $image = null): Post
    {
        return DB::transaction(function () use ($thread, $user, $body, $image) {
            $post = $thread->posts()->create([
                'user_id' => $user->id,
                'body' => $body,
            ]);

            if ($image) {
                $post->setRelation('thread', $thread);

                $this->storePostImage->handle($post, $image);
            }

            $thread->forceFill([
                'bumped_at' => now(),
            ])->save();

            return $post;
        });
    }
}
