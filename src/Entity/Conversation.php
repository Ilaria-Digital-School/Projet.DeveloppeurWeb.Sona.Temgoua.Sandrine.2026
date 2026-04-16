<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Date de création de la conversation
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    // Date de mise à jour (ex : nouveau message)
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    // Article concerné par la conversation
    #[ORM\ManyToOne]
    private ?Article $article = null;

    // Acheteur potentiel
    #[ORM\ManyToOne]
    private ?User $buyer = null;

    // Vendeur (propriétaire de l'article)
    #[ORM\ManyToOne]
    private ?User $seller = null;

    // Messages appartenant à la conversation
    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class)]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
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

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function getSeller(): ?User
    {
        return $this->seller;
    }

    public function setSeller(?User $seller): static
    {
        $this->seller = $seller;
        return $this;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

}
