<?php

namespace App\DTOs;

use App\Enums\NotificationStatus;
use App\Models\Notification;

readonly class NotificationDTO
{
    public function __construct(
        public int                $id,
        public int                $userId,
        public string             $userName,
        public int                $messageId,
        public string             $messageBody,
        public string             $channelSlug,
        public string             $categorySlug,
        public NotificationStatus $status,
        public ?string            $errorMessage,
        public string             $createdAt,
    ) {}

    public static function fromEloquent(Notification $notification): self
    {
        return new self(
            id: $notification->id,
            userId: $notification->user_id,
            userName: $notification->user?->name ?? '',
            messageId: $notification->message_id,
            messageBody: $notification->message?->body ?? '',
            channelSlug: $notification->channel_slug,
            categorySlug: $notification->category_slug,
            status: $notification->status,
            errorMessage: $notification->error_message,
            createdAt: $notification->created_at?->toIso8601String() ?? '',
        );
    }
}
