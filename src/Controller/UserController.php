<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

# Préfixe de route : toutes les routes de ce contrôleur commenceront par /user
#[Route('/user')]
final class UserController extends AbstractController
{
    # Route permettant d'afficher la liste de tous les utilisateurs
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        # Récupération de tous les utilisateurs depuis la base de données
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    # Route permettant de créer un nouvel utilisateur
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        # Création d'une nouvelle instance de l'entité User
        $user = new User();

        # Création du formulaire basé sur UserType
        $form = $this->createForm(UserType::class, $user);

        # Traitement des données envoyées par la requête
        $form->handleRequest($request);

        # Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            # Préparation de l'objet pour l'enregistrement
            $entityManager->persist($user);

            # Sauvegarde dans la base de données
            $entityManager->flush();

            # Redirection vers la liste des utilisateurs
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        # Affichage du formulaire de création
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    # Route permettant d'afficher le détail d'un utilisateur
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        # Symfony récupère automatiquement l'utilisateur correspondant à l'id
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    # Route permettant de modifier un utilisateur
    #[Route('/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        # Création du formulaire de modification
        $form = $this->createForm(UserType::class, $user);

        # Traitement des données envoyées
        $form->handleRequest($request);

        # Vérification si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {

            # Mise à jour des données dans la base
            $entityManager->flush();

            # Redirection vers la liste des utilisateurs
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        # Affichage du formulaire de modification
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    # Route permettant de supprimer un utilisateur
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        # Vérification du token CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {

            # Suppression de l'utilisateur
            $entityManager->remove($user);

            # Validation de la suppression en base de données
            $entityManager->flush();
        }

        # Redirection vers la liste des utilisateurs
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}