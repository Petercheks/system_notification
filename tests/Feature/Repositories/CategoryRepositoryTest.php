<?php

namespace Tests\Feature\Repositories;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_all_categories_alphabetically_by_name(): void
    {
        $this->seed(CategorySeeder::class);

        $result = (new CategoryRepository())->get();

        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(Category::class, $result);
        $this->assertSame(['Finance', 'Movies', 'Sports'], $result->pluck('name')->all());
    }
}
