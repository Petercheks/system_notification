<?php

namespace Tests\Feature\Http\Api\v1;

use App\Enums\NotificationStatus;
use App\Models\Category;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    #[Test]
    public function it_returns_an_empty_collection_when_there_are_no_notifications(): void
    {
        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    #[Test]
    public function it_returns_notifications_sorted_from_newest_to_oldest(): void
    {
        $sports  = Category::where('slug', 'sports')->first();
        $user    = User::factory()->create();
        $message = Message::create(['category_id' => $sports->id, 'body' => 'body']);

        $this->travelTo(now()->subMinutes(10));
        $oldest = Notification::create([
            'user_id'       => $user->id,
            'message_id'    => $message->id,
            'channel_slug'  => 'email',
            'category_slug' => 'sports',
            'status'        => NotificationStatus::Delivered,
        ]);

        $this->travelTo(now()->addMinutes(5));
        $middle = Notification::create([
            'user_id'       => $user->id,
            'message_id'    => $message->id,
            'channel_slug'  => 'sms',
            'category_slug' => 'sports',
            'status'        => NotificationStatus::Delivered,
        ]);

        $this->travelTo(now()->addMinutes(5));
        $newest = Notification::create([
            'user_id'       => $user->id,
            'message_id'    => $message->id,
            'channel_slug'  => 'push-notification',
            'category_slug' => 'sports',
            'status'        => NotificationStatus::Failed,
            'error_message' => 'boom',
        ]);

        $this->travelBack();

        $response = $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $ids = array_column($response->json('data'), 'id');
        $this->assertSame([$newest->id, $middle->id, $oldest->id], $ids);

        $response->assertJsonPath('data.0.status', 'failed');
        $response->assertJsonPath('data.0.error_message', 'boom');
        $response->assertJsonPath('data.0.user_name', $user->name);
        $response->assertJsonPath('data.0.message_body', 'body');
        $response->assertJsonPath('data.2.status', 'delivered');
    }
}
