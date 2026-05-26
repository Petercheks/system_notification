<?php

namespace App\DTOs;

use App\Enums\NotificationStatus;

readonly class NotificationResultDTO
{
    public function __construct(
        public int                $userId,
        public int                $messageId,
        public string             $channelSlug,
        public string             $categorySlug,
        public NotificationStatus $status,
        public ?string            $errorMessage = null,
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
            status: NotificationStatus::Delivered,
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
            status: NotificationStatus::Failed,
            errorMessage: $errorMessage,
        );
    }
}
