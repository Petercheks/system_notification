<?php

namespace App\Channels;

use App\DTOs\CreateMessageDTO;
use App\DTOs\NotificationResultDTO;
use App\DTOs\UserDTO;
use App\Interfaces\NotificationChannelInterface;
use Illuminate\Support\Facades\Log;

class SmsChannel implements NotificationChannelInterface
{
    public function send(
        UserDTO          $user,
        CreateMessageDTO $message,
        string           $categorySlug,
    ): NotificationResultDTO {
        try {
            // TODO: Implement SMS sending logic

            Log::info('SMS sent', [
                'to'       => $user->phone,
                'message'  => $message->body,
                'category' => $categorySlug,
            ]);

            return NotificationResultDTO::success(
                userId: $user->id,
                messageId: $message->messageId,
                channelSlug: 'sms',
                categorySlug: $categorySlug,
            );
        } catch (\Throwable $e) {
            Log::error('SMS send failed', [
                'to'       => $user->phone,
                'category' => $categorySlug,
                'error'    => $e->getMessage(),
            ]);

            return NotificationResultDTO::failed(
                userId: $user->id,
                messageId: $message->messageId,
                channelSlug: 'sms',
                categorySlug: $categorySlug,
                errorMessage: $e->getMessage(),
            );
        }
    }
}
