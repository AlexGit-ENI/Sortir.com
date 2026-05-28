<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Enum\EtatSortie;
use App\Form\FilterSortieListType;
use App\Form\SiteSelectType;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Doctrine\ORM\Mapping as ORM;
use DateTimeZone;
use Exception;
use mysql_xdevapi\Warning;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/sorties', name: 'sorties_')]
#[IsGranted("ROLE_USER")]
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

        $form = $this->createForm(FilterSortieListType::class, null, [
            'sites' => $sites,
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        // Par défaut, on affiche toutes les sorties avec une mise à jour de leurs états
        $sorties = $sortieService->findAllAndUpdate();

        // Ne pas récuprer les sorties archivées
        $sortiesNotArchived = [];
        foreach ($sorties as $sortie) {
            $isArchived = $sortie->getEtatSortie() === EtatSortie::ARCHIVED;
            if (!$isArchived) {
                $sortiesNotArchived[] = $sortie;
            }
        }

        // Si le filtre a été utilisé, on récupère l'id du site et on l'utilise pour chercher les sorties par site dans la BDD
        if($form->isSubmitted() && $form->isValid()) {

            // Variables qui permettent de vérifier si un élèment du formulaire a été rempli
            $site = $request->query->get('site');
            $search = $request->query->get('search');
            $dateMin = $request->query->get('dateMin');
            $dateMax = $request->query->get('dateMax');


            if($site) {
                try {
                    // Réutilisation du $sortiesNotArchived puisque c'est le modèle qui est passsé au template
                    $sortiesNotArchived = $sortieRepository->findBy(['site' => $site],
                        ['dateHeureDebut' => 'DESC']);
                } catch (Exception $exception) {
                    $this->addFlash('warning', "Impossible de rechercher les sorties par site : ".$exception->getMessage());
                }

                // Si un site a précédemment été sélectionné, je peux retrouver la liste de tous les évenements de tous les sites en enlevant le site
                if($site=""){
                    $sortiesNotArchived = $sortieRepository->findBy(['site' => $site],
                        ['dateHeureDebut' => 'DESC']);
                }
            }

            if($search) {
                try {
                    $sortiesNotArchived =  $sortieService->searchSorties($search);
                } catch (Exception $exception) {
                    $this->addFlash('warning', 'Une erreur est survenue lors de la recherche' . $exception->getMessage());
                }
            }

            if($dateMin) {

                if($dateMax) {
                    try {
                        $sortiesNotArchived = $sortieService->filterSortiesByDate($request->query->get('dateMin'), $request->query->get('dateMax'));
                    } catch (Exception $exception) {
                        $this->addFlash('warning', 'Une erreur est survenue lors de la recherche par date' . $exception->getMessage());
                    }
                } else {
                    $this->addFlash('danger', 'Il faut également préciser une date de fin de recherche');
                }

            }

            if($request->query->all('checkboxes')) {
                // je récupère l'utilisateur ici afin qu'il soit réutilisable dans plusieurs conditions
                $participant = $this->getUser();
                $isRegistered = in_array($participant, $sortie->getListeParticipants()->toArray());

                foreach ($request->query->all('checkboxes') as $checkbox) {

                   switch ($checkbox) {
                       case 'mySorties':
                           // Je cherche les sorties reliées à l'organisatuer par son ID
                           $sortiesNotArchived = $sortieRepository->findBy(['organisateur' => $participant->getId()], ['dateHeureDebut' => 'DESC'] );
                           break;
                       case 'sortiesRegisteredAt':
                           // Je vide le tableaux des sorties pour s'assurer qu'il soit vide au départ
                           $sortiesNotArchived = [];

                           // Je récup toutes les sorties (find aLL AND UPDAte?)
                           $sorties = $sortieRepository -> findAll();

                           foreach ($sorties as $sortie) {
                               // Je transforme l'attribut listeParticipants de chaque sortie en tableau et cherche si elle comporte l'utilisateur courant
                               if (in_array($participant, $sortie->getListeParticipants()->toArray())) {
                                    $sortiesNotArchived[] = $sortie;
                               }
                           }
                           break;

                       case 'sortiesUnregisteredAt':
                           $sortiesNotArchived = [];
                           $sorties = $sortieRepository -> findAll();
                           foreach ($sorties as $sortie) {
                               if (!(in_array($participant, $sortie->getListeParticipants()->toArray()))) {
                                   $sortiesNotArchived[] = $sortie;
                               }
                           }
                           break;

                        case 'pastSorties':
                            // Je cherche les sorties antérieures à la date du jour
                            $sortiesNotArchived = $sortieRepository->findSortiesBeforeDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
                            break;
                        default:
                            $this->addFlash('danger', 'Une erreur est survenue lors de l\'utilisation des filtres');
                            break;
                   }

                   }
                }



        }

        return $this->render('sortie/list.html.twig', [
            'filterSortieListForm' => $form->createView(),
            // on passe l'attribut sorties pour l'afficher dans le template dans les deux cas (toutes et filtrées par site)
            'sorties' => $sortiesNotArchived,

        ]);
    }

    #[Route('/archives', name: 'archives')]
    #[IsGranted("ROLE_ADMIN")]
    public function archives(SiteRepository $siteRepository, SortieRepository $sortieRepository,  SortieService $sortieService, Request $request): Response
    {
        // Par défaut, on affiche toutes les sorties avec une mise à jour de leurs états
        $sorties = $sortieRepository->findAllAndUpdate();

        // Ne pas récuprer les sorties archivées
        $sortiesArchived = [];
        foreach ($sorties as $sortie) {
            $isArchived = $sortie->getEtatSortie() === EtatSortie::ARCHIVED;
            if ($isArchived) {
                $sortiesArchived[] = $sortie;
            }
        }

        return $this->render('sortie/archives.html.twig', [
            'sorties' => $sortiesArchived,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Sortie $sortie, Request $request, SortieRepository $sortieRepository): Response{

        $sortie = $sortieRepository->find($sortie->getId());

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'id' => $sortie->getId(),
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
                return $this->redirectToRoute('sorties_detail', ['id' => $sortie->getId()]);
            }  catch (Exception $e) {
                $this->addFlash('danger', 'La sortie n\'a pas pu être créée en BDD : '. $e->getMessage());
                return $this->redirectToRoute('sorties_create');
            }
        }

        return $this->render('sortie/create.html.twig', [
            'createSortieForm' => $createSortieForm->createView(),
        ]);
    }

    //
    // UPDATE
    //

        #[Route('/{id}/update', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
        public function updateSortie(Request $request, int $id, SortieRepository $sortieRepository,
                                     SortieService $sortieService): Response{

            try {
                $sortie = $sortieRepository->find($id);

                if($sortie === null) {
                    $this->addFlash('danger', 'La sortie n\'existe pas');
                    return $this->redirectToRoute('sorties_list');
                }

            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('sorties_list');
            }

            $updateSortieForm = $this->createForm(SortieType::class, $sortie, [
                'action' => $this->generateUrl('sorties_update', ['id' => $id]),
                'method' => 'POST',
            ]);

            $updateSortieForm->handleRequest($request);
            if($sortie->getOrganisateur() === $this->getUser()) {
                if($updateSortieForm->isSubmitted() && $updateSortieForm->isValid()) {
                    try{
                        $sortieService->persistAndFlush($sortie);
                        $this->addFlash('success', 'La sortie a été modifiée avec succès');
                        return $this->redirectToRoute('sorties_detail', ['id' => $sortie->getId()]);
                    } catch(Exception $e) {
                        $this->addFlash('danger', 'Erreur de modification : ' .$e->getMessage());
                        return $this->redirectToRoute('sorties_update', ['id' => $sortie->getId()]);
                    }
                }

            } else {
                $this->addFlash('danger', 'Seul le créateur de la sortie est autorisé à la modifier');
            }

            return $this->render('sortie/update.html.twig', [
                'sortie' => $sortie,
                'sortieUpdateForm' => $updateSortieForm->createView(),
            ]);
        }

        #[Route('/{id}/inscription', name: 'inscription', requirements: ['id' => '\d+'], methods: ['GET'])]
        public function inscription(Sortie $sortie, EntityManagerInterface $em, SortieService $sortieService): Response {
            $maxInscription = $sortie->getNbInscriptionsMax();
            $sortie = $sortieService->updateEtatSortie($sortie);


            // Vérifier que l'EtatSortie soit à Ouverte pour s'inscrire
            if($sortie->getEtatSortie() !=  EtatSortie::OPEN) {
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
                $sortie->setEtatSortie(EtatSortie::CLOSED);
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

    #[Route('/{id}/publier', name: 'open', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function open(
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

        $sortie->setEtatSortie(EtatSortie::OPEN);

        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'La sortie a été publiée.');

        return $this->redirectToRoute('sorties_list');
    }

}
