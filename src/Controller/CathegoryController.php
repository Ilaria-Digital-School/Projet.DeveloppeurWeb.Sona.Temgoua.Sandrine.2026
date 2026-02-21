<?php

namespace App\Controller;

use App\Entity\Cathegory;
use App\Form\CathegoryType;
use App\Repository\CathegoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cathegory')]
final class CathegoryController extends AbstractController
{
    #[Route(name: 'app_cathegory_index', methods: ['GET'])]
    public function index(CathegoryRepository $cathegoryRepository): Response
    {
        return $this->render('cathegory/index.html.twig', [
            'cathegories' => $cathegoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cathegory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cathegory = new Cathegory();
        $form = $this->createForm(CathegoryType::class, $cathegory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cathegory);
            $entityManager->flush();

            return $this->redirectToRoute('app_cathegory_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cathegory/new.html.twig', [
            'cathegory' => $cathegory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cathegory_show', methods: ['GET'])]
    public function show(Cathegory $cathegory): Response
    {
        return $this->render('cathegory/show.html.twig', [
            'cathegory' => $cathegory,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_cathegory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $slug, EntityManagerInterface $entityManager, CathegoryRepository $cathegoryRepository): Response
    {
        $cathegory = $cathegoryRepository->findOneBy(['slug' => $slug]);

        $form = $this->createForm(CathegoryType::class, $cathegory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cathegory_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cathegory/edit.html.twig', [
            'cathegory' => $cathegory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cathegory_delete', methods: ['POST'])]
    public function delete(Request $request, Cathegory $cathegory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cathegory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cathegory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cathegory_index', [], Response::HTTP_SEE_OTHER);
    }
}
