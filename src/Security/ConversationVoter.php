<?php

namespace App\Security;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConversationVoter extends Voter
{
    const VIEW = 'CONVERSATION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $conversation, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // admin peut tout voir
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // 🔥 logique clé : vérifier participation
        return $conversation->getSeller() === $user
    || $conversation->getBuyer() === $user;
    }
}