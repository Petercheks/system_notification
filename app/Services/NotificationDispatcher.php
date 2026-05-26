<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Message;
use App\Models\Notification;
use App\Repositories\UserRepository;

class NotificationDispatcher
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function dispatch(Message $message, string $categorySlug): void
    {
        $users = $this->userRepository->getByCategory($categorySlug);

        $now      = now();
        $rows     = [];
        $payloads = [];

        foreach ($users as $user) {
            foreach ($user->channels as $channelSlug) {
                $rows[] = [
                    'user_id'       => $user->id,
                    'message_id'    => $message->id,
                    'channel_slug'  => $channelSlug,
                    'category_slug' => $categorySlug,
                    'status'        => NotificationStatus::Pending->value,
                    'error_message' => null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                $payloads[] = [
                    'userId'       => $user->id,
                    'messageId'    => $message->id,
                    'channelSlug'  => $channelSlug,
                    'categorySlug' => $categorySlug,
                    'body'         => $message->body,
                ];
            }
        }

        if ($rows === []) {
            return;
        }

        Notification::query()->insert($rows);

        foreach ($payloads as $payload) {
            SendNotificationJob::dispatch(
                userId: $payload['userId'],
                messageId: $payload['messageId'],
                channelSlug: $payload['channelSlug'],
                categorySlug: $payload['categorySlug'],
                body: $payload['body'],
            )->afterCommit();
        }
    }
}
