<?php

namespace App\Controller\Visitor\Registration;

use App\Entity\User;
use App\Form\RegistrationForm;
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

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur est déjà connecté, on le redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_visitor_welcome');
        }

        $user = new User();
        // On crée le formulaire d'inscription lié à cet utilisateur
        $form = $this->createForm(RegistrationForm::class, $user);
        // On traite la requête HTTP (remplissage du formulaire)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère le mot de passe brut saisi par l'utilisateur
            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            // On encode le mot de passe et on le stocke dans l'entité User
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setRoles(['ROLE_USER']);

            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            // On envoie un email de confirmation avec un lien sécurisé
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('ultra-psg@gmail.com', 'Luis Enrique'))
                    ->to((string) $user->getEmail())
                    ->subject('Vérification de compte | Ultra PSG')
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            // On redirige l'utilisateur vers une page qui lui demande de vérifier son email
            return $this->redirectToRoute('app_register_waiting_for_email_verification');
        }

        return $this->render('pages/visitor/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/inscription/en-attente-de-la-verification-du-compte', name: 'app_register_waiting_for_email_verification', methods: ['GET'])]
    public function waitingForEmailVerification(): Response
    {
        return $this->render('pages/visitor/registration/waiting_for_email_verification.html.twig');
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        // On récupère l'ID de l'utilisateur depuis l'URL
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        // On cherche l'utilisateur correspondant à cet ID
        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            // Cette méthode va vérifier le lien, activer le compte, et enregistrer la modification
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            // En cas d'erreur (lien expiré, déjà utilisé, etc.), on affiche un message d'erreur
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre email a bien été verifiée, vous pouvez vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
