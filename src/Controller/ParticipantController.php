<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/profils', name: 'participant_')]
final class ParticipantController extends AbstractController
{
    #[Route('/modifier', name: 'upload')]
    public function new(
        Request $request,
        ParticipantRepository $participantRepository,
        UserPasswordHasherInterface $passwordHasher,
    ) : Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $passwordHasher->hashPassword($participant, $form->get('plainPassword')->getData());
            $hashedPassword = $passwordHasher->hashPassword($participant, $plainPassword);
            $participant->setPassword($hashedPassword);

            $participantRepository->save($participant, true);
            $this->addFlash('Changement enregistré.');
            return $this->redirectToRoute('app_participant');
        }

        return $this->render('participant/index.html.twig', [
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
//
//        return $this->render('participant/index.html.twig', [
//            'controller_name' => 'ParticipantController',
//        ]);
//    }
//    #[Route('/login', name: 'app_login')]
//    public function login(AuthenticationUtils $authenticationUtils): Response
//    {
//        // Si déjà connecté, on redirige
//        if ($this->getUser()) {
//            return $this->redirectToRoute('sorties');
//        }
//
//        $error = $authenticationUtils->getLastAuthenticationError();
//        $lastUsername = $authenticationUtils->getLastAuthenticationError();
//
//        return $this->render('participant/login.html.twig', [
//            'error' => $error,
//            'last_username' => $lastUsername,
//        ]);
//}
}
