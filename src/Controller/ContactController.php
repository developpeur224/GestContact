<?php

namespace App\Controller;

use App\DTO\ContactDTO;
use App\Entity\Categorie;
use App\Entity\Contact;
use App\EventSubscriber\ContacterNousRequestEvent;
use App\Form\ContactNousType;
use App\Form\ContactType;
use App\Repository\CategorieRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/contact')]
class ContactController extends AbstractController
{

    #[Route('/index', name: 'app_contact_index', methods: ['GET', 'POST'])]
    public function index(ContactRepository $contactRepository, CategorieRepository $categorieRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $roleUser = $this->getUser()->getRoles()[0];
        //Formulaire de Contactez-Nous ContactDTO
        $contactNous = new ContactDTO();
        if ($roleUser == "ROLE_USER") {
            $contacts = $contactRepository->findAllOrderedByUpdateDate(true, $user);
        } else {
            $contacts = $contactRepository->findAllOrderedByUpdateDate();
        }

        $formDTO = $this->createForm(ContactNousType::class, $contactNous);
        //Formulaire de contact
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $context = [
            'formDTO' => $formDTO->createView(),
            'form' => $form->createView(),
            'contacts' => $contacts,
            'categories' => $categorieRepository->findAll(),
        ];
        return $this->render('contact/index.html.twig', $context);
    }


