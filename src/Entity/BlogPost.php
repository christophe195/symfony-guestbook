<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(length: 255, options: ['default' => 'DRAFT'])]
    private ?string $state = 'DRAFT';

    #[ORM\Column]
    private ?\DateTimeImmutable $creationDate = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public const STATE_DRAFT = 'DRAFT';
    public const STATE_REVIEW = 'REVIEW';
    public const STATE_APPROVED = 'APPROVED';
    public const STATE_REJECTED = 'REJECTED';
    public const STATE_PUBLISHED = 'PUBLISHED';

    public const STATE_OPTIONS = [
        self::STATE_DRAFT => self::STATE_DRAFT,
        self::STATE_REVIEW => self::STATE_REVIEW,
        self::STATE_APPROVED => self::STATE_APPROVED,
        self::STATE_REJECTED => self::STATE_REJECTED,
        self::STATE_PUBLISHED => self::STATE_PUBLISHED
    ];

    public function __construct()
    {
        $this->creationDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function computeSlug(SluggerInterface $slugger): void
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = $this->getCreationDate()->format('Ymdhis')  . '-' . $slugger->slug((string) $this)->lower();
        }
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
