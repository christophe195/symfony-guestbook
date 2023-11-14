<?php
namespace App\MessageHandler;

use App\Message\MailMessage;
use App\Repository\MailRepository;
use App\Service\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MailMessageHandler
{
    public function __construct(
        private readonly mailRepository $mailRepository,
        private readonly MailService    $mailService,
    ) {}

    /**
     * @throws \Exception
     */
    public function __invoke(MailMessage $message): void
    {
        $mail = $this->mailRepository->find($message->getId());
        if (!$mail) {
            throw new \Exception('Mail with id ' . $message->getId() . ' not found.');
        }

        $this->mailService->sendMail($mail);
    }
}