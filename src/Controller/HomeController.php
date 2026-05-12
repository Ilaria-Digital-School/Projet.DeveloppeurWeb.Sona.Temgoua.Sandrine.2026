<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{

    # Route correspondant à la page d'accueil du site
    #[Route('/', name: 'app_home')]
    public function index(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator,
    ): Response {


        /*
        Création d'une requête avec QueryBuilder pour récupérer
        les articles depuis la base de données.
        Ici on les trie par id décroissant pour afficher les plus récents.
        */
        $query = $entityManager
            ->getRepository(Article::class)
            ->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->getQuery();

        /*
        Utilisation du composant KnpPaginator pour paginer les résultats.
        Cela permet d'afficher les articles par pages plutôt que tout charger.
        */
        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 
            16 // Nombre d'articles affichés par page
        );

        # On envoie les articles paginés à la vue Twig
        return $this->render('home/index.html.twig', [
            'articles' => $articles,
        ]);
    }
       
    # Route de la page contact
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        # Affichage de la vue contact
        return $this->render('pages/contact.html.twig');
    }

    # Route de la page à propos du projet
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }
}