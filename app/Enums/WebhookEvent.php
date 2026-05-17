<?php

namespace App\Enums;

enum WebhookEvent: string
{
    case CompressionCompleted = 'compression.completed';
    case CompressionFailed = 'compression.failed';
}
