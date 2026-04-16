<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

# Préfixe de route pour toutes les fonctionnalités liées à la réinitialisation du mot de passe
#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    # Utilisation d’un trait fourni par SymfonyCasts pour gérer certaines fonctions liées au reset password
    use ResetPasswordControllerTrait;

    # Injection des services nécessaires (helper pour reset password et EntityManager)
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Affiche et traite le formulaire de demande de réinitialisation du mot de passe
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        # Création du formulaire demandant l’email de l’utilisateur
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        # Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            # Récupération de l'email saisi
            /** @var string $email */
            $email = $form->get('email')->getData();

            # Appel de la méthode qui envoie l'email de réinitialisation
            return $this->processSendingPasswordResetEmail($email, $mailer, $translator);
        }

        # Affichage du formulaire
        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Page de confirmation après la demande de réinitialisation
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        # Génère un faux token si aucun utilisateur n'existe pour éviter
        # de révéler si un email est enregistré ou non dans la base
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        # Affiche une page indiquant que l’email de réinitialisation a été envoyé
        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Vérifie le token et permet à l’utilisateur de changer son mot de passe
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        # Si un token est présent dans l'URL
        if ($token) {

            # On stocke le token en session pour éviter qu’il apparaisse dans l’URL
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        # Récupération du token depuis la session
        $token = $this->getTokenFromSession();

        # Si aucun token n'est trouvé
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {

            # Vérifie la validité du token et récupère l'utilisateur associé
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        } catch (ResetPasswordExceptionInterface $e) {

            # Message d'erreur si le token est invalide ou expiré
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        # Création du formulaire permettant de saisir un nouveau mot de passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        # Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {

            # Un token ne doit être utilisé qu’une seule fois
            $this->resetPasswordHelper->removeResetRequest($token);

            # Récupération du nouveau mot de passe
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            # Hashage du nouveau mot de passe pour sécuriser son stockage
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            # Mise à jour en base de données
            $this->entityManager->flush();

            # Nettoyage de la session
            $this->cleanSessionAfterReset();

            # Redirection vers la page d'accueil
            return $this->redirectToRoute('app_home');
        }

        # Affichage du formulaire de changement de mot de passe
        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    # Méthode interne qui gère l’envoi de l’email de réinitialisation
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        # Recherche de l'utilisateur correspondant à l'email
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        # On ne révèle pas si l'utilisateur existe ou non pour des raisons de sécurité
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {

            # Génération du token de réinitialisation
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        } catch (ResetPasswordExceptionInterface $e) {

            return $this->redirectToRoute('app_check_email');
        }

        # Création de l'email contenant le lien de réinitialisation
        $email = (new TemplatedEmail())
            ->from(new Address('contact.share.fr@test.com', 'shareo.fr'))
            ->to((string) $user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        # Envoi de l'email
        $mailer->send($email);

        # Stockage du token dans la session
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}