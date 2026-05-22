<?php

namespace App\Security;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const CREATE = 'ARTICLE_CREATE';
    public const EDIT = 'ARTICLE_EDIT';
    public const DELETE = 'ARTICLE_DELETE';

    public function __construct(
        private Security $security
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifie que l'attribut est supporté
        if (!in_array($attribute, [
            self::CREATE,
            self::EDIT,
            self::DELETE
        ])) {
            return false;
        }

        // CREATE n'a pas besoin d'article
        if ($attribute === self::CREATE) {
            return true;
        }

        // EDIT et DELETE nécessitent un Article
        return $subject instanceof Article;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool
    {
        $user = $token->getUser();

        // Utilisateur non connecté
        if (!$user instanceof User) {
            return false;
        }

        // Admin = tous les droits
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // CREATE
        if ($attribute === self::CREATE) {
            return true;
        }

        /** @var Article $article */
        $article = $subject;

        return match ($attribute) {

            self::EDIT,
            self::DELETE
                => $article->getAuthor() === $user,

            default => false,
        };
    }
}