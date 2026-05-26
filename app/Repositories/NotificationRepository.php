<?php

namespace App\Repositories;

use App\DTOs\NotificationDTO;
use App\Models\Notification;
use Illuminate\Support\Collection;

class NotificationRepository
{
    public function get(): Collection
    {
        return Notification::query()
            ->with([
                'user:id,name',
                'message:id,body,category_id'
            ])
            ->latest()
            ->get()
            ->map(fn(Notification $notification) => NotificationDTO::fromEloquent($notification))
            ->values();
    }
}
