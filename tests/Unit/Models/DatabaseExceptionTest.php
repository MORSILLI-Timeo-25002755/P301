<?php

namespace Tests\Unit\Models;

use Exception;
use Models\DatabaseException;
use Tests\TestCase;

class DatabaseExceptionTest extends TestCase
{
    public function testDatabaseExceptionIsAnException(): void
    {
        $exception = new DatabaseException('Query error', 500);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('Query error', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
    }
}
