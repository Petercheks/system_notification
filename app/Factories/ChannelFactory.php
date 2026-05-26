<?php

namespace App\Factories;

use App\Channels\EmailChannel;
use App\Channels\PushChannel;
use App\Channels\SmsChannel;
use App\Interfaces\NotificationChannelInterface;

class ChannelFactory
{
    public function __construct(
        private readonly EmailChannel $emailChannel,
        private readonly SmsChannel   $smsChannel,
        private readonly PushChannel  $pushChannel,
    ) {}

    public function resolve(string $slug): NotificationChannelInterface
    {
        return match ($slug) {
            'email' => $this->emailChannel,
            'sms' => $this->smsChannel,
            'push-notification' => $this->pushChannel,
            default => throw new \InvalidArgumentException("Unknown channel: [{$slug}]"),
        };
    }
}
