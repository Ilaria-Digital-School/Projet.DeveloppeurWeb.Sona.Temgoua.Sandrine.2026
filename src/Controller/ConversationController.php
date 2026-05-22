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
use Knp\Component\Pager\PaginatorInterface;


final class ConversationController extends AbstractController
{
    // ==================== UTILISATEUR : ses propres conversations ====================

    #[Route('/conversations', name: 'app_conversation_list')]
    #[IsGranted('ROLE_USER')]
    public function list(ConversationRepository $repo): Response
    {
        $user = $this->getUser();

        // L'utilisateur ne voit que ses conversations (comme acheteur ou vendeur)
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
    #[IsGranted('ROLE_USER')]
    public function start(
        int $articleId,
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        ConversationRepository $conversationRepository
    ): Response {
        $user = $this->getUser();
        $article = $articleRepository->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }

        $seller = $article->getAuthor();
        if ($seller === $user) {
            $this->addFlash('danger', 'Vous ne pouvez pas discuter avec votre propre annonce.');
            // Correction : redirection vers l'article via son slug
            return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
        }

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
            $em->persist($conversation);
        }

        $conversation->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        return $this->redirectToRoute('app_conversation_show', [
            'id' => $conversation->getId()
        ]);
    }

    #[Route('/conversation/{id}', name: 'app_conversation_show')]
    #[IsGranted(ConversationVoter::VIEW, subject: 'conversation')]
    public function show(Conversation $conversation, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Marquer les messages comme lus (seulement ceux envoyés par l'autre)
        foreach ($conversation->getMessages() as $msg) {
            if ($msg->getSender() !== $user && !$msg->getReadAt()) {
                $msg->setReadAt(new \DateTimeImmutable());
            }
        }
        $em->flush();

        $otherUser = ($conversation->getBuyer() === $user)
            ? $conversation->getSeller()
            : $conversation->getBuyer();

        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
            'currentUser' => $user,
            'otherUser' => $otherUser
        ]);
    }

    #[Route('/message/send/{id}', name: 'app_message_send', methods: ['POST'])]
    #[IsGranted(ConversationVoter::VIEW, subject: 'conversation')]
    public function send(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();

        // Nettoyage du contenu
        $content = trim($request->request->get('content', ''));

        // Fichiers
        $imageFile = $request->files->get('image');
        $fileFile = $request->files->get('file');
        $audioFile = $request->files->get('audio');

        $hasImage = $imageFile && $imageFile->getSize() > 0;
        $hasFile = $fileFile && $fileFile->getSize() > 0;
        $hasAudio = $audioFile && $audioFile->getSize() > 0;

        // ❌ Empêche l'envoi vide
        if (
            empty($content)
            && !$hasImage
            && !$hasFile
            && !$hasAudio
        ) {
            $this->addFlash('danger', 'Le message ne peut pas être vide.');

            return $this->redirectToRoute('app_conversation_show', [
                'id' => $conversation->getId()
            ]);
        }

        $message = new Message();
        $message->setContent($content);
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setConversation($conversation);
        $message->setSender($user);
        $message->setIsRead(false);

        // Réponse à un message
        $replyToId = $request->request->get('replyTo');

        if ($replyToId) {
            $replyMessage = $em->getRepository(Message::class)->find($replyToId);

            if ($replyMessage) {
                $message->setReplyTo($replyMessage);
            }
        }

        // Gestion des fichiers
        $uploadsDir = $this->getParameter('uploads_directory');

        // IMAGE
        if ($imageFile) {
            $filename = uniqid() . '.' . $imageFile->guessExtension();

            $imageFile->move($uploadsDir, $filename);

            $message->setImagePath('uploads/' . $filename);
        }

        // FILE
        if ($fileFile) {
            $filename = uniqid() . '.' . $fileFile->guessExtension();

            $fileFile->move($uploadsDir, $filename);

            $message->setFilePath('uploads/' . $filename);
        }

        // AUDIO
        if ($audioFile) {
            $filename = uniqid() . '.' . $audioFile->guessExtension();

            $audioFile->move($uploadsDir, $filename);

            $message->setAudioPath('uploads/' . $filename);
        }

        $conversation->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('app_conversation_show', [
            'id' => $conversation->getId()
        ]);
    }
    // ==================== ADMIN : gestion complète de toutes les conversations ====================

    #[Route('/admin/conversations', name: 'app_admin_conversation_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        Request $request,
        ConversationRepository $repo,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        $qb = $repo->createQueryBuilder('c')
            ->leftJoin('c.article', 'a')
            ->leftJoin('c.buyer', 'b')
            ->leftJoin('c.seller', 's');

        if ($search) {
            $qb->andWhere('a.title LIKE :search OR b.email LIKE :search OR s.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('c.updatedAt', 'DESC');

        $conversations = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/conversation/list.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/admin/conversation/{id}/delete', name: 'app_admin_conversation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDelete(Request $request, Conversation $conversation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('admin_delete_conversation_' . $conversation->getId(), $request->request->get('_token'))) {
            // Supprime tous les messages liés à cette conversation
            foreach ($conversation->getMessages() as $message) {
                $em->remove($message);
            }
            // Puis supprime la conversation
            $em->remove($conversation);
            $em->flush();
            $this->addFlash('success', 'Conversation supprimée par l\'administrateur.');
        }
        return $this->redirectToRoute('app_admin_conversation_list');
    }

    #[Route('/admin/message/{id}/delete', name: 'app_admin_message_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDeleteMessage(Request $request, Message $message, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('admin_delete_message_' . $message->getId(), $request->request->get('_token'))) {
            $conversationId = $message->getConversation()->getId();
            $em->remove($message);
            $em->flush();
            $this->addFlash('success', 'Message supprimé par l\'administrateur.');
            return $this->redirectToRoute('app_admin_conversation_show', ['id' => $conversationId]);
        }
        return $this->redirectToRoute('app_admin_conversation_list');
    }

    #[Route('/admin/conversation/{id}', name: 'app_admin_conversation_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminShow(Conversation $conversation, EntityManagerInterface $em): Response
    {
        // Marquer tous les messages comme lus (optionnel)
        foreach ($conversation->getMessages() as $msg) {
            if (!$msg->getReadAt()) {
                $msg->setReadAt(new \DateTimeImmutable());
            }
        }
        $em->flush();

        return $this->render('admin/conversation/show.html.twig', [
            'conversation' => $conversation,
        ]);
    }
}
