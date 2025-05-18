<?php

namespace App\Service;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    private Mailer $mailer;
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $transport = new EsmtpTransport('mailhog', 1025);
        $this->mailer = new Mailer($transport);
        $this->twig = $twig;
    }

    public function sendEmail(string $to, string $subject, string $template, array $context = []): void
    {
        $html = $this->twig->render($template, $context);
        $email = (new Email())
            ->from('noreply@example.com')
            ->to($to)
            ->subject($subject)
            ->html($html);
        $this->mailer->send($email);
    }
}
