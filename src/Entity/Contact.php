<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[UniqueEntity(fields: ['telephone'], message:'Le numero de telephone est unique !')]
#[UniqueEntity(fields: ['email'], message:'Le numero de telephone est unique !')]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    #[ORM\Column(length: 100)]
    private ?string $nom = null;
    
    #[Assert\Sequentially([
        new Assert\NotBlank(message:"Ce champs n'est pas vide !"),
        new Assert\Length(min: 2, minMessage:'La taille du prenom est supperieur à 2'),
    ])]
    #[ORM\Column(length: 100)]
    private ?string $prenom = null;
    
    #[Assert\Sequentially([
        new Assert\NotBlank(message:"Ce champs n'est pas vide !"),
        new Assert\Email(
            message: 'L\'email {{ value }} n\'est pas valid !.',
        ),
    ])]
    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(message:"Ce champs n'est pas vide !"),
        new Assert\Length(min: 6, minMessage:'La taille du numero est supperieur ou égal à 2'),
    ])]
    #[Assert\Regex(
        pattern:"/^[0-9\+]+$/",
        message:"Seuls les chiffres et le signe + sont autorisés."
    )]
    #[ORM\Column(length: 50)]
    private ?string $telephone = null;

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    #[Assert\Length(min: 4, minMessage: "C'est suppérieur ou egal à 4")]
    #[ORM\Column(length: 50)]
    private ?string $address = null;

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    #[ORM\ManyToOne(inversedBy: 'contact')]
    private ?Categorie $categorie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creerContact = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $miseAJourContact = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getCreerContact(): ?\DateTimeInterface
    {
        return $this->creerContact;
    }

    public function setCreerContact(\DateTimeInterface $creerContact): static
    {
        $this->creerContact = $creerContact;

        return $this;
    }

    public function getMiseAJourContact(): ?\DateTimeInterface
    {
        return $this->miseAJourContact;
    }

    public function setMiseAJourContact(\DateTimeInterface $miseAJourContact): static
    {
        $this->miseAJourContact = $miseAJourContact;

        return $this;
    }

    /**
     * Vérifie si l'email existe déjà dans la base de données.
     */
    public function isEmailExists(ContactRepository $em): bool
    {
        $existingContact = $em->findOneBy(['email' => $this->getEmail()]);
        return $existingContact !== null;
    }

    /**
     * Vérifie si le numéro de téléphone existe déjà dans la base de données.
     */
    public function isTelephoneExists(ContactRepository $em): bool
    {
        $existingContact = $em->findOneBy(['telephone' => $this->getTelephone()]);
        return $existingContact !== null;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}