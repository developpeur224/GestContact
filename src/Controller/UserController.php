<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\EventSubscriber\UserAuthEvent;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\Randomizer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\ByteString;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET', 'POST'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher, 
        EventDispatcherInterface $dispatcher, 
        EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                $combinaison = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                $shfl = str_shuffle($combinaison);
                $password = substr($shfl, 0, 8);
                // Prepare data for email template
                $data = [
                    'password' => $password,
                    'email' => $form->get('email')->getData(),
                ];
                $dispatcher->dispatch(new UserAuthEvent($data));
            } catch(\Exception $e) {
                $this->addFlash(
                   'error',
                   'Une erreur d\'envoi du message à l\'utilisateur : ' . $e->getMessage()
                );
                return $this->redirectToRoute('app_user_new');
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
       return $this->render('user/show.html.twig',[
        'user' => $user,
       ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Traitez le fichier uploadé
            $user->setImageFile($form->get('imageFile')->getData());
            
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app.delete.user', methods: ['POST', 'GET'])]
    public function supprimer(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'Utilisateur non trouvé']);
        }
        $contacts = $entityManager->getRepository(Contact::class)->findBy(['user' => $user]);
        foreach ($contacts as $contact) {
            $entityManager->remove($contact);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(['status' => 'success','message' => 'Contact supprimé avec succès']);
    }

    #[Route('/edit/profil', name: 'app_profil_edit', methods: ['POST'])]
    public function editProfil(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $id = $request->request->get('id');
        $user = $entityManager->getRepository(User::class)->find($id);
        $action = $request->request->get('action');

        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'Utilisateur non trouvé']);
        }
        if($action == 'update') {
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setTelephone($request->request->get('telephone'));
            $entityManager->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Profil modifié avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'telephone' => $user->getTelephone(),
                ],
            ]);
        
        }
        return new JsonResponse(['status' => 'error', 'message' => 'Formulaire non valide']);
    }
}