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

        Log::info('Push notification sent', [
            'to'       => $user->name,
            'message'  => $message->body,
            'category' => $categorySlug,
        ]);

        return NotificationResultDTO::success(
            userId: $user->id,
            messageId: $message->categoryId,
            channelSlug: 'push-notification',
            categorySlug: $categorySlug,
        );
    }
}
