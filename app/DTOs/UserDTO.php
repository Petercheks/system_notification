<?php

namespace App\DTOs;

use App\Models\User;

readonly class UserDTO
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $email,
        public string $phone,
        public array  $subscribed,
        public array  $channels,
    ) {}

    public static function fromEloquent(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            phone: $user->phone,
            subscribed: $user->subscribed->pluck('slug')->toArray(),
            channels: $user->channels->pluck('slug')->toArray(),
        );
    }
}
