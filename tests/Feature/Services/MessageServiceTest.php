<?php

namespace Tests\Feature\Services;

use App\DTOs\CreateMessageDTO;
use App\Jobs\SendNotificationJob;
use App\Models\Category;
use App\Models\Channel;
use App\Models\User;
use App\Services\MessageService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChannelSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategorySeeder::class, ChannelSeeder::class]);
    }

    #[Test]
    public function it_persists_the_message_and_dispatches_one_job_per_subscribed_user_per_channel(): void
    {
        Queue::fake();

        $sports = Category::where('slug', 'sports')->first();
        $email  = Channel::where('slug', 'email')->first();
        $sms    = Channel::where('slug', 'sms')->first();

        $userA = User::factory()->create();
        $userA->subscribed()->attach($sports);
        $userA->channels()->attach([$email->id, $sms->id]);

        $userB = User::factory()->create();
        $userB->subscribed()->attach($sports);
        $userB->channels()->attach($email->id);

        // not subscribed to sports — must be ignored
        $userC = User::factory()->create();
        $userC->channels()->attach($email->id);

        $result = $this->app->make(MessageService::class)
            ->handle(new CreateMessageDTO('sports', 'go team'));

        $this->assertSame('queued', $result['status']);
        $this->assertDatabaseHas('messages', [
            'id'          => $result['message_id'],
            'body'        => 'go team',
            'category_id' => $sports->id,
        ]);

        Queue::assertPushed(SendNotificationJob::class, 3);
        Queue::assertPushed(SendNotificationJob::class, fn (SendNotificationJob $job) =>
            $job->userId === $userA->id && $job->channelSlug === 'sms'
        );
        Queue::assertNotPushed(SendNotificationJob::class, fn (SendNotificationJob $job) =>
            $job->userId === $userC->id
        );
    }

    #[Test]
    public function it_rolls_back_the_transaction_when_the_category_is_unknown(): void
    {
        Queue::fake();

        $thrown = false;

        try {
            $this->app->make(MessageService::class)
                ->handle(new CreateMessageDTO('does-not-exist', 'body'));
        } catch (ModelNotFoundException) {
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Expected a ModelNotFoundException to be thrown.');
        $this->assertDatabaseCount('messages', 0);
        Queue::assertNothingPushed();
    }
}
