<?php

namespace App\Http\Resources;

use App\DTOs\NotificationDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
            'message_id' => $this->message_id,
            'message_body' => $this->message->body,
            'channel_slug' => $this->channel_slug,
            'category_slug' => $this->category_slug,
            'status' => $this->status->value,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
        ];
    }
}
