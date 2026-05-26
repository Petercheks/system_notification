<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryRepository
{
    public function get(): Collection
    {
        return Category::query()
            ->orderBy('name', 'asc')
            ->get();
    }
}
