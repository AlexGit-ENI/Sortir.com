<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/profils', name: 'participant_')]
final class ParticipantController extends AbstractController
{
    #[Route('/{id}/modifier', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id) : Response
    {
        //Recuperate the participant with the ID
        $participant = $entityManager->getRepository(Participant::class)->find($id);
        $sites = $entityManager->getRepository(Site::class)->findAll();

        if(!$participant) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        if ($participant->getUserIdentifier() !== $this->getUser()->getUserIdentifier()) {
            $this->addFlash('danger', 'You are not allowed to edit this profile!');
            return $this->redirectToRoute('sorties_list');
        }

        $form = $this->createForm(ParticipantType::class, $participant, [
            'action' => $this->generateUrl('participant_update', ['id' => $id]),
            'method' => 'POST',
//            'sites' => $sites
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                $entityManager->persist($participant);
                $entityManager->flush();
                $this->addFlash('success', 'Idea Successfully Updated !');

                return $this->redirectToRoute('participant_detail', ['id' => $participant->getId()]);

            } catch (\Exception $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('participant/update.html.twig', [
            'form' => $form
        ]);
    }

    /*
        Route pour la page profil d'un participant
     */
    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id, ParticipantRepository $participantRepository): Response
    {

        $participant=$participantRepository->find($id);

        // Pour le debug
        // dd($movie);

        return $this->render('participant/detail.html.twig', [
            'participant' => $participant,
        ]);
    }
}
