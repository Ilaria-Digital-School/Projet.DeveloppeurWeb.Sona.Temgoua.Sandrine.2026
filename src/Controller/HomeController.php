<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Article;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /*
        $user = new User();
		$user->setFirstname('admin');
        $user->setLastname('system');
        $user->setEmail('admin_system@test.com');
        $user->setPassword('admin');
        $user->setIsVerified(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());


        // Hash the password using the UserPasswordHasherInterface.
        $hashedPassword = $userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        // Set the user's roles.
        $user->setRoles(['ROLE_ADMIN']);

        // Perform any necessary operations on the user, such as saving it to the database.
        // For example:
        $entityManager->persist($user);
        $entityManager->flush();
*/
        $article = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('home/index.html.twig', [
            // 'controller_name' => 'HomeController',
            'articles' => $article,
        ]);
    }

    #[Route('/contact', name: 'contact')] public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

}
