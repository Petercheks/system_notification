<?php

namespace App\Repositories;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Support\Collection;

class UserRepository
{
    public function getByCategory(string $categorySlug): Collection
    {
        return User::query()
            ->whereHas(
                'subscribed',
                fn($query) => $query->where('categories.slug', $categorySlug)
            )
            ->with([
                'subscribed:id,slug',
                'channels:id,slug'
            ])
            ->get()
            ->map(fn(User $user) => UserDTO::fromEloquent($user))
            ->values();
    }
}
