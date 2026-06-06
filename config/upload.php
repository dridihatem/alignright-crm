<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for file uploads,
    | including large file handling and background processing settings.
    |
    */

    'max_file_size' => env('UPLOAD_MAX_FILE_SIZE', '500M'), // 500MB default
    'max_files_per_request' => env('UPLOAD_MAX_FILES_PER_REQUEST', 20),
    'chunk_size' => env('UPLOAD_CHUNK_SIZE', 1048576), // 1MB chunks
    'temp_directory' => storage_path('app/temp_uploads'),
    'cleanup_temp_files_after' => 24, // hours
    
    /*
    |--------------------------------------------------------------------------
    | Background Upload Settings
    |--------------------------------------------------------------------------
    */
    'background_upload' => [
        'enabled' => env('BACKGROUND_UPLOAD_ENABLED', true),
        'queue' => env('BACKGROUND_UPLOAD_QUEUE', 'default'),
        'timeout' => env('BACKGROUND_UPLOAD_TIMEOUT', 1800), // 30 minutes
        'retry_attempts' => env('BACKGROUND_UPLOAD_RETRY', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Drive Settings
    |--------------------------------------------------------------------------
    */
    'google_drive' => [
        'large_file_threshold' => env('GOOGLE_DRIVE_LARGE_FILE_THRESHOLD', 5242880), // 5MB
        'resumable_upload_chunk_size' => env('GOOGLE_DRIVE_CHUNK_SIZE', 1048576), // 1MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    */
    'allowed_types' => [
        'stl' => ['stl'],
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
        'documents' => ['pdf', 'doc', 'docx'],
        'archives' => ['zip', 'rar', '7z'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing Settings
    |--------------------------------------------------------------------------
    */
    'image_processing' => [
        'quality' => env('IMAGE_QUALITY', 85),
        'max_width' => env('IMAGE_MAX_WIDTH', 1920),
        'max_height' => env('IMAGE_MAX_HEIGHT', 1080),
        'compress_images' => env('COMPRESS_IMAGES', true),
    ],
];
