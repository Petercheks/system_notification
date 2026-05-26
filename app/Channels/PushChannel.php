<?php

namespace App\Channels;

use App\DTOs\CreateMessageDTO;
use App\DTOs\NotificationResultDTO;
use App\DTOs\UserDTO;
use App\Interfaces\NotificationChannelInterface;
use Illuminate\Support\Facades\Log;

class PushChannel implements NotificationChannelInterface
{
    public function send(
        UserDTO          $user,
        CreateMessageDTO $message,
        string           $categorySlug,
    ): NotificationResultDTO {
        try {
            // TODO: Implement push notification sending logic

            Log::info('Push notification sent', [
                'to'       => $user->name,
                'message'  => $message->body,
                'category' => $categorySlug,
            ]);

            return NotificationResultDTO::success(
                userId: $user->id,
                messageId: $message->messageId,
                channelSlug: 'push-notification',
                categorySlug: $categorySlug,
            );
        } catch (\Throwable $e) {
            Log::error('Push notification send failed', [
                'to'       => $user->name,
                'category' => $categorySlug,
                'error'    => $e->getMessage(),
            ]);

            return NotificationResultDTO::failed(
                userId: $user->id,
                messageId: $message->messageId,
                channelSlug: 'push-notification',
                categorySlug: $categorySlug,
                errorMessage: $e->getMessage(),
            );
        }
    }
}
