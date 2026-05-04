<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ConversationRepository;

class AccountController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/mon-compte', name: 'app_account')]
    public function index(ConversationRepository $conversationRepository): Response
    {
        $user = $this->getUser();

        $conversations = $conversationRepository->createQueryBuilder('c')
            ->where('c.buyer = :user OR c.seller = :user')
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'conversations' => $conversations,
        ]);
    }
}