    #[Route('/add', name: 'app_contact_add', methods: ['POST'])]
    public function add(
        Request $request,
        ValidatorInterface $validator,
        ContactRepository $contactRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $action = $request->request->get('action');
        
        if ($action !== 'create') {
            return new JsonResponse(['status' => 'error', 'message' => 'Action non valide']);
        }

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
            // Valider les contraintes d'entité avec le ValidatorInterface
            $errors = $validator->validate($contact);
            if (count($errors) > 0) {
                // Si des violations de contraintes sont détectées, construisez un tableau de messages d'erreur
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse([
                    'status' => 'violation',
                    'message' => 'Erreurs de validation du formulaire.',
                    'errors' => $errorMessages,
                ]); // Utilisez le code 400 pour indiquer un problème de validation
            }
        // Valider le formulaire Symfony
        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est valide et il n'y a pas de violations, persistez l'entité Contact
            $user = $this->getUser();
            $contact->setUser($user);
            $entityManager->persist($contact);
            $entityManager->flush();

            // Récupérer le nombre total d'éléments après l'ajout
            $nbAllElements = $contactRepository->count([]);
            $data = [];
            $roleUser = $this->getUser()->getRoles()[0];
            if ($roleUser == "ROLE_USER") {
                $contacts = $contactRepository->findAllOrderedByUpdateDate(true, $user);
            } else {
                $contacts = $contactRepository->findAllOrderedByUpdateDate();
            }
            foreach ($contacts as $contact) {
                $data[] = [
                    'id' => $contact->getId(),
                    'nom' => $contact->getNom(),
                    'prenom' => $contact->getPrenom(),
                    'email' => $contact->getEmail(),
                    'telephone' => $contact->getTelephone(),
                    'address' => $contact->getAddress(),
                    'creerContact' => $contact->getCreerContact()->format('Y-m-d H:i:s'),
                    'miseAJourContact' => $contact->getMiseAJourContact()->format('Y-m-d H:i:s'),
                    'categorie' => $contact->getCategorie() ? $contact->getCategorie()->getNom () : null,
                    'user' => $contact->getUser() ? $contact->getUser()->getEmail() : null
                ];
            }

            // Retourner une réponse JSON avec les détails du contact ajouté
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Contact ajouté avec succès',
                'contact' => [
                    'number' => $nbAllElements,
                    'id' => $contact->getId(),
                    'nom' => $contact->getNom(),
                    'prenom' => $contact->getPrenom(),
                    'telephone' => $contact->getTelephone(),
                    'email' => $contact->getEmail(),
                    'address' => $contact->getAddress(),
                    'categorie' => [
                        'nom' => $contact->getCategorie()->getNom(),
                    ],
                ],
            ]);
        }

        // Si le formulaire n'est pas valide
        return new JsonResponse(['status' => 'error', 'message' => 'Formulaire non valide'], 400);
    }

    #[Route('/get', name: 'app_contact_get', methods: ['POST'])]
    public function get(Request $request, ContactRepository $contactRepository): JsonResponse
    {
        $contactId = $request->request->get('id');
        $contact = $contactRepository->find($contactId);

        if (!$contact) {
            return new JsonResponse(['status' => 'error', 'message' => 'Contact non trouvé']);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Contact modifié avec succès',
            'contact' => [
                'id' => $contact->getId(),
                'nom' => $contact->getNom(),
                'prenom' => $contact->getPrenom(),
                'telephone' => $contact->getTelephone(),
                'email' => $contact->getEmail(),
                'address' => $contact->getAddress(),
                'categorie' => [
                    'id' => $contact->getCategorie()->getId(),
                    'nom' => $contact->getCategorie()->getNom(),
                ],
            ],
        ]);
    }

    #[Route('/edit', name: 'app_contact_edit', methods: ['POST'])]
    public function edit(
        Request $request, 
        ValidatorInterface $validator, 
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $id = $request->request->get('id');
        $contact = $entityManager->getRepository(Contact::class)->find($id);
        $action = $request->request->get('action');

        if (!$contact) {
            return new JsonResponse(['status' => 'error', 'message' => 'Contact non trouvé']);
        }
        if($action == 'update') {
            $contact->setNom($request->request->get('nom'));
            $contact->setPrenom($request->request->get('prenom'));
            $contact->setEmail($request->request->get('email'));
            $contact->setTelephone($request->request->get('telephone'));
            $contact->setAddress($request->request->get('address'));
            $contact->setCategorie($entityManager->getRepository(Categorie::class)->find($request->request->get('categorie')));
            $contact->setMiseAJourContact(new \DateTime());
            // Valider les contraintes d'entité avec le ValidatorInterface
            $errors = $validator->validate($contact);
            
            if (count($errors) > 0) {
                // Si des violations de contraintes sont détectées, construisez un tableau de messages d'erreur
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse([
                    'status' => 'violation',
                    'message' => 'Erreurs de validation du formulaire.',
                    'errors' => $errorMessages,
                ]); // Utilisez le code 400 pour indiquer un problème de validation
            }
            
            $entityManager->flush();
            
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Contact modifié avec succès',
                'contact' => [
                    'id' => $contact->getId(),
                    'nom' => $contact->getNom(),
                    'prenom' => $contact->getPrenom(),
                    'telephone' => $contact->getTelephone(),
                    'email' => $contact->getEmail(),
                    'address' => $contact->getAddress(),
                    'categorie' => [
                        'id' => $contact->getCategorie()->getId(),
                        'nom' => $contact->getCategorie()->getNom(),
                    ],
                ],
            ]);
        
        }
        return new JsonResponse(['status' => 'error', 'message' => 'Formulaire non valide']);
    }

    #[Route('/delete/{id}', name: 'app.delete.contact', methods: ['GET'])]
    public function del(Contact $contact, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$contact) {
            return new JsonResponse(['status' => 'error', 'message' => 'Contact non trouvé']);
        }
        $entityManager->remove($contact);
        $entityManager->flush();
        return new JsonResponse(['status' => 'success','message' => 'Contact supprimé avec succès']);
    }

    #[Route('/sendEmail', name: 'app.sendEmail', methods: ['POST'])]
    public function sendEmail(
        Request $request,
        ValidatorInterface $validator,
        EventDispatcherInterface $dispatcher,
    ): JsonResponse
    {
        $action = $request->request->get('action');
        if (!$action) {
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur d\'Envoie !']);
        }
        $data = new ContactDTO();
        $user = $this->getUser();
        $formDTO = $this->createForm(ContactNousType::class, $data);
        $formDTO->handleRequest($request);
        $data->nom = $user->nom;
        $data->prenom = $user->prenom;
        $data->telephone = $user->telephone;
        $data->email = $user->email;
        // Valider les contraintes d'entité avec le ValidatorInterface
        $errors = $validator->validate($data);
        if (count($errors) > 0) {
            // Si des violations de contraintes sont détectées, construisez un tableau de messages d'erreur
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse([
                'status' => 'violation',
                'message' => 'Erreurs de validation du formulaire.',
                'errors' => $errorMessages,
            ]); // Utilisez le code 400 pour indiquer un problème de validation
        }
        if ($formDTO->isSubmitted()) {
            // Envoi de message email
            try {
                $dispatcher->dispatch(new ContacterNousRequestEvent($data));
                return new JsonResponse(
                    [
                        'status' => 'success',
                        'message' => 'Email envoyé avec succès !',
                    ]
                    );
            } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Une erreur d\'Envoie !']);

            }
        }
        return new JsonResponse(['status' => 'error', 'message' => 'Erreur est survenu !']);

    }
}