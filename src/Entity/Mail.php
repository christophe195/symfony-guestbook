<?php

namespace App\Entity;

use App\Repository\MailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailRepository::class)]
class Mail
{
    const COMMENT_NOTIFICATION = 'emails/comment_notification.html.twig';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $template = null;

    #[ORM\Column(length: 255)]
    private ?string $fromMail = null;

    #[ORM\Column(length: 255)]
    private ?string $toMail = null;

    #[ORM\Column(length: 255)]
    private ?string $context = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function getFromMail(): ?string
    {
        return $this->fromMail;
    }

    public function setFromMail(string $fromMail): static
    {
        $this->fromMail = $fromMail;

        return $this;
    }

    public function getToMail(): ?string
    {
        return $this->toMail;
    }

    public function setToMail(string $toMail): static
    {
        $this->toMail = $toMail;

        return $this;
    }

    public function getContext(): ?array
    {
        if(is_null($this->context)) {
            return null;
        }
        return json_decode($this->context, true);
    }

    public function setContext(array $context): static
    {
        $this->context = json_encode($context);

        return $this;
    }
}
