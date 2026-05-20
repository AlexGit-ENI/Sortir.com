<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties', name: 'sorties_')]
final class SortieController extends AbstractController
{


    #[Route('', name: 'list')]
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {
        $site= $request->query->get('site') ?? 'Quimper';
        $sorties = $sortieRepository->findBySite($site);

        return $this->render('sortie/list.html.twig', [
            'site' => $site,
            'sorties' => $sorties,
        ]);
    }
}
