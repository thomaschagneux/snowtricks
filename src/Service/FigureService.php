<?php

namespace App\Service;

use App\Entity\Figure;
use App\Entity\User;
use App\Form\FigureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class FigureService
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createFigureForm(Figure $figure, User $user): \Symfony\Component\Form\FormInterface
    {
        $figure->setUser($user);

        return $this->formFactory->create(FigureType::class, $figure);
    }

    public function handlefigureSubmission(Figure $figure, $form)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }
}
