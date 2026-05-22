<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Contenu du message
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le message est obligatoire")]
    private ?string $content = null;

    // Date d'envoi du message
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    // Permet de savoir si le message a été lu
    #[ORM\Column]
    private ?bool $isRead = false;

    // Relation avec la conversation
    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?Conversation $conversation = null;

    // Utilisateur qui envoie le message
    #[ORM\ManyToOne(inversedBy: 'sentMessages')]
    private ?User $sender = null;

    #[ORM\Column(nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(nullable: true)]
    private ?string $audioPath = null;

    #[ORM\ManyToOne(targetEntity: Message::class)]
    private ?Message $replyTo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isDeleted = false;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    private ?Article $article = null;

    // =========================
    // GETTERS & SETTERS
    // =========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = trim($content);
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getAudioPath(): ?string
    {
        return $this->audioPath;
    }

    public function setAudioPath(?string $audioPath): static
    {
        $this->audioPath = $audioPath;
        return $this;
    }

    public function getReplyTo(): ?self
    {
        return $this->replyTo;
    }

    public function setReplyTo(?self $replyTo): static
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }
    
}