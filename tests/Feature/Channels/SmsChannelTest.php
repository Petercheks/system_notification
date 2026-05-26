<?php

namespace Tests\Feature\Channels;

use App\Channels\SmsChannel;
use App\DTOs\CreateMessageDTO;
use App\DTOs\NotificationResultDTO;
use App\DTOs\UserDTO;
use App\Enums\NotificationStatus;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SmsChannelTest extends TestCase
{
    #[Test]
    public function it_logs_the_sms_to_the_user_phone_and_returns_a_delivered_result(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($message, $context) =>
                $message === 'SMS sent'
                && $context['to'] === '+1-555-0001'
                && $context['message'] === 'stocks up'
                && $context['category'] === 'finance'
            );

        $result = (new SmsChannel())->send(
            new UserDTO(7, 'Bob', 'bob@example.com', '+1-555-0001', ['finance'], ['sms']),
            new CreateMessageDTO('finance', 'stocks up', messageId: 12),
            'finance',
        );

        $this->assertInstanceOf(NotificationResultDTO::class, $result);
        $this->assertSame(NotificationStatus::Delivered, $result->status);
        $this->assertSame('sms', $result->channelSlug);
        $this->assertSame(12, $result->messageId);
    }
}
