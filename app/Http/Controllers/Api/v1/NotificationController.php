<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Repositories\NotificationRepository;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->notificationRepository->get(),
        ]);
    }
}
