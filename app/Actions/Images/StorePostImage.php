<?php

namespace App\Actions\Images;

use App\Models\Image;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class StorePostImage
{
    /**
     * Store an uploaded image on the post.
     */
    public function handle(Post $post, UploadedFile $file): Image
    {
        $originalFilename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $size = $file->getSize();

        $path = $file->store(
            'images/'.$post->thread->board->team_id.'/'.$post->thread->board_id,
            'public',
        );

        if ($path === false) {
            throw new RuntimeException('Failed to store the uploaded image.');
        }

        $dimensions = @getimagesize(Storage::disk('public')->path($path));

        return $post->image()->create([
            'disk' => 'public',
            'path' => $path,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'size' => $size,
            'width' => is_array($dimensions) ? $dimensions[0] : null,
            'height' => is_array($dimensions) ? $dimensions[1] : null,
        ]);
    }
}
