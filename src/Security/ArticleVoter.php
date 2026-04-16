<?php

namespace App\Security;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ArticleVoter extends Voter
{
    const EDIT = 'ARTICLE_EDIT';
    const DELETE = 'ARTICLE_DELETE';
const CREATE = 'ARTICLE_CREATE';

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) 
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si pas connecté → aucun droit sauf lecture 
        if (!$user instanceof User) {
            return false;
        }

        // L'admin a tous les droits
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }
        

        // Vérifier si l'utilisateur est l'auteur
        /** @var Article $article */
        $article = $subject;
        return $user === $article->getAuthor();
    }
}