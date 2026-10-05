<?php

namespace Tests\Unit\Views;

use Tests\TestCase;
use Views\Dashboard;
use Views\Error;
use Views\ForgotPassword;
use Views\Homepage;
use Views\Login;
use Views\Register;

class ViewsTest extends TestCase
{
    public function testErrorViewRendersTitleAndMessage(): void
    {
        $view = new Error('Titre Erreur', 'Détail de l\'erreur', '/retry');

        ob_start();
        $view->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('Titre Erreur', $output);
        $this->assertStringContainsString(htmlspecialchars('Détail de l\'erreur'), $output);
        $this->assertStringContainsString('/retry', $output);
    }

    public function testErrorViewDefaultPage(): void
    {
        $view = new Error('404', 'Page introuvable');

        ob_start();
        $view->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('404', $output);
        $this->assertStringContainsString('Page introuvable', $output);
        $this->assertStringContainsString('href=/', $output);
    }

    public function testLoginViewRendersFormAndCsrfToken(): void
    {
        $view = new Login('token-test-12345');

        ob_start();
        $view->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('token-test-12345', $output);
        $this->assertStringContainsString('name="email"', $output);
        $this->assertStringContainsString('name="password"', $output);
    }

    public function testRegisterViewWithErrors(): void
    {
        $view = new Register('token-reg-123');

        ob_start();
        $view->show(notFilled: true, validPassword: true);
        $output1 = ob_get_clean();

        $this->assertStringContainsString('Veuillez remplir tous les champs !', $output1);

        ob_start();
        $view->show(notFilled: false, validPassword: false);
        $output2 = ob_get_clean();

        $this->assertStringContainsString('Échec de la confirmation du mot de passe.', $output2);
    }

    public function testDashboardViewRendersUsername(): void
    {
        $view = new Dashboard('Charlie');

        ob_start();
        $view->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('Hello, Charlie !', $output);
    }

    public function testForgotPasswordViewModes(): void
    {
        // Message mode
        $viewWithMessage = new ForgotPassword(message: 'Email envoyé');
        ob_start();
        $viewWithMessage->show();
        $outputMsg = ob_get_clean();
        $this->assertStringContainsString('Email envoyé', $outputMsg);

        // Error mode
        $viewWithError = new ForgotPassword(error: 'Email invalide');
        ob_start();
        $viewWithError->show();
        $outputErr = ob_get_clean();
        $this->assertStringContainsString('Email invalide', $outputErr);

        // Token mode
        $viewWithToken = new ForgotPassword(token: 'reset-token-123');
        ob_start();
        $viewWithToken->show();
        $outputToken = ob_get_clean();
        $this->assertStringContainsString('Nouveau mot de passe', $outputToken);
        $this->assertStringContainsString('name="password"', $outputToken);
        $this->assertStringContainsString('name="confirm"', $outputToken);
    }

    public function testHomepageViewRendersHero(): void
    {
        $view = new Homepage();

        ob_start();
        $view->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('Création de sondages', $output);
        $this->assertStringContainsString('Créer un compte', $output);
        $this->assertStringContainsString('Se connecter', $output);
    }
}
