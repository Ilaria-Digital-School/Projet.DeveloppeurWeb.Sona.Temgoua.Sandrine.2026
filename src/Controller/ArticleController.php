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


#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $repo, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search', '');

        $qb = $repo->createQueryBuilder('a');

        if ($search) {
            $qb->andWhere('a.title LIKE :search OR a.summary LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('a.publishedAt', 'DESC');

        $articles = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );

        // 🔥 AJOUT DES STATS (propre)
        $stats = [
            'total' => $repo->count([]),
            'published' => $repo->count(['isVerified' => true]),
            'drafts' => $repo->count(['isVerified' => false]),
        ];

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'search' => $search,
            'stats' => $stats, // ✅ on envoie à Twig
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
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

            $em->persist($article);
        
            $this->addFlash('success', 'Article publié avec succès !');

            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function show(string $slug, ArticleRepository $repo): Response
    {

        $article = $repo->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
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


            return $this->redirectToRoute('app_article_index');
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
        }

        return $this->redirectToRoute('app_article_index');
    }
}
