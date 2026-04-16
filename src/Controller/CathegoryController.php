<?php

namespace App\Controller;

# Import de l'entité Cathegory pour manipuler les catégories
use App\Entity\Cathegory;

# Import du formulaire Symfony utilisé pour créer et modifier une catégorie
use App\Form\CathegoryType;

# Repository permettant de récupérer les catégories depuis la base de données
use App\Repository\CathegoryRepository;

# EntityManager utilisé pour enregistrer, modifier ou supprimer des données
use Doctrine\ORM\EntityManagerInterface;

# Contrôleur de base fourni par Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

# Classe permettant de gérer les requêtes HTTP (GET, POST...)
use Symfony\Component\HttpFoundation\Request;

# Classe utilisée pour renvoyer une réponse HTTP
use Symfony\Component\HttpFoundation\Response;

# Permet de définir les routes grâce aux attributs PHP
use Symfony\Component\Routing\Attribute\Route;

# Préfixe de route : toutes les routes de ce contrôleur commenceront par /cathegory
#[Route('/cathegory')]
final class CathegoryController extends AbstractController
{
    # Route qui affiche la liste de toutes les catégories
    #[Route(name: 'app_cathegory_index', methods: ['GET'])]
    public function index(CathegoryRepository $cathegoryRepository): Response
    {
        # On récupère toutes les catégories via le repository
        return $this->render('cathegory/index.html.twig', [
            'cathegories' => $cathegoryRepository->findAll(),
        ]);
    }

    # Route permettant de créer une nouvelle catégorie
    #[Route('/new', name: 'app_cathegory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        # Création d'une nouvelle instance de Cathegory
        $cathegory = new Cathegory();

        # Création du formulaire basé sur CathegoryType
        $form = $this->createForm(CathegoryType::class, $cathegory);

        # Permet au formulaire de traiter la requête HTTP
        $form->handleRequest($request);

        # Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            # Préparation de l'objet pour l'enregistrement en base
            $entityManager->persist($cathegory);

            # Exécution de la requête pour sauvegarder la catégorie
            $entityManager->flush();

            # Redirection vers la liste des catégories
            return $this->redirectToRoute('app_cathegory_index', [], Response::HTTP_SEE_OTHER);
        }

        # Affichage du formulaire de création
        return $this->render('cathegory/new.html.twig', [
            'cathegory' => $cathegory,
            'form' => $form,
        ]);
    }

    # Route permettant d'afficher le détail d'une catégorie
    #[Route('/{id}', name: 'app_cathegory_show', methods: ['GET'])]
    public function show(Cathegory $cathegory): Response
    {
        # Symfony récupère automatiquement la catégorie correspondant à l'id
        return $this->render('cathegory/show.html.twig', [
            'cathegory' => $cathegory,
        ]);
    }

    # Route permettant de modifier une catégorie en utilisant son slug
    #[Route('/{slug}/edit', name: 'app_cathegory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $slug, EntityManagerInterface $entityManager, CathegoryRepository $cathegoryRepository): Response
    {
        # Recherche de la catégorie correspondant au slug
        $cathegory = $cathegoryRepository->findOneBy(['slug' => $slug]);

        # Création du formulaire de modification
        $form = $this->createForm(CathegoryType::class, $cathegory);

        # Traitement des données envoyées dans la requête
        $form->handleRequest($request);

        # Vérification de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            # Mise à jour des données dans la base
            $entityManager->flush();

            # Redirection vers la liste des catégories
            return $this->redirectToRoute('app_cathegory_index', [], Response::HTTP_SEE_OTHER);
        }

        # Affichage du formulaire de modification
        return $this->render('cathegory/edit.html.twig', [
            'cathegory' => $cathegory,
            'form' => $form,
        ]);
    }

    # Route permettant de supprimer une catégorie
    #[Route('/{id}', name: 'app_cathegory_delete', methods: ['POST'])]
    public function delete(Request $request, Cathegory $cathegory, EntityManagerInterface $entityManager): Response
    {
        # Vérification du token CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete'.$cathegory->getId(), $request->getPayload()->getString('_token'))) {

            # Suppression de la catégorie dans la base de données
            $entityManager->remove($cathegory);

            # Exécution de la suppression
            $entityManager->flush();
        }

        # Redirection vers la liste des catégories
        return $this->redirectToRoute('app_cathegory_index', [], Response::HTTP_SEE_OTHER);
    }
}