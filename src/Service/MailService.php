<?php
namespace App\Service;

use App\Entity\Mail;
use Doctrine\DBAL\Exception;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use App\Message\MailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MailService {

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface        $mailer,
        private readonly MessageBusInterface    $bus,
    ) {}

    /**
     * @throws \Throwable
     * @throws Exception
     */
    public function saveMail(Mail $mail): void
    {
        /*$this->entityManager->persist($mail);
        $this->entityManager->flush();

        $this->bus->dispatch(new MailMessage($mail->getId()));*/

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        $id = time();

        try {
            // Voer de SQL-query handmatig uit
            $sql = '
                INSERT INTO mail 
                    (
                        id, 
                        subject, 
                        template, 
                        from_mail, 
                        to_mail, 
                        context
                    ) 
                    VALUES 
                    (
                        :id, 
                        :subject, 
                        :template, 
                        :fromMail, 
                        :toMail, 
                        :context
                    )';
            $params = [
                'id' => $id,
                'subject' => $mail->getSubject(),
                'template' => $mail->getTemplate(),
                'fromMail' => $mail->getFromMail(),
                'toMail' => $mail->getToMail(),
                'context' => json_encode($mail->getContext()),
            ];

            $connection->executeStatement($sql, $params);
            $connection->commit();
            $this->bus->dispatch(new MailMessage($id));
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
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