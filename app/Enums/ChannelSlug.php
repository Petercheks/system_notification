<?php

namespace App\Enums;

enum ChannelSlug: string
{
    case Email = 'email';
    case Sms = 'sms';
    case PushNotification = 'push-notification';
}
