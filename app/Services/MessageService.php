<?php

namespace App\Services;

use App\DTOs\CreateMessageDTO;
use App\Models\Category;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher,
    ) {}

    public function handle(CreateMessageDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {

            $category = Category::query()
                ->where('slug', $dto->categorySlug)
                ->firstOrFail();

            $message = Message::query()
                ->create([
                    'category_id' => $category->id,
                    'body'        => $dto->body,
                ]);

            $this->dispatcher->dispatch($message, $dto->categorySlug);

            return [
                'message_id' => $message->id,
                'status'     => 'queued',
            ];
        });
    }
}
