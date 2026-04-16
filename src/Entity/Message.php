<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Contenu du message
    #[ORM\Column(length: 255)]
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
    // Méthodes getters et setters permettant d'accéder et de modifier les propriétés
    // de l'entité Message (contenu du message, date de création, statut de lecture,
    // conversation associée et utilisateur expéditeur). Elles sont utilisées par
    // Doctrine et par les contrôleurs pour manipuler les données avant leur
    // enregistrement ou leur récupération depuis la base de données.

    #[ORM\ManyToOne]
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
        $this->content = $content;
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

    // 📎 FILE PATH
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;
        return $this;
    }


    // 🖼 IMAGE PATH
    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }


    // 🎤 AUDIO PATH
    public function getAudioPath(): ?string
    {
        return $this->audioPath;
    }

    public function setAudioPath(?string $audioPath): static
    {
        $this->audioPath = $audioPath;
        return $this;
    }


    // ↩️ REPLY TO MESSAGE
    public function getReplyTo(): ?self
    {
        return $this->replyTo;
    }

    public function setReplyTo(?self $replyTo): static
    {
        $this->replyTo = $replyTo;
        return $this;
    }


    // ✔ READ AT
    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;
        return $this;
    }

   
}
