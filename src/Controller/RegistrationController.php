<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    # Injection du service EmailVerifier pour gérer la vérification d'email
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    # Route permettant l'inscription d'un nouvel utilisateur
    #[Route('/register', name: 'app_register')]
public function register(
    Request $request,
    UserPasswordHasherInterface $userPasswordHasher,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger
): Response
{
// 🔹 Gestion des messages selon le contexte
    if ($request->query->get('from') === 'publish') {
        $this->addFlash('info', 'Créez un compte pour publier votre annonce et commencer à vendre, donner ou louer facilement 🚀');
    }

    if ($request->query->get('from') === 'contact') {
        $this->addFlash('info', 'Créez un compte pour contacter le vendeur et démarrer la conversation 💬');
    }

    // Création utilisateur
    $user = new User();

    // Formulaire
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    // Si formulaire valide
    if ($form->isSubmitted() && $form->isValid()) {

        /** @var string $plainPassword */
        $plainPassword = $form->get('plainPassword')->getData();

        // Hash mot de passe
        $user->setPassword(
            $userPasswordHasher->hashPassword($user, $plainPassword)
        );
        $slug = $slugger->slug($user->getEmail())->lower();
$user->setSlug($slug);

        // Sauvegarde
        $entityManager->persist($user);
        $entityManager->flush();

        // Email de confirmation
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('contact.sym-E-commerce.cm@test.com', 'sym-E-commerce.cm'))
                ->to((string) $user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        // Message succès
        $this->addFlash('success', 'Inscription réussie ! Vérifiez votre email pour activer votre compte ✉️');

        // REDIRECTION INTELLIGENTE
        $from = $request->query->get('from');
        $articleId = $request->query->get('articleId');

        if ($from === 'contact' && $articleId) {
            return $this->redirectToRoute('app_conversation_start', [
                'articleId' => $articleId
            ]);
        }

        if ($from === 'publish') {
            return $this->redirectToRoute('app_article_new');
        }

        return $this->redirectToRoute('app_home');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form,
    ]);
}
    # Route appelée lorsque l'utilisateur clique sur le lien de vérification d'email
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        # Récupération de l'identifiant de l'utilisateur dans l'URL
        $id = $request->query->get('id');

        # Si l'id est absent, redirection vers l'inscription
        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        # Recherche de l'utilisateur correspondant
        $user = $userRepository->find($id);

        # Si l'utilisateur n'existe pas, redirection
        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        # Vérification du lien de confirmation d'email
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {

            # Message d'erreur si la vérification échoue
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        # Message de succès si l'email est confirmé
        $this->addFlash('success', 'Your email address has been verified.');

        # Redirection après confirmation
        return $this->redirectToRoute('app_register');
    }
    
}