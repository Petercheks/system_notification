<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $notifications = $this->notificationRepository->get();
        return NotificationResource::collection($notifications);
    }
}
