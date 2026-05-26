<?php

namespace Tests\Feature\Jobs;

use App\Enums\NotificationStatus;
use App\Factories\ChannelFactory;
use App\Jobs\SendNotificationJob;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChannelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class SendNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategorySeeder::class, ChannelSeeder::class]);
    }

    #[Test]
    public function handle_persists_a_delivered_notification_when_the_channel_succeeds(): void
    {
        Log::spy();

        $sports = Category::where('slug', 'sports')->first();
        $email  = Channel::where('slug', 'email')->first();

        $user = User::factory()->create();
        $user->subscribed()->attach($sports);
        $user->channels()->attach($email);

        $message = Message::create(['category_id' => $sports->id, 'body' => 'win!']);

        $job = new SendNotificationJob(
            userId: $user->id,
            messageId: $message->id,
            channelSlug: 'email',
            categorySlug: 'sports',
            body: $message->body,
        );

        $job->handle($this->app->make(ChannelFactory::class));

        $this->assertDatabaseHas('notifications', [
            'user_id'       => $user->id,
            'message_id'    => $message->id,
            'channel_slug'  => 'email',
            'category_slug' => 'sports',
            'status'        => NotificationStatus::Delivered->value,
            'error_message' => null,
        ]);
    }

    #[Test]
    public function failed_persists_a_failed_notification_with_the_error_message(): void
    {
        $sports  = Category::where('slug', 'sports')->first();
        $user    = User::factory()->create();
        $message = Message::create(['category_id' => $sports->id, 'body' => 'oops']);

        $job = new SendNotificationJob(
            userId: $user->id,
            messageId: $message->id,
            channelSlug: 'sms',
            categorySlug: 'sports',
            body: 'oops',
        );

        $job->failed(new RuntimeException('gateway down'));

        $this->assertDatabaseHas('notifications', [
            'user_id'       => $user->id,
            'message_id'    => $message->id,
            'channel_slug'  => 'sms',
            'category_slug' => 'sports',
            'status'        => NotificationStatus::Failed->value,
            'error_message' => 'gateway down',
        ]);
    }
}
