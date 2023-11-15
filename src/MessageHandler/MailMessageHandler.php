<?php
namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Message\MailMessage;
use App\Repository\MailRepository;
use App\Service\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MailMessageHandler
{
    public function __construct(
        private mailRepository $mailRepository,
        private MailService $mailService,
    ) {
    }

    public function __invoke(MailMessage $message)
    {
        $mail = $this->mailRepository->find($message->getId());
        if (!$mail) {
            return;
        }

        $this->mailService->sendMail($mail);
    }
}