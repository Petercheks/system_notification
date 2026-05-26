<?php

namespace Tests\Feature\Http\Api\v1;

use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_the_seeded_categories_alphabetically(): void
    {
        $this->seed(CategorySeeder::class);

        $response = $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'slug']]]);

        $slugs = array_column($response->json('data'), 'slug');
        $this->assertSame(['finance', 'movies', 'sports'], $slugs);
    }

    #[Test]
    public function it_returns_an_empty_collection_when_no_categories_exist(): void
    {
        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }
}
