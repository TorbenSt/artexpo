<?php

return [
    'networks' => [
        'instagram',
        'facebook',
        'twitter',
    ],

    // Character limits per network
    'limits' => [
        'instagram' => 2200,  // Caption limit
        'facebook' => 63206,  // Post limit
        'twitter' => 280,     // X character limit
    ],

    // Default language for content generation
    'default_language' => 'de',

    // Languages supported for RAG indexing
    'supported_languages' => ['de', 'en'],
];
