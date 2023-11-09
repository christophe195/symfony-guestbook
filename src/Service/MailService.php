<?php
namespace App\Service;

use App\Entity\Mail;
use App\Message\CommentMessage;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use App\Message\MailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MailService {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private MessageBusInterface $bus,
    ) {
    }

    public function saveMail(Mail $mail): void
    {
        $this->entityManager->persist($mail);
        $this->entityManager->flush();

        $this->bus->dispatch(new MailMessage($mail->getId()));
        $this->entityManager->flush();
    }
    public function sendMail(Mail $mail): void
    {
        $this->mailer->send((new NotificationEmail())
            ->subject($mail->getSubject())
            ->htmlTemplate($mail->getTemplate())
            ->from($mail->getFromMail())
            ->to($mail->getToMail())
            ->context($mail->getContext() ?? [])
        );

        $this->entityManager->remove($mail);
        $this->entityManager->flush();
    }
}