<?php

namespace App\DTOs;

readonly class CreateMessageDTO
{
    public function __construct(
        public int    $categoryId,
        public string $body,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            categoryId: (int) $data['category_id'],
            body: trim($data['body']),
        );
    }
}
