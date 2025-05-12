<?php

namespace App\Service;

use App\Entity\Figure;
use App\Entity\User;
use App\Form\FigureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FigureService
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function createFigureForm(Figure $figure, User $user, string $action = 'add'): FormInterface
    {
        $figure->setUser($user);

        return $this->formFactory->create(FigureType::class, $figure, ['action' => $action]);
    }

    public function handlefigureSubmission(Figure $figure, FormInterface $form): void
    {
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Figure $data */
            $data = $form->getData();
            if (null === $data->getName()) {
                throw new \Exception('Le nom de la figure est obligatoire.');
            }
            $slug = $this->slugger->slug($data->getName());
            $data->setFeaturedMedia($figure->getFeaturedMedia());
            $data->setMediaCollection($figure->getMedia());
            $data->setSlug($slug);
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }
}
