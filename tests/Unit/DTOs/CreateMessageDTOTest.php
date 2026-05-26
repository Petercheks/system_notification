<?php

namespace Tests\Unit\DTOs;

use App\DTOs\CreateMessageDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CreateMessageDTOTest extends TestCase
{
    #[Test]
    public function it_builds_from_a_request_trims_the_body_and_attaches_the_id_immutably(): void
    {
        $dto = CreateMessageDTO::fromRequest([
            'category_slug' => 'sports',
            'body'          => "   padded message\n",
        ]);

        $this->assertSame('sports', $dto->categorySlug);
        $this->assertSame('padded message', $dto->body);
        $this->assertNull($dto->messageId);

        $withId = $dto->withMessageId(42);

        $this->assertNotSame($dto, $withId);
        $this->assertNull($dto->messageId);
        $this->assertSame(42, $withId->messageId);
        $this->assertSame('sports', $withId->categorySlug);
        $this->assertSame('padded message', $withId->body);
    }
}
