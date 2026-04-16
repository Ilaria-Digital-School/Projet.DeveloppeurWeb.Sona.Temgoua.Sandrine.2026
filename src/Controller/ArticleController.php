<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Image;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('search', '');
        
        $paginatedData = $articleRepository->findPaginatedArticles(
            $page,
            10,
            $search,
            $this->getUser()
        );
        
        return $this->render('article/index.html.twig', [
            'articles' => $paginatedData['items'],
            'currentPage' => $paginatedData['currentPage'],
            'totalPages' => $paginatedData['totalPages'],
            'total' => $paginatedData['total'],
            'search' => $search,
            'stats' => $this->getArticleStats($entityManager)
        ]);
    }
    
    #[Route('/search-articles', name: 'app_article_search_ajax', methods: ['GET'])]
    public function searchAjax(Request $request, ArticleRepository $articleRepository): Response
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        
        $paginatedData = $articleRepository->findPaginatedArticles(
            $page,
            10,
            $search,
            $this->getUser()
        );
        
        $html = $this->renderView('article/_table_rows.html.twig', [
            'articles' => $paginatedData['items']
        ]);
        
        $paginationHtml = $this->renderView('article/_pagination.html.twig', [
            'currentPage' => $paginatedData['currentPage'],
            'totalPages' => $paginatedData['totalPages'],
            'search' => $search
        ]);
        
        return $this->json([
            'html' => $html,
            'paginationHtml' => $paginationHtml,
            'total' => $paginatedData['total'],
            'currentPage' => $paginatedData['currentPage'],
            'totalPages' => $paginatedData['totalPages']
        ]);
    }
    
    #[Route('/article/stats', name: 'app_article_stats', methods: ['GET'])]
    public function getStats(EntityManagerInterface $entityManager): Response
    {
        return $this->json($this->getArticleStat($entityManager));
    }
    
    private function getArticleStat(EntityManagerInterface $entityManager): array
    {
        $repo = $entityManager->getRepository(Article::class);
        
        $total = $repo->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
            
        $published = $repo->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.isVerified = true')
            ->getQuery()
            ->getSingleScalarResult();
            
        $drafts = $repo->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.isVerified = false')
            ->getQuery()
            ->getSingleScalarResult();
            
        return [
            'total' => $total,
            'published' => $published,
            'drafts' => $drafts
        ];
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            //dd($form->isValid(), $form->getErrors(true, false), $form->getData());

            $images = $form->get('images')->getData();
            if ($images) {
                dump('Nombre d\'images reçues : ' . count($images));
                foreach ($images as $img) {
                    dump('Image : ' . $img->getClientOriginalName());
                }
            } else {
                dump('AUCUNE IMAGE REÇUE');
            }
            
            $article->setSlug(uniqid('',true)); // Génération d'un slug unique simple
            // Gestion de l'image principale
            $mainImageFile = $form->get('image')->getData();
            if ($mainImageFile) {
                $mainImageFileName = $fileUploader->upload($mainImageFile);
                $article->setImage($mainImageFileName);
            }

            // Gestion des images supplémentaires
            $additionalImages = $form->get('images')->getData();
            if ($additionalImages) {
                foreach ($additionalImages as $imageFile) {
                    $imageFileName = $fileUploader->upload($imageFile);
                    $image = new Image();
                    $image->setImage($imageFileName);
                    $image->setArticle($article);
                    $entityManager->persist($image);
                }
            }

            $article->setAuthor($this->getUser());
            $article->setIsVerified(false);

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            dd($form->isValid(), $form->getErrors(true, false), $form->getData());
            // Gestion de la nouvelle image principale
            $mainImageFile = $form->get('image')->getData();
            if ($mainImageFile) {
                // Supprimer l'ancienne image si nécessaire
                if ($article->getImage()) {
                    $oldImagePath = $fileUploader->getTargetDirectory() . '/' . $article->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $mainImageFileName = $fileUploader->upload($mainImageFile);
                $article->setImage($mainImageFileName);
            }

            // Gestion des nouvelles images supplémentaires
            $additionalImages = $form->get('images')->getData();
            if ($additionalImages) {
                foreach ($additionalImages as $imageFile) {
                    $imageFileName = $fileUploader->upload($imageFile);
                    $image = new Image();
                    $image->setImage($imageFileName);
                    $image->setArticle($article);
                    $entityManager->persist($image);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Article mis à jour avec succès !');
            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Article $article,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {

            // Image principale
            if (!empty($article->getImage())) {
                $imagePath = $fileUploader->getTargetDirectory() . '/' . $article->getImage();

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Images associées
            foreach ($article->getImages() as $image) {
                $imagePath = $fileUploader->getTargetDirectory() . '/' . $image->getImage();

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $entityManager->remove($image); // 🔥 important
            }

            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès !');
        }

        return $this->redirectToRoute('app_article_index');
    }

    #[Route('/image/{id}/delete', name: 'app_image_delete', methods: ['POST'])]
    public function deleteImage(Image $image, EntityManagerInterface $entityManager, FileUploader $fileUploader): JsonResponse
    {
        try {
            $imagePath = $fileUploader->getTargetDirectory() . '/' . $image->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            $entityManager->remove($image);
            $entityManager->flush();
            
            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function getArticleStats(EntityManagerInterface $entityManager): array
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();
        
        return [
            'total' => count($articles),
            'published' => count(array_filter($articles, fn($a) => $a->isVerified())),
            'drafts' => count(array_filter($articles, fn($a) => !$a->isVerified())),
        ];
    }
}