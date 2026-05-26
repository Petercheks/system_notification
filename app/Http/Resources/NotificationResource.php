<?php

namespace App\Http\Resources;

use App\DTOs\NotificationDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read NotificationDTO $resource
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var NotificationDTO $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'user_id' => $dto->userId,
            'user_name' => $dto->userName,
            'message_id' => $dto->messageId,
            'message_body' => $dto->messageBody,
            'channel_slug' => $dto->channelSlug,
            'category_slug' => $dto->categorySlug,
            'status' => $dto->status->value,
            'error_message' => $dto->errorMessage,
            'created_at' => $dto->createdAt,
        ];
    }
}
