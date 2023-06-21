<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class AjoutNotificationService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(): void
    {   
            $email = (new Email())
                ->from('guerfala71@gmail.com')
                ->to('mohamedtaher.guerfala@esprit.tn')
                ->subject('Une nouvelle réclamation a été ajouté  ')
                ->text(sprintf('Une nouvelle réclamation a été ajouté.'));
            $this->mailer->send($email);
    }


}