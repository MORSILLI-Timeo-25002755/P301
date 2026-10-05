<?php

namespace Tests\Unit\Controllers;

use Controllers\Homepage;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function testExecuteRendersHomepage(): void
    {
        $controller = new Homepage();
        $controller->execute();

        $this->assertTrue(true);
    }
}
