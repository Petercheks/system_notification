<?php

namespace App\Interfaces;

use App\DTOs\CreateMessageDTO;
use App\DTOs\NotificationResultDTO;
use App\DTOs\UserDTO;

interface NotificationChannelInterface
{
    public function send(
        UserDTO $user,
        CreateMessageDTO $message,
        string $categorySlug
    ): NotificationResultDTO;
}
