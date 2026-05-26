<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\CreateMessageDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService,
    ) {}

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $dto = CreateMessageDTO::fromRequest($request->validated());

        $result = $this->messageService->handle($dto);

        return response()->json([
            'data' => $result,
        ], 201);
    }
}
