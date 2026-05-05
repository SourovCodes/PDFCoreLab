<?php

return [
    'source_disk' => env('PDF_COMPRESSION_SOURCE_DISK', 'local'),

    'output_disk' => env('PDF_COMPRESSION_OUTPUT_DISK', env('PDF_COMPRESSION_SOURCE_DISK', 'local')),

    'source_directory' => env('PDF_COMPRESSION_SOURCE_DIRECTORY', 'pdf-compressions/originals'),

    'output_directory' => env('PDF_COMPRESSION_OUTPUT_DIRECTORY', 'pdf-compressions/compressed'),

    'ghostscript_binary' => env('GHOSTSCRIPT_BINARY', 'gs'),

    'max_upload_size_kb' => (int) env('PDF_COMPRESSION_MAX_UPLOAD_SIZE_KB', 51200),

    'process_timeout_seconds' => (int) env('PDF_COMPRESSION_PROCESS_TIMEOUT_SECONDS', 300),

    'retention_days' => (int) env('PDF_COMPRESSION_RETENTION_DAYS', 7),
];
