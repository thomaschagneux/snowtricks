<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\User;
use App\Form\FigureType;
use App\Service\CommentService;
use App\Service\FigureService;
use App\Service\MediaService;
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
        private readonly MediaService $mediaService,
    ) {
    }

    #[Route('/new', name: 'app_figure_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $figure = new Figure();

        return $this->handleFigureForm($request, $figure, 'add');
    }

    #[Route('/{id}', name: 'app_figure_show', methods: ['GET', 'POST'])]
    public function show(Figure $figure, Request $request, CommentService $commentService): Response
    {
        $mediaData = $this->mediaService->prepareMediaData($figure->getMedia());
        $featuredImage = $this->mediaService->getFeaturedImage($figure);

        $user = $this->getUser();
        $form = null;

        if ($user instanceof User) {
            $form = $commentService->createCommentForm($figure, $user);
            $form->handleRequest($request);

            if ($commentService->handleCommentSubmission($form)) {
                $this->addFlash('success', 'Votre commentaire a été ajouté.');

                return $this->redirectToRoute('app_figure_show', ['id' => $figure->getId()]);
            }
        }

        $commentsData = $commentService->prepareComments($figure->getComments());

        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
            'medias' => $mediaData,
            'featuredImage' => $featuredImage,
            'comments' => $commentsData,
            'commentForm' => $form?->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    public function edit(Figure $figure, Request $request, FigureService $figureService): Response
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $editFigureForm = $figureService->createFigureForm($figure, $user, 'edit');
            $oldFigure = clone $figure;
            $editFigureForm->handleRequest($request);

            $figureService->handlefigureSubmission($oldFigure, $editFigureForm);
        } else {
            $this->addFlash('error', 'Vous devez être connecté pour modifier une figure.');

            return $this->redirectToRoute('app_login');
        }

        return $this->handleFigureForm($request, $figure, 'edit');
    }

    #[Route('/{id}/delete', name: 'app_figure_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Figure $figure): Response
    {
        if ($this->isCsrfTokenValid('delete'.$figure->getId(), $request->getPayload()->getString('_token'))) {
            $comments = $figure->getComments();
            foreach ($comments as $comment) {
                $this->entityManager->remove($comment);
            }

            $this->entityManager->remove($figure);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_home', []);
    }

    private function handleFigureForm(Request $request, Figure $figure, ?string $action): RedirectResponse|Response
    {
        $mediaData = $this->mediaService->prepareMediaData($figure->getMedia());
        $featuredImage = $this->mediaService->getFeaturedImage($figure);

        $form = $this->createForm(FigureType::class, $figure, ['action' => $action ?? 'add']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$figure->getUser() instanceof User) {
                /** @var User $user */
                $user = $this->getUser();
                $figure->setUser($user);
            }

            if ('edit' !== $action) {
                $formMediaData = $form->get('media')->getData();

                if (null === $formMediaData) {
                    $formMediaData = [];
                }
                $medias = $formMediaData instanceof Collection ? $formMediaData : new ArrayCollection(is_array($formMediaData) ? $formMediaData : []);
                $figure->setMediaCollection($medias);
            }

            $this->entityManager->persist($figure);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('figure/_form.html.twig', [
            'figure' => $figure,
            'form' => $form->createView(),
            'featuredImage' => $featuredImage,
            'medias' => $mediaData,
        ]);
    }
}
