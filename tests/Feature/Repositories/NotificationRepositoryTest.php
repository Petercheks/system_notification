<?php

namespace Tests\Feature\Repositories;

use App\DTOs\NotificationDTO;
use App\Enums\NotificationStatus;
use App\Models\Category;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\NotificationRepository;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_notifications_as_dtos_ordered_from_newest_to_oldest(): void
    {
        $this->seed(CategorySeeder::class);

        $category = Category::where('slug', 'sports')->first();
        $user     = User::factory()->create();
        $message  = Message::create(['category_id' => $category->id, 'body' => 'body']);

        $this->travelTo(now()->subMinutes(10));
        $oldest = Notification::create([
            'user_id'       => $user->id,
            'message_id'    => $message->id,
            'channel_slug'  => 'email',
            'category_slug' => 'sports',
            'status'        => NotificationStatus::Delivered,
        ]);

        $this->travelTo(now()->addMinutes(10));
        $newest = Notification::create([
            'user_id'       => $user->id,
            'message_id'    => $message->id,
            'channel_slug'  => 'sms',
            'category_slug' => 'sports',
            'status'        => NotificationStatus::Failed,
            'error_message' => 'boom',
        ]);

        $this->travelBack();

        $result = (new NotificationRepository())->get();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(NotificationDTO::class, $result);
        $this->assertSame($newest->id, $result->first()->id);
        $this->assertSame($oldest->id, $result->last()->id);
        $this->assertSame($user->name, $result->first()->userName);
        $this->assertSame('body', $result->first()->messageBody);
        $this->assertSame(NotificationStatus::Failed, $result->first()->status);
        $this->assertSame('boom', $result->first()->errorMessage);
    }
}
