<?php

namespace App\Controller\Admin\Tag;

use App\Entity\Tag;
use App\Form\AdminTagFormType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class TagController extends AbstractController
{
    #[Route('/tag/index', name: 'app_admin_tag_index', methods: ['GET'])]
    public function index(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();

        return $this->render('pages/admin/tag/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route('/tag/create', name: 'app_admin_tag_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tag = new Tag();

        $form = $this->createForm(AdminTagFormType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setCreatedAt(new \DateTimeImmutable());
            $tag->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash('success', 'Le tag a bien été crée.');

            return $this->redirectToRoute('app_admin_tag_index');
        }

        return $this->render('pages/admin/tag/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tag/edit/{id<\d+>}', name: 'app_admin_tag_edit', methods: ['GET', 'POST'])]
    public function edit(Tag $tag, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminTagFormType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash('success', 'Le tag a bien été modifié.');

            return $this->redirectToRoute('app_admin_tag_index');
        }

        return $this->render('pages/admin/tag/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tag/delete/{id<\d+>}', name: 'app_admin_tag_delete', methods: ['POST'])]
    public function delete(Tag $tag, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid("delete-tag-{$tag->getId()}", $request->request->get('csrf_token'))) {
            $tagName = $tag->getName();

            $entityManager->remove($tag);
            $entityManager->flush();

            $this->addFlash('success', "Le tag: '{$tagName}' a bien été supprimé.");
        }

        return $this->redirectToRoute('app_admin_tag_index');
    }
}
