<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Message;
use App\Repositories\UserRepository;

class NotificationDispatcher
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function dispatch(Message $message, string $categorySlug): void
    {
        $users = $this->userRepository->getByCategory($categorySlug);

        foreach ($users as $user) {
            foreach ($user->channels as $channelSlug) {
                SendNotificationJob::dispatch(
                    userId: $user->id,
                    messageId: $message->id,
                    channelSlug: $channelSlug,
                    categorySlug: $categorySlug,
                    body: $message->body,
                );
            }
        }
    }
}
