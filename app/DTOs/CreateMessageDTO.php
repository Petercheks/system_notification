<?php

namespace App\DTOs;

readonly class CreateMessageDTO
{
    public function __construct(
        public string  $categorySlug,
        public string  $body,
        public ?int    $messageId = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            categorySlug: $data['category_slug'],
            body: trim($data['body']),
        );
    }

    public function withMessageId(int $messageId): self
    {
        return new self(
            categorySlug: $this->categorySlug,
            body: $this->body,
            messageId: $messageId,
        );
    }
}
