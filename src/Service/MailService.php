<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class MailService
{
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private Environment $twig;
    private LoggerInterface $logger;
    private string $fromEmail;

    public function __construct(
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        LoggerInterface $logger,
        string $fromEmail = 'noreply@tuapp.com' // Cambia por tu email
    ) {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->fromEmail = $fromEmail;
    }

    public function sendVerificationEmail(string $userEmail, string $userName, string $token): void
    {
        try {
            // Generar URL de verificación
            $verificationUrl = $this->urlGenerator->generate(
                'app_verify_email',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // Crear el contenido del email
            $htmlContent = $this->twig->render('emails/verification.html.twig', [
                'userName' => $userName,
                'verificationUrl' => $verificationUrl
            ]);

            // Crear y configurar el email
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($userEmail)
                ->subject('Verifica tu cuenta')
                ->html($htmlContent);

            // Enviar el email
            $this->mailer->send($email);

            $this->logger->info('Verification email sent', [
                'email' => $userEmail,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to send verification email', [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('No se pudo enviar el correo de verificación: ' . $e->getMessage());
        }
    }
}
