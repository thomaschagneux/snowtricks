<?php

// src/Service/MediaService.php

namespace App\Service;

use App\Entity\Figure;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Asset\Packages;

class MediaService
{
    public function __construct(
        private readonly Packages $assetsManager,
    ) {
    }

    /**
     * @param array<int, Media>|Collection<int, Media> $medias
     *
     * @return list<array<string, int|string|false|null>>
     */
    public function prepareMediaData(array|Collection $medias): array
    {
        $mediaData = [];

        if ($medias instanceof Collection) {
            $medias = $medias->toArray();
        }

        foreach ($medias as $media) {
            $link = $media->getPath() ?? $media->getUrl();
            $category = $media->getMediaType()->getCategory();
            $isExternal = filter_var($link, FILTER_VALIDATE_URL);

            if (is_string($link) && $this->isExternalVideo($link)) {
                $category = 'video';
                $link = $this->convertToEmbedUrl($link);
            }

            $mediaData[] = [
                'id' => $media->getId(),
                'link' => $link,
                'category' => $category,
                'mimeType' => $media->getMediaType()->getMimeType(),
                'isExternal' => $isExternal,
            ];
        }

        return $mediaData;
    }

    public function getFeaturedImage(Figure $figure): ?string
    {
        $featuredMedia = $figure->getFeaturedMedia();
        if ($featuredMedia) {
            return $featuredMedia->getPath() ?? $featuredMedia->getUrl();
        }

        $medias = $figure->getMedia();
        if (!$medias->isEmpty()) {
            $firstMedia = $medias->first();

            return $firstMedia->getPath() ?? $firstMedia->getUrl();
        }

        return null;
    }

    public function guessMimeTypeFromUrl(string $url): string
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

        if ($this->isExternalVideo($url)) {
            return 'video/mp4';
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) {
            return 'application/octet-stream';
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extensionToMime[$extension] ?? 'application/octet-stream';
    }

    public function isExternalVideo(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $videoPlatforms = ['youtube.com'];

        foreach ($videoPlatforms as $platform) {
            if (str_contains($url, $platform)) {
                return true;
            }
        }

        return false;
    }

    public function convertToEmbedUrl(string $url): string
    {
        return $this->convertYoutubeUrl($url);
    }

    private function convertYoutubeUrl(string $url): string
    {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        return $url;
    }

    public function getProfilePicture(?User $user): string
    {
        if (!$user || !$user->getPicture()) {
            return $this->generateInitialsAvatar($user);
        }

        $profilePicture = $user->getPicture();

        if ($profilePicture->getUrl()) {
            return $profilePicture->getUrl();
        }

        if ($profilePicture->getPath()) {
            return $this->assetsManager->getUrl($profilePicture->getPath()); // Image locale
        }

        return $this->generateInitialsAvatar($user);
    }

    private function generateInitialsAvatar(?User $user): string
    {
        if (!$user) {
            return '/images/default-avatar.png';
        }

        $initials = strtoupper(mb_substr($user->getFirstName(), 0, 1).mb_substr($user->getLastName(), 0, 1));

        return 'data:image/svg+xml;base64,'.base64_encode($this->generateSvg($initials));
    }

    private function generateSvg(string $initials): string
    {
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50">
                <rect width="100%%" height="100%%" fill="#6c757d"/>
                <text x="50%%" y="50%%" font-size="20" fill="white" text-anchor="middle" alignment-baseline="central">%s</text>
            </svg>',
            $initials
        );
    }
}
