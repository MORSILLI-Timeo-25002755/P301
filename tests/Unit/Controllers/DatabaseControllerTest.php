<?php

namespace Tests\Unit\Controllers;

use Controllers\DatabaseController;
use RuntimeException;
use Tests\TestCase;

class TestableDatabaseController extends DatabaseController
{
    public function callGenerateCsrfToken(): string
    {
        return $this->generateCsrfToken();
    }

    public function callVerifyCsrfToken(?string $token): bool
    {
        return $this->verifyCsrfToken($token);
    }

    public function callLogSecurity(string $type, string $message): void
    {
        $this->logSecurity($type, $message);
    }

    public function callSetSecurityHeaders(): void
    {
        $this->setSecurityHeaders();
    }
}

class DatabaseControllerTest extends TestCase
{
    private TestableDatabaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $db = $this->createInMemoryDatabaseConnection();
        $this->controller = new TestableDatabaseController($db);
    }

    public function testGenerateCsrfTokenGenerates64HexCharacters(): void
    {
        $token = $this->controller->callGenerateCsrfToken();

        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function testGenerateCsrfTokenReusesExistingSessionToken(): void
    {
        $_SESSION['csrf_token'] = 'existing-token-value-1234567890';
        $token = $this->controller->callGenerateCsrfToken();

        $this->assertSame('existing-token-value-1234567890', $token);
    }

    public function testVerifyCsrfTokenReturnsTrueForValidToken(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';

        $this->assertTrue($this->controller->callVerifyCsrfToken('valid-token'));
    }

    public function testVerifyCsrfTokenReturnsFalseForInvalidToken(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';

        $this->assertFalse($this->controller->callVerifyCsrfToken('invalid-token'));
    }

    public function testVerifyCsrfTokenReturnsFalseWhenSessionHasNoToken(): void
    {
        unset($_SESSION['csrf_token']);

        $this->assertFalse($this->controller->callVerifyCsrfToken('any-token'));
        $this->assertFalse($this->controller->callVerifyCsrfToken(null));
    }

    public function testRedirectSetsLastRedirectAndTerminates(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            $this->controller->redirect('/target-page');
        } finally {
            $this->assertSame('/target-page', DatabaseController::$lastRedirect);
        }
    }

    public function testTerminateThrowsExceptionInTestingMode(): void
    {
        DatabaseController::$isTesting = true;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        $this->controller->terminate();
    }

    public function testLogSecurityWritesToFile(): void
    {
        $this->controller->callLogSecurity('TEST_TYPE', 'Test security message');

        $logPath = __DIR__ . '/../../../logs/security.log';
        $this->assertFileExists($logPath);
        $content = file_get_contents($logPath);
        $this->assertStringContainsString('[TEST_TYPE] Test security message', $content);
    }
}
