<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Image;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Security\ArticleVoter;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/article')]
class ArticleController extends AbstractController
{
    // ==================== ADMIN : Gestion de tous les articles ====================

    #[Route('/admin/articles', name: 'app_admin_article_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminIndex(Request $request, ArticleRepository $repo, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search', '');
        $qb = $repo->createQueryBuilder('a');

        if ($search) {
            $qb->andWhere('a.title LIKE :search OR a.summary LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('a.publishedAt', 'DESC');

        $articles = $paginator->paginate($qb, $request->query->getInt('page', 1), 20);

        $stats = [
            'total' => $repo->count([]),
            'published' => $repo->count(['isVerified' => true]),
            'drafts' => $repo->count(['isVerified' => false]),
        ];

        return $this->render('admin/articles/index.html.twig', [
            'articles' => $articles,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    #[Route('/admin/articles/{id}/edit', name: 'app_admin_article_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(Request $request, Article $article, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $fileName = $fileUploader->upload($imageFile);
                    $image = new Image();
                    $image->setImage($fileName);
                    $article->addImage($image);
                    $em->persist($image);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Article modifié par l\'administrateur.');
            return $this->redirectToRoute('app_admin_article_index');
        }

        return $this->render('admin/articles/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/admin/articles/{id}/delete', name: 'app_admin_article_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDelete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('admin_delete_article_' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Article supprimé par l\'administrateur.');
        }
        return $this->redirectToRoute('app_admin_article_index');
    }

    #[Route('/admin/image/{id}/delete', name: 'app_admin_image_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteImage(Request $request, Image $image, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_image_' . $image->getId(), $request->request->get('_token'))) {
            // Supprime physiquement le fichier si tu veux
            $em->remove($image);
            $em->flush();
            $this->addFlash('success', 'Image supprimée.');
        } else {
            $this->addFlash('error', 'Token invalide.');
        }
        // Redirige vers la page admin précédente (la liste des articles)
        return $this->redirectToRoute('app_admin_article_index');
    }

    // ==================== UTILISATEUR : Gestion de ses propres articles ====================

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $fileName = $fileUploader->upload($imageFile);
                    $image = new Image();
                    $image->setImage($fileName);
                    $article->addImage($image);
                    $em->persist($image);
                }
            }

            $article->setAuthor($this->getUser());
            $article->setPublishedAt(new \DateTimeImmutable());
            $article->setIsVerified(true);
            $slug = $slugger->slug($article->getTitle())->lower();
            $article->setSlug($slug);

            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Article publié avec succès !');
            return $this->redirectToRoute('app_my_articles');
        }

        return $this->render('article/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/my-articles', name: 'app_my_articles', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myArticles(Request $request, ArticleRepository $repo, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $qb = $repo->createQueryBuilder('a')
            ->where('a.author = :user')
            ->setParameter('user', $user)
            ->orderBy('a.publishedAt', 'DESC');

        $articles = $paginator->paginate($qb, $request->query->getInt('page', 1), 10);

        return $this->render('article/my_articles.html.twig', ['articles' => $articles]);
    }

    #[Route('/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function show(string $slug, ArticleRepository $repo): Response
    {
        $article = $repo->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }
        return $this->render('article/show.html.twig', ['article' => $article]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted(ArticleVoter::EDIT, subject: 'article')]
    public function edit(Request $request, Article $article, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $fileName = $fileUploader->upload($imageFile);
                    $image = new Image();
                    $image->setImage($fileName);
                    $article->addImage($image);
                    $em->persist($image);
                }
                $em->flush();
            }
            $this->addFlash('success', 'Article mis à jour avec succès !');
            return $this->redirectToRoute('app_my_articles');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_article_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted(ArticleVoter::DELETE, subject: 'article')]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Article supprimé.');
        }
        return $this->redirectToRoute('app_my_articles');
    }
}
