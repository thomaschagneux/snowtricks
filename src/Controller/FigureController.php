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
        private readonly FigureService $figureService,
    ) {
    }

    #[Route('/add', name: 'app_figure_add', methods: ['GET', 'POST'])]
    public function add(Request $request): RedirectResponse|Response
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $figure = new Figure();
            $figureForm = $this->figureService->createFigureForm($figure, $user);

            $figureForm->handleRequest($request);
            if ($figureForm->isSubmitted() && $figureForm->isValid()) {
                $figureService = $this->figureService;
                $figureService->handlefigureSubmission($figure, $figureForm);

                return $this->redirectToRoute('app_home');
            }

            return $this->render('figure/new.html.twig', [
                'form' => $figureForm->createView(),
            ]);
        }
        $this->addFlash('success', 'Vous devez être connecté pour accéder à cette page.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/{slug}', name: 'app_figure_show', methods: ['GET', 'POST'])]
    public function show(string $slug, Request $request, CommentService $commentService): Response
    {
        $figure = $this->entityManager->getRepository(Figure::class)->findOneBy(['slug' => $slug]);

        if (!$figure instanceof Figure) {
            throw $this->createNotFoundException('La figure n\'existe pas.');
        }

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

    #[Route('/{slug}/edit', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    public function edit(string $slug, Request $request, FigureService $figureService): Response
    {
        $figure = $this->entityManager->getRepository(Figure::class)->findOneBy(['slug' => $slug]);

        if (!$figure instanceof Figure) {
            throw $this->createNotFoundException('La figure n\'existe pas.');
        }

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

    #[Route('/{slug}/delete', name: 'app_figure_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        string $slug,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $figure = $this->entityManager->getRepository(Figure::class)->findOneBy(['slug' => $slug]);

            if (!$figure instanceof Figure) {
                throw $this->createNotFoundException('La figure n\'existe pas.');
            }
            if ($this->isCsrfTokenValid('delete'.$figure->getSlug(), $request->getPayload()->getString('_token'))) {
                $comments = $figure->getComments();
                foreach ($comments as $comment) {
                    $this->entityManager->remove($comment);
                }

                $this->entityManager->remove($figure);
                $this->entityManager->flush();
            } else {
                throw $this->createNotFoundException('Le token est invalide, veuillez rafraichir la page et rééssayer.');
            }

            return $this->redirectToRoute('app_home', []);
        }
        $this->addFlash('error', 'Vous devez être connecté pour effectuer cette action.');

        return $this->redirectToRoute('app_login');
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
