<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

class TransactionController extends AbstractController
{

    # Route permettant d'afficher les articles disponibles à l'achat
    #[Route('/acheter', name: 'app_acheter')]
    public function acheter(
        ArticleRepository $articleRepository, 
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        
        # Création d'une requête pour récupérer les articles dont le type de transaction est "acheter"
        $query = $articleRepository->createQueryBuilder('a')
            ->where('a.transactionType = :type')
    
            ->setParameter('type', 'acheter')
            
            ->orderBy('a.id', 'DESC')
            ->getQuery();
        
        # Pagination des résultats pour limiter le nombre d'articles affichés par page
        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4 // 4 articles par page
        );

        # Envoi des articles à la vue Twig
        return $this->render('pages/acheter.html.twig', [
            'articles' => $articles,
        ]);
    }


    # Route permettant d'afficher les articles disponibles à la location
    #[Route('/louer', name: 'app_louer')]
    public function louer(
        ArticleRepository $articleRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        
        # Requête pour récupérer les articles dont le type est "louer"
        $query = $articleRepository->createQueryBuilder('a')
            ->where('a.transactionType = :type')
            ->setParameter('type', 'louer')
        
            ->orderBy('a.id', 'DESC')
            ->getQuery();
        
        # Pagination des résultats
        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );

        # Affichage de la page louer avec les articles correspondants
        return $this->render('pages/louer.html.twig', [
            'articles' => $articles,
            'pageTitle' => 'Louer',
        ]);
    }

    # Route permettant d'afficher les articles disponibles en don
    #[Route('/don', name: 'app_don')]
    public function don(
        ArticleRepository $articleRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        
        # Requête pour récupérer les articles dont le type est "don"
        $query = $articleRepository->createQueryBuilder('a')
            ->where('a.transactionType = :type')
            ->setParameter('type', 'don')
        
            ->orderBy('a.id', 'DESC')
            ->getQuery();
        
        # Pagination des résultats
        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );

        # Affichage de la page don
        return $this->render('pages/don.html.twig', [
            'articles' => $articles,
            'pageTitle' => 'Don',
        ]);
    }

    # Route permettant d'afficher les articles de type brocante
    #[Route('/brocante', name: 'app_brocante')]
    public function index(
        ArticleRepository $articleRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        
        # Requête pour récupérer les articles dont le type est "brocante"
        $query = $articleRepository->createQueryBuilder('a')
            ->where('a.transactionType = :type')
            ->setParameter('type', 'brocante')       
            ->orderBy('a.id', 'DESC')
            ->getQuery();
        
        # Pagination des résultats
        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );

        # Affichage de la page brocante
        return $this->render('pages/brocante.html.twig', [
            'articles' => $articles,
            'pageTitle' => 'Brocante',
        ]);
    }

    #[Route('/alimentaire', name: 'app_alimentaire')]
public function alimentaire(
    ArticleRepository $articleRepository,
    Request $request,
    PaginatorInterface $paginator
): Response {

    $query = $articleRepository->createQueryBuilder('a')
        ->where('a.transactionType = :type')
        ->setParameter('type', 'alimentaire')
        ->orderBy('a.id', 'DESC')
        ->getQuery();

    $articles = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        4
    );

    return $this->render('pages/alimentaires.html.twig', [
        'articles' => $articles,
        'pageTitle' => 'Produits alimentaires',
    ]);
}
}