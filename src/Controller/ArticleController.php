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
use Symfony\Component\Form\FormError;

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


    // ==================== UTILISATEUR : Gestion de ses propres articles ====================

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Récupérer l'image principale
            $mainImage = $form->get('image')->getData();
            
            // Vérifier si l'image principale est présente
            if (!$mainImage) {
                $this->addFlash('danger', 'Veuillez ajouter une image principale.');
                return $this->render('article/new.html.twig', ['form' => $form->createView()]);
            }
            
            // Upload de l'image principale
            $fileName = $fileUploader->upload($mainImage);
            $article->setImage($fileName);
            
            // Gestion des images supplémentaires
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
            
            // Configuration de l'article
            $article->setAuthor($this->getUser());
            $article->setPublishedAt(new \DateTimeImmutable());
            $article->setIsVerified(true);
            $slug = $slugger->slug($article->getTitle())->lower();
            $article->setSlug($slug);
            
            // Sauvegarde
            $em->persist($article);
            $em->flush();
            
            $this->addFlash('success', 'Article publié avec succès !');
            return $this->redirectToRoute('app_my_articles');
        }
        
        return $this->render('article/new.html.twig', [
            'form' => $form->createView()
        ]);
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


   #[Route('/{id<\d+>}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
#[IsGranted(ArticleVoter::EDIT, subject: 'article')]
public function edit(
    Request $request,
    Article $article,
    EntityManagerInterface $em,
    FileUploader $fileUploader
): Response
{
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // ===========================
        // IMAGE PRINCIPALE
        // ===========================

        $mainImage = $form->get('image')->getData(); // ← AJOUTÉ

        if ($mainImage) { // ← AJOUTÉ
            $fileName = $fileUploader->upload($mainImage); // ← AJOUTÉ
            $article->setImage($fileName); // ← AJOUTÉ
        } // ← AJOUTÉ

        // ===========================
        // IMAGES SECONDAIRES
        // ===========================

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

        $em->flush(); // ← DÉPLACÉ EN DEHORS DU if($imageFiles)

        $this->addFlash('success', 'Article mis à jour avec succès !');

        return $this->redirectToRoute('app_my_articles');
    }

    return $this->render('article/new.html.twig', [
        'form' => $form->createView(),
        'article' => $article,
    ]);
}
    #[Route('/{id<\d+>}/delete', name: 'app_article_delete', methods: ['POST'])]
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

    #[Route('/show/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function show(string $slug, ArticleRepository $repo): Response
    {
        $article = $repo->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }
        return $this->render('article/show.html.twig', ['article' => $article]);
    }
}
