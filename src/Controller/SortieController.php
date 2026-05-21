<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SiteSelectType;
use App\Form\SortieType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties', name: 'sorties_')]
final class SortieController extends AbstractController
{
    // READ

    #[Route('', name: 'list')]
    public function list(SiteRepository $siteRepository, SortieRepository $sortieRepository, Request $request): Response
    {
        // On récupére tous les sites pour les passer au formulaire filtre (SiteSelect)
        $sites = $siteRepository->findAll();

        $form = $this->createForm(SiteSelectType::class, null, [
            'sites' => $sites,
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        // Par défaut, on affiche toutes les sorties
        $sorties = $sortieRepository->findAll();

        // Si le filtre a été utilisé, on récupère l'id du site et on l'utilise pour chercher les sorties par site dans la BDD
        if($form->isSubmitted()) {

            $selectedSite = $request->query->get('site');
            $sorties = $sortieRepository->findBy(['site' => $selectedSite]);
        }

        return $this->render('sortie/list.html.twig', [
            'siteSelectForm' => $form->createView(),
            // on passe l'attribut sorties pour l'afficher dans le template dans les deux cas (toutes et filtrées par site)
            'sorties' => $sorties,
        ]);
    }

    // CREATE

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function createSortie(Request $request, SortieService $sortieService): Response{

        $sortie = new Sortie();

        $createSortieForm = $this->createForm(SortieType::class, $sortie, [
            'action' => $this->generateUrl('sorties_create'),
            'method' => 'POST',
        ]);

        $createSortieForm->handleRequest($request);

        if($createSortieForm->isSubmitted() && $createSortieForm->isValid()) {

            try{
                $sortieService->create($sortie);
                $this->addFlash('success', 'La sortie a bien été créée');

                // TODO : rediriger vers la sortie qui a été créée
                return $this->redirectToRoute('sorties_create');
            }  catch (Exception $e) {
                $this->addFlash('danger', 'La sortie n\'a pas pu être créée en BDD : '. $e->getMessage());
                return $this->redirectToRoute('sorties_create');
            }
        }

        return $this->render('sortie/create.html.twig', [
            'createSortieForm' => $createSortieForm->createView(),
        ]);
    }
}
