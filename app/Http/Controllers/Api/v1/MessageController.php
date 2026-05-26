<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\CreateMessageDTO;
use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService,
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_slug' => 'required|string|exists:categories,slug',
            'body'          => 'required|string',
        ]);

        $dto = CreateMessageDTO::fromRequest($data);

        $result = $this->messageService->handle($dto);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data'    => $result,
        ], 201);
    }
}
