<?php
namespace App\EntityListener;

use App\Entity\BlogPost;
use App\Entity\Mail;
use App\Service\MailService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: BlogPost::class)]
#[AsEntityListener(event: Events::preUpdate, entity: BlogPost::class)]
class BlogPostEntityListener
{
    public function __construct(
        private readonly SluggerInterface                    $slugger,
        private readonly MailService                         $mailService,
        #[Autowire('%admin_email%')] private readonly string $adminEmail,
    ) {}

    public function prePersist(BlogPost $blogpost, LifecycleEventArgs $event): void
    {
        $blogpost->computeSlug($this->slugger);
    }

    /**
     * @throws \Throwable
     * @throws Exception
     */
    public function preUpdate(BlogPost $blogpost, LifecycleEventArgs $event): void
    {
        $blogpost->computeSlug($this->slugger);
        if (
            $event->hasChangedField('state') &&
            ($event->getNewValue('state') !== $event->getOldValue('state'))
        ) {
            $mail = new Mail();
            $mail->setSubject('De status van je blogpost is bijgewerkt');
            $mail->setTemplate(Mail::BLOGPOST_CHANGE_STATE_NOTIFICATION);
            $mail->setFromMail($this->adminEmail);
            $mail->setToMail($this->adminEmail);
            $mail->setContext([
                'newState' => $event->getNewValue('state'),
                'oldState' => $event->getOldValue('state'),
                'title' => $blogpost->getTitle(),
            ]);
            $this->mailService->saveMail($mail);
        }
    }
}