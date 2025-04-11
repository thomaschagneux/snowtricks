<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Figure;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class CommentService
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createCommentForm(Figure $figure, User $user): FormInterface
    {
        $comment = new Comment();
        $comment->setFigure($figure);
        $comment->setUser($user);

        return $this->formFactory->create(CommentType::class, $comment);
    }

    public function handleCommentSubmission(FormInterface $form): bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param Collection<int, Comment> $comments
     *
     * @return list<array<string, string|null>>
     */
    public function prepareComments(Collection $comments): array
    {
        $commentsData = [];

        foreach ($comments as $comment) {
            $user = $comment->getUser();
            $commentsData[] = [
                'author' => $user instanceof User ? $user->getFullName() : 'Anonyme',
                'content' => $comment->getContent(),
                'avatar' => $user instanceof User ? $this->mediaService->getProfilePicture($user) : '/images/default-avatar.png',
            ];
        }

        return $commentsData;
    }
}
