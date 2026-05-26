<?php

namespace Tests\Feature\Http\Api\v1;

use App\Jobs\SendNotificationJob;
use App\Models\Category;
use App\Models\Channel;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChannelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategorySeeder::class, ChannelSeeder::class]);
    }

    #[Test]
    public function it_creates_a_message_and_queues_one_job_per_subscription_channel(): void
    {
        Queue::fake();

        $sports = Category::where('slug', 'sports')->first();
        $email  = Channel::where('slug', 'email')->first();

        $user = User::factory()->create();
        $user->subscribed()->attach($sports);
        $user->channels()->attach($email);

        $response = $this->postJson('/api/v1/messages', [
            'category_slug' => 'sports',
            'body'          => 'Hello sports!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['data' => ['message_id', 'status']])
            ->assertJsonPath('data.status', 'queued');

        $this->assertDatabaseHas('messages', ['body' => 'Hello sports!']);
        Queue::assertPushed(SendNotificationJob::class, 1);
    }

    #[Test]
    public function it_returns_422_when_the_body_is_missing(): void
    {
        $this->postJson('/api/v1/messages', [
            'category_slug' => 'sports',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }

    #[Test]
    public function it_returns_422_when_the_body_is_only_whitespace(): void
    {
        $this->postJson('/api/v1/messages', [
            'category_slug' => 'sports',
            'body'          => '    ',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }

    #[Test]
    public function it_returns_422_when_the_body_exceeds_one_thousand_characters(): void
    {
        $this->postJson('/api/v1/messages', [
            'category_slug' => 'sports',
            'body'          => str_repeat('a', 1001),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }

    #[Test]
    public function it_returns_422_when_the_category_does_not_exist(): void
    {
        $this->postJson('/api/v1/messages', [
            'category_slug' => 'unknown',
            'body'          => 'hello',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_slug']);
    }

    #[Test]
    public function it_returns_422_when_the_category_slug_is_missing(): void
    {
        $this->postJson('/api/v1/messages', [
            'body' => 'hello',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_slug']);
    }

    #[Test]
    public function posting_a_message_then_listing_notifications_returns_the_delivery(): void
    {
        // QUEUE_CONNECTION=sync in phpunit.xml -> jobs run inline, end-to-end.
        $sports = Category::where('slug', 'sports')->first();
        $email  = Channel::where('slug', 'email')->first();

        $user = User::factory()->create();
        $user->subscribed()->attach($sports);
        $user->channels()->attach($email);

        $this->postJson('/api/v1/messages', [
            'category_slug' => 'sports',
            'body'          => 'go team',
        ])->assertCreated();

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $user->id)
            ->assertJsonPath('data.0.channel_slug', 'email')
            ->assertJsonPath('data.0.category_slug', 'sports')
            ->assertJsonPath('data.0.status', 'delivered')
            ->assertJsonPath('data.0.message_body', 'go team');
    }
}
