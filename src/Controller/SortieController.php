<?php

namespace App\Controller;

use App\Form\SiteSelectType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/sorties', name: 'sorties_')]
final class SortieController extends AbstractController
{


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


        #[Route('/{id}/inscription', name: 'inscription', requirements: ['id' => '\d+'], methods: ['GET'])]
        public function inscription(Sortie $sortie, EntityManagerInterface $em,): Response {
            $maxInscription = $sortie->getNbInscriptionsMax();
            if (count($sortie->getListeParticipants()) >= $maxInscription) {
                $this->addFlash('warning', 'Nombre d\'inscription dépassés'); {
                    try {
                        $sortie =$sortie->find($sortie->getListeParticipants()[0]);
                    }
                    catch (\Exception $e) {
                        $this->addFlash('warning', 'Une erreur est survenue');
                    }
                }

            }

            /** @var Participant $participant */
            $participant = $this->getUser();
            if ($sortie->getListeParticipants()->contains($participant)) {

                $this->addFlash('warning','Vous êtes actuellement inscrit.');
                return $this->redirectToRoute('sorties_list');

            }
            if (new \DateTime() > $sortie->getDateLimiteInscription()) {
                $this->addFlash('warning','La sortie n\'est plus disponible');
                return $this->redirectToRoute('sorties_list');
            }


            $sortie->addListeParticipant($participant);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'Inscription confirmée ! Amusez-vous bien.');
            return $this->redirectToRoute('sorties_list');


//            ROUTE A VERIFIER, OU REDIRECTION "sortie_list"
    }
    }

