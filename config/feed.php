<?php

use App\Models\Thread;

return [
    'feeds' => [
        'main' => [
            'items' => [Thread::class, 'getFeedItems'],
            'url' => '/feed',
            'title' => 'Gate Imageboard',
            'description' => 'Public threads.',
            'language' => 'en-US',
            'image' => '',
            'format' => 'atom',
            'view' => 'feed::atom',
            'type' => '',
            'contentType' => '',
        ],
    ],
];
