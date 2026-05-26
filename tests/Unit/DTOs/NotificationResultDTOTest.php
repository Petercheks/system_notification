<?php

namespace Tests\Unit\DTOs;

use App\DTOs\NotificationResultDTO;
use App\Enums\NotificationStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationResultDTOTest extends TestCase
{
    #[Test]
    public function success_and_failed_factories_produce_the_expected_result(): void
    {
        $success = NotificationResultDTO::success(
            userId: 1,
            messageId: 2,
            channelSlug: 'email',
            categorySlug: 'sports',
        );

        $this->assertSame(NotificationStatus::Delivered, $success->status);
        $this->assertNull($success->errorMessage);
        $this->assertSame(1, $success->userId);
        $this->assertSame(2, $success->messageId);
        $this->assertSame('email', $success->channelSlug);
        $this->assertSame('sports', $success->categorySlug);

        $failed = NotificationResultDTO::failed(
            userId: 1,
            messageId: 2,
            channelSlug: 'sms',
            categorySlug: 'movies',
            errorMessage: 'gateway timeout',
        );

        $this->assertSame(NotificationStatus::Failed, $failed->status);
        $this->assertSame('gateway timeout', $failed->errorMessage);
    }
}
