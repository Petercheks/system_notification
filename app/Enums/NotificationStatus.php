<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Pending = 'pending';
    case Delivered = 'delivered';
    case Failed = 'failed';
}
