<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VilleController extends AbstractController
{
    #[Route('/ville', name: 'villes_')]
    public function index(): Response
    {
        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }
}
