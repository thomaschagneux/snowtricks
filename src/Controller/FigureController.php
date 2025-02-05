<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\User;
use App\Form\FigureType;
use App\Repository\FigureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/figure')]
final class FigureController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(name: 'app_figure_index', methods: ['GET'])]
    public function index(FigureRepository $figureRepository): Response
    {
        return $this->render('figure/index.html.twig', [
            'figures' => $figureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_figure_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $figure = new Figure();

        return $this->handleFigureForm($request, $figure);
    }

    #[Route('/{id}', name: 'app_figure_show', methods: ['GET'])]
    public function show(Figure $figure): Response
    {
        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Figure $figure): Response
    {
        return $this->handleFigureForm($request, $figure);
    }

    #[Route('/{id}', name: 'app_figure_delete', methods: ['POST'])]
    public function delete(Request $request, Figure $figure): Response
    {
        if ($this->isCsrfTokenValid('delete'.$figure->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($figure);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_figure_index', [], Response::HTTP_SEE_OTHER);
    }

    private function handleFigureForm(Request $request, Figure $figure): RedirectResponse|Response
    {
        $form = $this->createForm(FigureType::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$figure->getUser() instanceof User) {
                /** @var User $user */
                $user = $this->getUser();
                $figure->setUser($user);
            }

            $formMediaData = $form->get('media')->getData();

            if (null === $formMediaData) {
                $formMediaData = [];
            }
            $medias = $formMediaData instanceof Collection ? $formMediaData : new ArrayCollection(is_array($formMediaData) ? $formMediaData : []);
            $figure->setMediaCollection($medias);

            $this->entityManager->persist($figure);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_figure_index');
        }

        return $this->render('figure/_form.html.twig', [
            'figure' => $figure,
            'form' => $form->createView(),
        ]);
    }
}
