<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Enum\EtatSortie;
use App\Form\SiteSelectType;
use App\Form\SortieType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use mysql_xdevapi\Warning;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/sorties', name: 'sorties_')]
final class SortieController extends AbstractController
{
    //
    // READ
    //

    #[Route('', name: 'list')]
    public function list(SiteRepository $siteRepository, SortieRepository $sortieRepository,  SortieService $sortieService, Request $request): Response
    {
        // On récupére tous les sites pour les passer au formulaire filtre (SiteSelect)
        $sites = $siteRepository->findAll();

        $form = $this->createForm(SiteSelectType::class, null, [
            'sites' => $sites,
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        // Par défaut, on affiche toutes les sorties avec une mise à jour de leurs états
        $sorties = $sortieRepository->findAllAndUpdate();

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

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    //TODO: limitée au role admin?
    public function detail(int $id, SortieRepository $sortieRepository): Response{

        $sortie = $sortieRepository->find($id);

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    //
    // CREATE
    //

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function createSortie(Request $request, SortieService $sortieService, EntityManagerInterface $entityManager): Response{


        $sortie = new Sortie();

        $createSortieForm = $this->createForm(SortieType::class, $sortie, [
            'action' => $this->generateUrl('sorties_create'),
            'method' => 'POST',
        ]);

        $createSortieForm->handleRequest($request);

        if($createSortieForm->isSubmitted() && $createSortieForm->isValid()) {

            try{
                $id = $this->getUser()->getId();
                $user = $entityManager->getRepository(Participant::class)->find($id);
                $sortieService->create($sortie, $user);
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


        #[Route('/{id}/inscription', name: 'inscription', requirements: ['id' => '\d+'], methods: ['GET'])]
        public function inscription(Sortie $sortie, EntityManagerInterface $em, SortieService $sortieService): Response {
            $maxInscription = $sortie->getNbInscriptionsMax();
            $sortie = $sortieService->updateEtatSortie($sortie);


            // Vérifier que l'EtatSortie soit à Ouverte pour s'inscrire
            if($sortie->getEtatSortie() !=  'Ouverte') {
                $this->addFlash('warning', 'Il faut que la sortie soit ouverte pour s\'inscrire');
            }

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
    }

                                    // Se désister =>>>
    #[Route('/{id}/desister', name: 'desister', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function desister(Sortie $sortie, EntityManagerInterface $em): Response
    {
        /** @var Participant $participant */
        $participant = $this->getUser();

        if (!$sortie->getListeParticipants()->contains($participant)) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('sorties_list');
        }


        $sortie->removeListeParticipant($participant);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Vous avez été désinscrit de la sortie.');
        return $this->redirectToRoute('sorties_list');
    }
                            //Annuler une sortie via l'organisateur (ADMIN) //
    #[Route('/{id}/annuler', name: 'annuler', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function cancel(
        Sortie $sortie,
        EntityManagerInterface $em
    ): Response {

        /** @var Participant $participant */
        $participant = $this->getUser();

        if ($sortie->getOrganisateur() !== $participant) {

            throw $this->createAccessDeniedException(
                'Seul l\'organisateur peut annuler cette sortie.'
            );
        }

        $sortie->setEtatSortie(EtatSortie::CANCELLED);

        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'La sortie a été annulée.');

        return $this->redirectToRoute('sorties_list');
    }

//            if ($sortie->getEtatSortie() === EtatSortie::CANCELLED) {
//
//            $this->addFlash('warning', 'La sortie est déjà annulée.');
//
//            return $this->redirectToRoute('sorties_list');
//        }
//
//        $sortie->setEtatSortie(EtatSortie::CANCELLED);
//
//        $em->persist($sortie);
//        $em->flush();
//
//        $this->addFlash('success', 'La sortie a bien été annulée par l\'organisateur.');
//
//        return $this->redirectToRoute('sorties_list');
//    }

}
