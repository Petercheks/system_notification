<?php

namespace Tests\Feature\Http;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    #[Test]
    public function the_dashboard_view_loads_successfully(): void
    {
        $this->withoutVite()
            ->get('/')
            ->assertOk()
            ->assertViewIs('notifications.index');
    }
}
