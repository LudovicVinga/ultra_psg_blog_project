<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\VisitorContactFormType;
use App\Repository\SettingRepository;
use App\Services\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(private SendEmailService $sendEmaiLService)
    {
    }

    #[Route('/contact', name: 'app_visitor_contact_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SettingRepository $settingRepository): Response
    {
        $contact = new Contact();

        $form = $this->createForm(VisitorContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setCreatedAt(new \DateTimeImmutable());

            /**
             * @var User
             */
            $user = $this->getUser();

            if (null != $user) {
                // Si l'email renseigné par le contact correspond a l'email d'un utilisateur
                if ($user->getEmail() == $contact->getEmail()) {
                    $contact->setUser($user);
                }
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            $this->sendEmaiLService->send([
                'sender_email' => 'ultra-psg@gmail.com',
                'sender_full_name' => 'Luis Enrique',
                'recipient_email' => 'ultra-psg@gmail.com',
                'subject' => 'Un contact vous a envoyé un message depuis le blog ULTRA PSG',
                'html_template' => 'emails/contact.html.twig', // Chemin ou trouver le fichier qui contient le template du mail envoyé
                'context' => [
                    'contact' => $contact,
                ],
            ]);

            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('app_visitor_contact_create');
        }

        $settings = $settingRepository->findAll();
        $setting = $settings[0];

        return $this->render('pages/visitor/contact/create.html.twig', [
            'form' => $form->createView(),
            'setting' => $setting,
        ]);
    }
}
