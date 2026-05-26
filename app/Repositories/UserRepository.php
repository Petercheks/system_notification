<?php

namespace App\Repositories;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Support\Collection;

class UserRepository
{
    public function getByCategory(int $categoryId): Collection
    {
        return User::query()
            ->whereHas('subscribed', fn($query) => $query->where('categories.id', $categoryId))
            ->with(['subscribed', 'channels'])
            ->get()
            ->map(fn(User $user) => UserDTO::fromEloquent($user))
            ->values();
    }
}
