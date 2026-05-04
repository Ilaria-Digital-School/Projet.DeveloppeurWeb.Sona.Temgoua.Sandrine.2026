<?php

namespace App\Controller;

namespace App\Controller;

use App\Entity\Message;
use App\Security\MessageVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageController extends AbstractController
{
    #[Route('/messages/{id}', name: 'app_message_show', methods: ['GET'])]
    #[IsGranted(MessageVoter::VIEW, subject: 'message')]
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }
}