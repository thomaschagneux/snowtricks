<?php

namespace App\Controller;

use App\Repository\FigureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly FigureRepository $figureRepository,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $figures = $this->figureRepository->findAll();

        return $this->render('home/index.html.twig', [
            'figures' => $figures,
        ]);
    }
}
