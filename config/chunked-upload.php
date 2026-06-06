<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chunked Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the robust chunked upload system
    |
    */

    // File Size Limits
    'max_file_size' => env('CHUNKED_UPLOAD_MAX_FILE_SIZE', 500 * 1024 * 1024), // 500MB
    'default_chunk_size' => env('CHUNKED_UPLOAD_CHUNK_SIZE', 2 * 1024 * 1024), // 2MB
    'max_chunks' => env('CHUNKED_UPLOAD_MAX_CHUNKS', 500),

    // Allowed File Types
    'allowed_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'stl'
    ],

    'allowed_mime_types' => [
        // Images
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        // STL files
        'model/stl', 'application/sla', 'application/vnd.ms-pki.stl'
    ],

    // Session Management
    'session_expire_hours' => env('CHUNKED_UPLOAD_SESSION_EXPIRE', 24),
    'cleanup_frequency' => env('CHUNKED_UPLOAD_CLEANUP_FREQUENCY', 'daily'),

    // Rate Limiting
    'rate_limits' => [
        'uploads_per_minute' => env('CHUNKED_UPLOAD_RATE_UPLOADS_MINUTE', 10),
        'chunks_per_minute' => env('CHUNKED_UPLOAD_RATE_CHUNKS_MINUTE', 60),
        'sessions_per_hour' => env('CHUNKED_UPLOAD_RATE_SESSIONS_HOUR', 30),
        'total_size_per_hour' => env('CHUNKED_UPLOAD_RATE_SIZE_HOUR', 2 * 1024 * 1024 * 1024), // 2GB
    ],

    // Image Optimization
    'image_optimization' => [
        'enabled' => env('CHUNKED_UPLOAD_OPTIMIZE_IMAGES', true),
        'max_width' => env('CHUNKED_UPLOAD_MAX_WIDTH', 800),
        'max_height' => env('CHUNKED_UPLOAD_MAX_HEIGHT', 800),
        'jpeg_quality' => env('CHUNKED_UPLOAD_JPEG_QUALITY', 85),
        'png_quality' => env('CHUNKED_UPLOAD_PNG_QUALITY', 90),
        'webp_quality' => env('CHUNKED_UPLOAD_WEBP_QUALITY', 85),
        'create_thumbnails' => env('CHUNKED_UPLOAD_CREATE_THUMBNAILS', true),
        'thumbnail_size' => env('CHUNKED_UPLOAD_THUMBNAIL_SIZE', 150),
        'replace_original' => env('CHUNKED_UPLOAD_REPLACE_ORIGINAL', true),
        'minimum_compression_ratio' => env('CHUNKED_UPLOAD_MIN_COMPRESSION', 10), // %
    ],

    // STL Processing
    'stl_processing' => [
        'enabled' => env('CHUNKED_UPLOAD_PROCESS_STL', true),
        'analyze_complexity' => env('CHUNKED_UPLOAD_STL_ANALYZE', true),
        'max_triangle_count' => env('CHUNKED_UPLOAD_STL_MAX_TRIANGLES', 1000000),
    ],

    // Storage
    'storage' => [
        'disk' => env('CHUNKED_UPLOAD_DISK', 'local'),
        'temp_path' => env('CHUNKED_UPLOAD_TEMP_PATH', 'temp/uploads'),
        'final_path' => env('CHUNKED_UPLOAD_FINAL_PATH', 'uploads'),
        'thumbnail_path' => env('CHUNKED_UPLOAD_THUMBNAIL_PATH', 'thumbnails'),
        'backup_path' => env('CHUNKED_UPLOAD_BACKUP_PATH', 'backups'),
    ],

    // Security
    'security' => [
        'virus_scanning' => env('CHUNKED_UPLOAD_VIRUS_SCAN', false),
        'file_validation' => env('CHUNKED_UPLOAD_FILE_VALIDATION', true),
        'hash_verification' => env('CHUNKED_UPLOAD_HASH_VERIFICATION', true),
        'secure_filenames' => env('CHUNKED_UPLOAD_SECURE_FILENAMES', true),
    ],

    // Logging
    'logging' => [
        'enabled' => env('CHUNKED_UPLOAD_LOGGING', true),
        'level' => env('CHUNKED_UPLOAD_LOG_LEVEL', 'info'),
        'log_successful_uploads' => env('CHUNKED_UPLOAD_LOG_SUCCESS', true),
        'log_failed_uploads' => env('CHUNKED_UPLOAD_LOG_FAILURES', true),
        'log_rate_limits' => env('CHUNKED_UPLOAD_LOG_RATE_LIMITS', true),
    ],

    // Performance
    'performance' => [
        'max_concurrent_uploads' => env('CHUNKED_UPLOAD_MAX_CONCURRENT', 3),
        'queue_optimization_jobs' => env('CHUNKED_UPLOAD_QUEUE_OPTIMIZATION', true),
        'optimization_delay_seconds' => env('CHUNKED_UPLOAD_OPTIMIZATION_DELAY', 30),
        'memory_limit' => env('CHUNKED_UPLOAD_MEMORY_LIMIT', '512M'),
        'max_execution_time' => env('CHUNKED_UPLOAD_MAX_EXECUTION_TIME', 300), // 5 minutes
    ],
];


