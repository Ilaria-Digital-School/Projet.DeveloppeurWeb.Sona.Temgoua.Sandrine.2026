<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ArticleRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConversationController extends AbstractController
{
    #[Route('/conversations', name: 'app_conversation_list')]
    public function list(ConversationRepository $repo): Response
    {
        $user = $this->getUser();

        $conversations = $repo->createQueryBuilder('c')
            ->where('c.buyer = :user OR c.seller = :user')
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('conversation/list.html.twig', [
            'conversations' => $conversations
        ]);
    }

    #[Route('/conversation/start/{articleId}', name: 'app_conversation_start')]
    public function start(
        int $articleId,
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        ConversationRepository $conversationRepository
    ): Response {
        $user = $this->getUser();

        $article = $articleRepository->find($articleId);
        $seller = $article->getAuthor();

        $conversation = $conversationRepository->findOneBy([
            'article' => $article,
            'buyer' => $user,
            'seller' => $seller
        ]);

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setArticle($article);
            $conversation->setBuyer($user);
            $conversation->setSeller($seller);
            $conversation->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($conversation);

            $message = new Message();
            $message->setContent("Bonjour, je suis intéressé par ".$article->getTitle());
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setConversation($conversation);
            $message->setSender($user);
            $message->setIsRead(false);

            $em->persist($message);
        }

        $em->flush();

        return $this->redirectToRoute('app_conversation_show', [
            'id' => $conversation->getId()
        ]);
    }

    #[Route('/conversation/{id}', name: 'app_conversation_show')]
    public function show(
        Conversation $conversation,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();
        
        if (
            $conversation->getBuyer() !== $user &&
            $conversation->getSeller() !== $user
        ) {
            throw $this->createAccessDeniedException();
        }
       dd($conversation->getMessages());
        // ✅ marquer comme lu
        foreach ($conversation->getMessages() as $msg) {
            
            if ($msg->getSender() !== $user && !$msg->getReadAt()) {
                $msg->setReadAt(new \DateTimeImmutable());
            }
        }
        
        $em->flush();

        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation
        ]);
    }

    #[Route('/message/send/{id}', name: 'app_message_send', methods: ['POST'])]
    public function send(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $message = new Message();
        $message->setContent($request->request->get('content'));
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setConversation($conversation);
        $message->setSender($this->getUser());
        $message->setIsRead(false);

        // 🔁 reply
        $replyToId = $request->request->get('replyTo');
        if ($replyToId) {
            $replyMessage = $em->getRepository(Message::class)->find($replyToId);
            $message->setReplyTo($replyMessage);
        }

        $conversation->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('app_conversation_show', [
            'id' => $conversation->getId()
        ]);
    }
}