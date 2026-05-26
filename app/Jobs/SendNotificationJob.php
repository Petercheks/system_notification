<?php

namespace App\Jobs;

use App\DTOs\CreateMessageDTO;
use App\DTOs\UserDTO;
use App\Enums\NotificationStatus;
use App\Factories\ChannelFactory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public array $backoff = [5, 30, 60];

    public function __construct(
        public readonly int    $userId,
        public readonly int    $messageId,
        public readonly string $channelSlug,
        public readonly string $categorySlug,
        public readonly string $body,
    ) {}

    public function handle(ChannelFactory $factory): void
    {
        $user    = User::with(['subscribed', 'channels'])->findOrFail($this->userId);
        $userDTO = UserDTO::fromEloquent($user);

        $messageDTO = new CreateMessageDTO(
            categorySlug: $this->categorySlug,
            body: $this->body,
            messageId: $this->messageId,
        );

        $channel = $factory->resolve($this->channelSlug);
        $result  = $channel->send($userDTO, $messageDTO, $this->categorySlug);

        $this->updateNotification(
            status: $result->status,
            errorMessage: $result->errorMessage,
        );
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendNotificationJob failed', [
            'user_id'      => $this->userId,
            'message_id'   => $this->messageId,
            'channel_slug' => $this->channelSlug,
            'error'        => $e->getMessage(),
        ]);

        $this->updateNotification(
            status: NotificationStatus::Failed,
            errorMessage: $e->getMessage(),
        );
    }

    private function updateNotification(NotificationStatus $status, ?string $errorMessage): void
    {
        Notification::query()
            ->where('user_id', $this->userId)
            ->where('message_id', $this->messageId)
            ->where('channel_slug', $this->channelSlug)
            ->update([
                'status'        => $status->value,
                'error_message' => $errorMessage,
                'updated_at'    => now(),
            ]);
    }
}
