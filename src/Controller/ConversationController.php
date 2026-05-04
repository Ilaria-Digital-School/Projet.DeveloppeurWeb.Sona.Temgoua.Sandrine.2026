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
use App\Security\ConversationVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    /** @var \App\Entity\User $user */
    $user = $this->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException();
    }

    $article = $articleRepository->find($articleId);

    if (!$article) {
        throw $this->createNotFoundException('Article introuvable');
    }

    $seller = $article->getAuthor();

    if (!$seller) {
        throw new \LogicException('Seller introuvable');
    }

    $conversation = $conversationRepository->findOneBy([
    'article' => $article,
    'buyer' => $user,
]);

if (!$conversation) {
    $conversation = new Conversation();
    $conversation->setArticle($article);
    $conversation->setBuyer($user);
    $conversation->setSeller($seller);

    $em->persist($conversation);

    // 💬 message automatique SIMPLE
    $message = new Message();
    $message->setContent("Bonjour, je suis intéressé par votre annonce " . $article->getTitle());
    $message->setCreatedAt(new \DateTimeImmutable());
    $message->setConversation($conversation);
    $message->setSender($user);
    $message->setIsRead(false);
    $message->setArticle($article);

    $em->persist($message);
}

$conversation->setUpdatedAt(new \DateTimeImmutable());

$em->flush();

return $this->redirectToRoute('app_conversation_show', [
    'id' => $conversation->getId()
]);

    }

    #[Route('/conversation/{id}', name: 'app_conversation_show')]
    #[IsGranted(ConversationVoter::VIEW, subject: 'conversation')]
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
    
        // ✅ marquer comme lu
        foreach ($conversation->getMessages() as $msg) {
            
            if ($msg->getSender() !== $user && !$msg->getReadAt()) {
                $msg->setReadAt(new \DateTimeImmutable());
            }
        }
        
        $em->flush();

        $currentUser = $this->getUser();

$otherUser = ($conversation->getBuyer() === $currentUser)
    ? $conversation->getSeller()
    : $conversation->getBuyer();

        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
            'currentUser' => $currentUser,
            'otherUser' => $otherUser
        ]);
    }

    #[Route('/message/send/{id}', name: 'app_message_send', methods: ['POST'])]
public function send(
    Conversation $conversation,
    Request $request,
    EntityManagerInterface $em
): Response {

    $user = $this->getUser();

    $message = new Message();
    $message->setContent($request->request->get('content'));
    $message->setCreatedAt(new \DateTimeImmutable());
    $message->setConversation($conversation);
    $message->setSender($user);
    $message->setIsRead(false);

    // 🔁 reply
    $replyToId = $request->request->get('replyTo');
    if ($replyToId) {
        $replyMessage = $em->getRepository(Message::class)->find($replyToId);
        $message->setReplyTo($replyMessage);
    }

    // 📷 IMAGE
    $imageFile = $request->files->get('image');
    if ($imageFile) {
        $filename = uniqid().'.'.$imageFile->guessExtension();
        $imageFile->move($this->getParameter('uploads_directory'), $filename);
        $message->setImagePath('uploads/'.$filename);
    }

    // 📎 FICHIER
    $fileFile = $request->files->get('file');
    if ($fileFile) {
        $filename = uniqid().'.'.$fileFile->guessExtension();
        $fileFile->move($this->getParameter('uploads_directory'), $filename);
        $message->setFilePath('uploads/'.$filename);
    }

    // 🎵 AUDIO
    $audioFile = $request->files->get('audio');
    if ($audioFile) {
        $filename = uniqid().'.'.$audioFile->guessExtension();
        $audioFile->move($this->getParameter('uploads_directory'), $filename);
        $message->setAudioPath('uploads/'.$filename);
    }

    $conversation->setUpdatedAt(new \DateTimeImmutable());

    $em->persist($message);
    $em->flush();

    return $this->redirectToRoute('app_conversation_show', [
        'id' => $conversation->getId()
    ]);
}
}