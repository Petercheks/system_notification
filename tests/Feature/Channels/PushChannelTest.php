<?php

namespace Tests\Feature\Channels;

use App\Channels\PushChannel;
use App\DTOs\CreateMessageDTO;
use App\DTOs\NotificationResultDTO;
use App\DTOs\UserDTO;
use App\Enums\NotificationStatus;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PushChannelTest extends TestCase
{
    #[Test]
    public function it_logs_the_push_and_returns_a_delivered_result(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($message, $context) =>
                $message === 'Push notification sent'
                && $context['to'] === 'Carol'
                && $context['message'] === 'new release'
                && $context['category'] === 'movies'
            );

        $result = (new PushChannel())->send(
            new UserDTO(3, 'Carol', 'carol@example.com', '+1-555-0002', ['movies'], ['push-notification']),
            new CreateMessageDTO('movies', 'new release', messageId: 5),
            'movies',
        );

        $this->assertInstanceOf(NotificationResultDTO::class, $result);
        $this->assertSame(NotificationStatus::Delivered, $result->status);
        $this->assertSame('push-notification', $result->channelSlug);
        $this->assertSame(5, $result->messageId);
    }
}
