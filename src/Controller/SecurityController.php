<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    # Route permettant d'afficher la page de connexion
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        # Récupère l'erreur de connexion s'il y en a une (mauvais identifiants par exemple)
        $error = $authenticationUtils->getLastAuthenticationError();

        # Récupère le dernier email ou nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        # Affichage de la page de connexion avec les informations nécessaires
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    # Route utilisée pour la déconnexion de l'utilisateur
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        # Cette méthode reste vide car Symfony intercepte automatiquement
        # la route grâce à la configuration de sécurité (firewall)
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}