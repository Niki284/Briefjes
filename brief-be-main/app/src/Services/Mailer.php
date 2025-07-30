<?php

namespace Services;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mime\Email;

class Mailer{
    public static function sendMail($to, $subject, $text, $html){
        $transport = Transport::fromDsn('smtp://sandbox.smtp.mailtrap.io:2525');
        $mailer = new SymfonyMailer($transport);

        $email = (new Email())
            ->from('no-reply@downtowncabco.com')
            ->to($to)
            ->subject($subject)
            ->text($text)
            ->html($html);

        $mailer->send($email);
    }

}