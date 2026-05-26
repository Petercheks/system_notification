<?php

namespace Tests\Feature\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChannelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationDispatcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategorySeeder::class, ChannelSeeder::class]);
    }

    #[Test]
    public function it_dispatches_one_job_per_user_per_enabled_channel(): void
    {
        Queue::fake();

        $finance = Category::where('slug', 'finance')->first();
        $email   = Channel::where('slug', 'email')->first();
        $push    = Channel::where('slug', 'push-notification')->first();

        $user = User::factory()->create();
        $user->subscribed()->attach($finance);
        $user->channels()->attach([$email->id, $push->id]);

        $message = Message::create(['category_id' => $finance->id, 'body' => 'Stock surge']);

        $this->app->make(NotificationDispatcher::class)->dispatch($message, 'finance');

        Queue::assertPushed(SendNotificationJob::class, 2);
        Queue::assertPushed(SendNotificationJob::class, fn (SendNotificationJob $job) =>
            $job->userId === $user->id
            && $job->messageId === $message->id
            && $job->categorySlug === 'finance'
            && $job->body === 'Stock surge'
            && $job->channelSlug === 'email'
        );
        Queue::assertPushed(SendNotificationJob::class, fn (SendNotificationJob $job) =>
            $job->channelSlug === 'push-notification'
        );
    }

    #[Test]
    public function it_skips_users_subscribed_to_other_categories_and_those_without_channels(): void
    {
        Queue::fake();

        $sports = Category::where('slug', 'sports')->first();
        $movies = Category::where('slug', 'movies')->first();
        $email  = Channel::where('slug', 'email')->first();

        $sportsUser = User::factory()->create();
        $sportsUser->subscribed()->attach($sports);
        $sportsUser->channels()->attach($email);

        $moviesUserWithChannels = User::factory()->create();
        $moviesUserWithChannels->subscribed()->attach($movies);
        $moviesUserWithChannels->channels()->attach($email);

        $moviesUserWithoutChannels = User::factory()->create();
        $moviesUserWithoutChannels->subscribed()->attach($movies);

        $message = Message::create(['category_id' => $movies->id, 'body' => 'Premiere tonight']);

        $this->app->make(NotificationDispatcher::class)->dispatch($message, 'movies');

        Queue::assertPushed(SendNotificationJob::class, 1);
        Queue::assertPushed(SendNotificationJob::class, fn (SendNotificationJob $job) =>
            $job->userId === $moviesUserWithChannels->id
        );
    }
}
