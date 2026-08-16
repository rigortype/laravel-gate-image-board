<?php

namespace App\Actions\Threads;

use App\Actions\Images\StorePostImage;
use App\Models\Board;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateThread
{
    public function __construct(private StorePostImage $storePostImage) {}

    /**
     * Start a thread with an opening post and image.
     */
    public function handle(Board $board, User $user, string $body, UploadedFile $image, ?string $subject = null): Thread
    {
        return DB::transaction(function () use ($board, $user, $body, $image, $subject) {
            $thread = $board->threads()->create([
                'subject' => $subject,
                'bumped_at' => now(),
            ]);

            $post = $thread->posts()->create([
                'user_id' => $user->id,
                'body' => $body,
            ]);

            $post->setRelation('thread', $thread->setRelation('board', $board));

            $this->storePostImage->handle($post, $image);

            return $thread;
        });
    }
}
