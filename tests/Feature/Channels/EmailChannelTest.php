<?php

namespace Tests\Feature\Channels;

use App\Channels\EmailChannel;
use App\DTOs\CreateMessageDTO;
use App\DTOs\NotificationResultDTO;
use App\DTOs\UserDTO;
use App\Enums\NotificationStatus;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailChannelTest extends TestCase
{
    #[Test]
    public function it_logs_the_email_and_returns_a_delivered_result(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($message, $context) =>
                $message === 'Email sent'
                && $context['to'] === 'alice@example.com'
                && $context['message'] === 'hi'
                && $context['category'] === 'sports'
            );

        $result = (new EmailChannel())->send(
            new UserDTO(1, 'Alice', 'alice@example.com', '+1-555-0000', ['sports'], ['email']),
            new CreateMessageDTO('sports', 'hi', messageId: 99),
            'sports',
        );

        $this->assertInstanceOf(NotificationResultDTO::class, $result);
        $this->assertSame(NotificationStatus::Delivered, $result->status);
        $this->assertSame('email', $result->channelSlug);
        $this->assertSame(99, $result->messageId);
        $this->assertNull($result->errorMessage);
    }
}
