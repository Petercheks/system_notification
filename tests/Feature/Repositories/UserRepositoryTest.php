<?php

namespace Tests\Feature\Repositories;

use App\DTOs\UserDTO;
use App\Models\Category;
use App\Models\Channel;
use App\Models\User;
use App\Repositories\UserRepository;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChannelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_subscribers_only_as_dtos_carrying_categories_and_channels_as_slugs(): void
    {
        $this->seed([CategorySeeder::class, ChannelSeeder::class]);

        $sports  = Category::where('slug', 'sports')->first();
        $finance = Category::where('slug', 'finance')->first();
        $movies  = Category::where('slug', 'movies')->first();
        $email   = Channel::where('slug', 'email')->first();
        $sms     = Channel::where('slug', 'sms')->first();

        $subscriber = User::factory()->create();
        $subscriber->subscribed()->attach([$sports->id, $finance->id]);
        $subscriber->channels()->attach([$email->id, $sms->id]);

        $otherCategory = User::factory()->create();
        $otherCategory->subscribed()->attach($movies);
        $otherCategory->channels()->attach($email);

        $result = (new UserRepository())->getByCategory('sports');

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(UserDTO::class, $result);

        $dto = $result->first();
        $this->assertSame($subscriber->id, $dto->id);
        $this->assertSame($subscriber->email, $dto->email);
        $this->assertSame($subscriber->phone, $dto->phone);
        $this->assertEqualsCanonicalizing(['sports', 'finance'], $dto->subscribed);
        $this->assertEqualsCanonicalizing(['email', 'sms'], $dto->channels);
    }
}
