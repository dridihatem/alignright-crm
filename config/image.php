<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Processing Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for image processing and compression
    | used throughout the application.
    |
    */

    'compression' => [
        // Maximum file size before compression (in bytes)
        'max_file_size' => env('IMAGE_MAX_FILE_SIZE', 10 * 1024 * 1024), // 10MB

        // Maximum dimensions for images
        'max_width' => env('IMAGE_MAX_WIDTH', 1920),
        'max_height' => env('IMAGE_MAX_HEIGHT', 1080),

        // Quality settings for different formats
        'jpeg_quality' => env('IMAGE_JPEG_QUALITY', 85),
        'png_quality' => env('IMAGE_PNG_QUALITY', 8), // 0-9, where 9 is best compression
        'webp_quality' => env('IMAGE_WEBP_QUALITY', 85),

        // Client-side compression settings
        'client_quality' => env('IMAGE_CLIENT_QUALITY', 0.85),
    ],

    'validation' => [
        // Maximum file size for upload (in bytes)
        'max_upload_size' => env('IMAGE_MAX_UPLOAD_SIZE', 50 * 1024 * 1024), // 50MB

        // Allowed image formats
        'allowed_types' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp'
        ],

        // Allowed file extensions
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'
        ],
    ],

    'storage' => [
        // Storage disk for processed images
        'disk' => env('IMAGE_STORAGE_DISK', 'local'),

        // Directory for storing processed images
        'directory' => env('IMAGE_STORAGE_DIRECTORY', 'processed-images'),

        // Whether to keep original files
        'keep_originals' => env('IMAGE_KEEP_ORIGINALS', false),
    ],

    'cache' => [
        // Whether to cache processed images
        'enabled' => env('IMAGE_CACHE_ENABLED', true),

        // Cache duration in seconds
        'duration' => env('IMAGE_CACHE_DURATION', 86400), // 24 hours
    ],

    'profiles' => [
        // Predefined compression profiles
        'thumbnail' => [
            'max_width' => 300,
            'max_height' => 300,
            'quality' => 80,
        ],
        'medium' => [
            'max_width' => 800,
            'max_height' => 600,
            'quality' => 85,
        ],
        'large' => [
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 90,
        ],
        'high_quality' => [
            'max_width' => 2560,
            'max_height' => 1440,
            'quality' => 95,
        ],
    ],
];
