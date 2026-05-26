<?php

namespace App\DTOs;

readonly class NotificationResultDTO
{
    public function __construct(
        public int     $userId,
        public int     $messageId,
        public string  $channelSlug,
        public string  $categorySlug,
        public string  $status,
        public ?string $errorMessage = null,
    ) {}

    public static function success(
        int    $userId,
        int    $messageId,
        string $channelSlug,
        string $categorySlug,
    ): self {
        return new self(
            userId: $userId,
            messageId: $messageId,
            channelSlug: $channelSlug,
            categorySlug: $categorySlug,
            status: 'success',
        );
    }

    public static function failed(
        int    $userId,
        int    $messageId,
        string $channelSlug,
        string $categorySlug,
        string $errorMessage,
    ): self {
        return new self(
            userId: $userId,
            messageId: $messageId,
            channelSlug: $channelSlug,
            categorySlug: $categorySlug,
            status: 'failed',
            errorMessage: $errorMessage,
        );
    }
}
