<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseCategories = [
            'Sports',
            'Finance',
            'Movies',
        ];

        foreach ($baseCategories as $category) {
            Category::create([
                'name' => $category,
            ]);
        }
    }
}
