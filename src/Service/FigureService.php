<?php

namespace App\Service;

use App\Entity\Figure;
use App\Entity\Media;
use App\Entity\User;
use App\Form\FigureType;
use App\Service\MediaService;
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
        private readonly MediaService $mediaService,
    ) {
    }

    public function createFigureForm(Figure $figure, User $user, string $action = 'add'): FormInterface
    {
        $figure->setUser($user);

        return $this->formFactory->create(FigureType::class, $figure, ['action' => $action]);
    }

    public function handlefigureSubmission(Figure $figure, FormInterface $form, string $uploadDirectory): void
    {
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Figure $data */
            $data = $form->getData();
            if (null === $data->getName()) {
                throw new \Exception('Le nom de la figure est obligatoire.');
            }
            $slug = $this->slugger->slug($data->getName());
            $data->setSlug($slug);


            // Handle new media upload
            if ($form->has('newMedia')) {
                $newMedia = $form->get('newMedia')->getData();
                if ($newMedia instanceof Media) {
                    $file = $form->get('newMedia')->get('file')->getData();
                    $url = $newMedia->getUrl();

                    if ($file || $url) {
                        $media = new Media();

                        if ($file) {
                            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                            $safeFilename = $this->slugger->slug($originalFilename);
                            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                            $file->move($uploadDirectory, $newFilename);
                            $media->setPath('uploads/media/'.$newFilename);

                            $mimeType = $file->getClientMimeType();
                            $this->mediaService->setMediaType($media, $mimeType);
                        } elseif ($url) {
                            $media->setUrl($url);
                            $mimeType = $this->mediaService->guessMimeTypeFromUrl($url);
                            $this->mediaService->setMediaType($media, $mimeType);
                        }

                        $this->entityManager->persist($media);
                        $data->addMedium($media);
                    }
                }
            }

            // Handle new featured media upload
            if ($form->has('newFeaturedMedia')) {
                $newFeaturedMedia = $form->get('newFeaturedMedia')->getData();
                if ($newFeaturedMedia instanceof Media) {
                    $file = $form->get('newFeaturedMedia')->get('file')->getData();
                    $url = $newFeaturedMedia->getUrl();

                    if ($file || $url) {
                        $media = new Media();

                        if ($file) {
                            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                            $safeFilename = $this->slugger->slug($originalFilename);
                            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                            $file->move($uploadDirectory, $newFilename);
                            $media->setPath('uploads/media/'.$newFilename);

                            $mimeType = $file->getClientMimeType();
                            $this->mediaService->setMediaType($media, $mimeType);
                        } elseif ($url) {
                            $media->setUrl($url);
                            $mimeType = $this->mediaService->guessMimeTypeFromUrl($url);
                            $this->mediaService->setMediaType($media, $mimeType);
                        }

                        $this->entityManager->persist($media);
                        $data->setFeaturedMedia($media);
                    }
                }
            }

            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }
}
