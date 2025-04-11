<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\MediaType;
use App\Form\MediaType as FileType;
use App\Repository\MediaRepository;
use App\Repository\MediaTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/media')]
final class MediaController extends AbstractController
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly MediaTypeRepository $mediaTypeRepository,
        private readonly SluggerInterface $slugger,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(name: 'app_media_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('media/index.html.twig', [
            'media' => $this->mediaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_media_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        #[Autowire('%kernel.project_dir%/public/uploads/media')] string $filesDirectory,
    ): Response {
        $medium = new Media();
        $form = $this->createForm(FileType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            $url = $medium->getUrl();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move($filesDirectory, $newFilename);
                    $medium->setPath('uploads/media/'.$newFilename);
                } catch (\Exception) {
                    $errorMessage = 'Il y a eu une erreur lors de l\'upload du fichier';

                    return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
                }
                $mimeType = $file->getClientMimeType();
                $this->mimeType($medium, $mimeType);
            } elseif ($url) {
                $mimeType = $this->guessMimeTypeFromUrl($url);
                $this->mimeType($medium, $mimeType);
            } else {
                $errorMessage = 'Veuillez remplir le formulaire';

                return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($medium);
            $this->entityManager->flush();

            $successMessage = 'Le formulaire a bien été validé';

            return $this->redirectToRoute('app_media_index');
        }

        return $this->render('media/new.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_media_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Media $medium,
        #[Autowire('%kernel.project_dir%/public/uploads/media')] string $filesDirectory,
    ): Response {
        $form = $this->createForm(FileType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            $url = $medium->getUrl();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $filePath = $medium->getPath();
                    $projectDir = $this->getParameter('kernel.project_dir');
                    if (is_string($filePath) && is_string($projectDir)) {
                        $oldFile = $projectDir.'/public/'.$filePath;
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    $file->move($filesDirectory, $newFilename);
                    $medium->setPath('uploads/media/'.$newFilename);
                } catch (\Exception) {
                    $errorMessage = 'Il y a eu une erreur lors de l\'upload du fichier';

                    return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
                }
                $mimeType = $file->getClientMimeType();
                $this->mimeType($medium, $mimeType);
            } elseif ($url) {
                $mimeType = $this->guessMimeTypeFromUrl($url);
                $this->mimeType($medium, $mimeType);
            } else {
                $errorMessage = 'Veuillez remplir le formulaire';

                return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($medium);
            $this->entityManager->flush();

            $successMessage = 'Le formulaire a bien été validé';

            return $this->redirectToRoute('app_media_index');
        }

        return $this->render('media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_media_delete', methods: ['POST'])]
    public function delete(Request $request, Media $medium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medium->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($medium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
    }

    private function getReadableNameFromMimeType(string $mimeType): string
    {
        $mimeMapping = [
            'image/jpeg' => 'Image JPEG',
            'image/png' => 'Image PNG',
            'image/gif' => 'Image GIF',
            'video/mp4' => 'Vidéo MP4',
            'video/mpeg' => 'Vidéo MPEG',
            'audio/mpeg' => 'Audio MP3',
        ];

        return $mimeMapping[$mimeType] ?? 'Autre';
    }

    private function guessMimeTypeFromUrl(string $url): string
    {
        $extensionToMime = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'mpeg' => 'video/mpeg',
            'mp3' => 'audio/mpeg',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
        ];

        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path)) {
            return 'application/octet-stream';
        }
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extensionToMime[$extension] ?? 'application/octet-stream';
    }

    private function mimeType(Media $mediaEntity, string $mimeType): void
    {
        $mediaType = $this->mediaTypeRepository->findOneBy(['mimeType' => $mimeType]);
        if (!$mediaType) {
            $mediaType = new MediaType();
            $mediaType->setMimeType($mimeType);
            $mediaType->setName($this->getReadableNameFromMimeType($mimeType));
            $this->entityManager->persist($mediaType);
            $this->entityManager->flush();
        }
        $mediaEntity->setMediaType($mediaType);
    }
}
