<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontendController extends AbstractController
{
    #[Route(path: '/', name: 'app_frontend', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }
}
