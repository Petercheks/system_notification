<?php

namespace App\Factories;

use App\Channels\EmailChannel;
use App\Channels\PushChannel;
use App\Channels\SmsChannel;
use App\Enums\ChannelSlug;
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
        $channel = ChannelSlug::tryFrom($slug)
            ?? throw new \InvalidArgumentException("Unknown channel: [{$slug}]");

        return match ($channel) {
            ChannelSlug::Email => $this->emailChannel,
            ChannelSlug::Sms => $this->smsChannel,
            ChannelSlug::PushNotification => $this->pushChannel,
        };
    }
}
