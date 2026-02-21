<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    #[Route('/acheter', name: 'app_acheter')]
public function acheter(ArticleRepository $articleRepository): Response
{
    return $this->render('pages/acheter.html.twig', [
        'articles' => $articleRepository->findByTransactionType('acheter'),
    ]);
}


    #[Route('/louer', name: 'app_louer')]
    public function louer(ArticleRepository $articleRepository): Response
    {
        return $this->render('pages/louer.html.twig', [
            'articles' => $articleRepository->findByTransactionType('louer'),
            'pageTitle' => 'Louer',
        ]);
    }

    #[Route('/don', name: 'app_don')]
    public function don(ArticleRepository $articleRepository): Response
    {
        return $this->render('pages/don.html.twig', [
            'articles' => $articleRepository->findByTransactionType('don'),
            'pageTitle' => 'Don',
        ]);
    }

    #[Route('/brocante', name: 'app_brocante')]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('brocante/index.html.twig', [
            'articles' => $articleRepository->findByTransactionType('brocante'),
            'pageTitle' => 'Brocante',
        ]);
    }
}
