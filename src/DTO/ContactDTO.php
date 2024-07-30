<?php

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;

class ContactDTO {

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    public string $nom = '';

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    public string $prenom = '';

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    public string $telephone = '';

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    public string $titre = '';

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    public string $choiceType = '';

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank(message:"Ce champs n'est pas vide !")]
    public string $description = '';

}