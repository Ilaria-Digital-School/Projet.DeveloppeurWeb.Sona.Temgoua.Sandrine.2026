<?php

namespace App\Controller\Author;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/authors')]
final class ShowAuthorController extends AbstractController
{
    #[Route('/show-author/{slug}', name: 'app_show_author')]
    public function showAuthor(string $slug, UserRepository $userRepository): Response
    {
        // je recupère le user dont le slug est dans l'url
        $user = $userRepository->findOneBy(['slug' => $slug]);

        return $this->render('author/show.html.twig', [
            'author' => $user,
        ]);
    }
}
