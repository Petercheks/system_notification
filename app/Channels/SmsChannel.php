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

        Log::info('SMS sent', [
            'to'       => $user->phone,
            'message'  => $message->body,
            'category' => $categorySlug,
        ]);

        return NotificationResultDTO::success(
            userId: $user->id,
            messageId: $message->categoryId,
            channelSlug: 'sms',
            categorySlug: $categorySlug,
        );
    }
}
