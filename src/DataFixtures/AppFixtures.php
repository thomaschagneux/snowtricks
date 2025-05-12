<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Figure;
use App\Entity\FigureGroup;
use App\Entity\Media;
use App\Entity\MediaType;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $figureGroups = $this->loadFigureGroups($manager);
        $mediaTypes = $this->loadMediaTypes($manager);
        $medias = $this->loadMedias($manager, $mediaTypes);
        $user = $this->loadUser($manager, $medias);
        $figures = $this->loadFigures($manager, $figureGroups, $medias, $user);
        $this->loadComments($manager, $figures, $user);

        $manager->flush();
    }

    private function loadFigureGroups(ObjectManager $manager): array
    {
        $figureGroups = [];
        for ($i = 0; $i < 50; ++$i) {
            $figureGroup = new FigureGroup();
            $figureGroup->setName('Groupe figure '.$i);
            $figureGroups[] = $figureGroup;
            $manager->persist($figureGroup);
        }

        return $figureGroups;
    }

    private function loadMediaTypes(ObjectManager $manager): array
    {
        $mediaTypes = [];
        for ($i = 0; $i < 50; ++$i) {
            $mediaType = new MediaType();
            $mediaType->setName('Media Type '.$i);
            $mediaType->setMimeType('image/jpeg');
            $mediaTypes[] = $mediaType;
            $manager->persist($mediaType);
        }

        return $mediaTypes;
    }

    private function loadMedias(ObjectManager $manager, array $mediaTypes)
    {
        $medias = [];
        for ($i = 0; $i < 50; ++$i) {
            $media = new Media();
            $media->setMediaType($mediaTypes[$i]);
            $media->setUrl('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRcGZ-7IfZBD2ndLDeQwDHCAyNVmwDuHCjKzA&s');
            $medias[] = $media;
            $manager->persist($media);
        }

        return $medias;
    }

    private function loadUser(ObjectManager $manager, array $medias)
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'pass123'));
        $user->setFirstName('Thomas');
        $user->setLastName('Chagneux');
        $user->setEmail('thomas.chagneux@gmail.com');
        $user->setPicture($medias[0]);
        $manager->persist($user);

        return $user;
    }

    private function loadFigures(ObjectManager $manager, $figureGroups, $medias, $user)
    {
        $figures = [];
        $mediasArray = [];
        for ($i = 0; $i <= 5; ++$i) {
            $mediasArray[] = $medias[$i];
        }
        $mediaCollection = new ArrayCollection($mediasArray);
        for ($i = 0; $i < 50; ++$i) {
            $figure = new Figure();
            $figure->setUser($user);
            $figure->setName(' Nom figure '.$i);
            $figure->setSlug('nom-figure-'.$i);
            $figure->setFigureGroup($figureGroups[$i]);
            $figure->setMediaCollection($mediaCollection);
            $figure->setFeaturedMedia($medias[$i]);
            $figure->setContent('contenu figure '.$i);
            $figure->setShortDescription('description courte '.$i);
            $figures[] = $figure;
            $manager->persist($figure);
        }

        return $figures;
    }

    private function loadComments(ObjectManager $manager, $figures, $user)
    {
        $comments = [];
        foreach ($figures as $figure) {
            for ($i = 0; $i < 20; ++$i) {
                $comment = new Comment();
                $comment->setUser($user);
                $comment->setFigure($figure);
                $comment->setContent('comment '.$i);
                $comments[] = $comment;
                $manager->persist($comment);
            }
        }

        return $comments;
    }
}
