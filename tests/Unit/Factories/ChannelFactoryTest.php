<?php

namespace Tests\Unit\Factories;

use App\Channels\EmailChannel;
use App\Channels\PushChannel;
use App\Channels\SmsChannel;
use App\Factories\ChannelFactory;
use App\Interfaces\NotificationChannelInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ChannelFactoryTest extends TestCase
{
    private ChannelFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ChannelFactory(
            new EmailChannel(),
            new SmsChannel(),
            new PushChannel(),
        );
    }

    #[Test]
    public function it_resolves_every_known_slug_to_the_right_channel(): void
    {
        $this->assertInstanceOf(EmailChannel::class, $this->factory->resolve('email'));
        $this->assertInstanceOf(SmsChannel::class, $this->factory->resolve('sms'));
        $this->assertInstanceOf(PushChannel::class, $this->factory->resolve('push-notification'));
    }

    #[Test]
    public function every_resolved_channel_honors_the_channel_interface(): void
    {
        foreach (['email', 'sms', 'push-notification'] as $slug) {
            $this->assertInstanceOf(NotificationChannelInterface::class, $this->factory->resolve($slug));
        }
    }

    #[Test]
    public function it_throws_when_the_slug_is_unknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown channel: [telegram]');

        $this->factory->resolve('telegram');
    }
}
