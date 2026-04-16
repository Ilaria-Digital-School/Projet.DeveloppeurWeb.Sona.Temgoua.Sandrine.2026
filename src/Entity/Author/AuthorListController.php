<?php

namespace App\Controller\Author;

use App\Repository\CathegoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/authors')]
class AuthorListController extends AbstractController
{
    #[Route('', name: 'app_author_index', methods: ['GET'])]
    public function authorList(UserRepository $userRepository, CathegoryRepository $cathegoryRepository, EntityManagerInterface $em): Response
    {
        

        $authors = $userRepository->findAuthors();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }
}