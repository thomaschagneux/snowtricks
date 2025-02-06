<?php

namespace App\Controller;

use App\Entity\FigureGroup;
use App\Form\FigureGroupType;
use App\Repository\FigureGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/figure-group')]
final class FigureGroupController extends AbstractController
{
    #[Route(name: 'app_figure_group_index', methods: ['GET'])]
    public function index(FigureGroupRepository $figureGroupRepository): Response
    {
        return $this->render('figure_group/index.html.twig', [
            'figure_groups' => $figureGroupRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_figure_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $figureGroup = new FigureGroup();
        $form = $this->createForm(FigureGroupType::class, $figureGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($figureGroup);
            $entityManager->flush();

            return $this->redirectToRoute('app_figure_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('figure_group/new.html.twig', [
            'figure_group' => $figureGroup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_figure_group_show', methods: ['GET'])]
    public function show(FigureGroup $figureGroup): Response
    {
        return $this->render('figure_group/show.html.twig', [
            'figure_group' => $figureGroup,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_figure_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FigureGroup $figureGroup, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FigureGroupType::class, $figureGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_figure_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('figure_group/edit.html.twig', [
            'figure_group' => $figureGroup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_figure_group_delete', methods: ['POST'])]
    public function delete(Request $request, FigureGroup $figureGroup, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$figureGroup->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($figureGroup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_figure_group_index', [], Response::HTTP_SEE_OTHER);
    }
}
