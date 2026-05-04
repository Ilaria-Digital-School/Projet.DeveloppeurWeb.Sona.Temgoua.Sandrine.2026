<?php

namespace App\Security;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    const VIEW = 'MESSAGE_VIEW';
    const EDIT = 'MESSAGE_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Message;
    }

    protected function voteOnAttribute(string $attribute, mixed $message, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // auteur du message
        if ($message->getAuthor() === $user) {
            return true;
        }

        // 🔥 participant de la conversation
        return $message->getConversation()
            ->getParticipants()
            ->contains($user);
    }
}