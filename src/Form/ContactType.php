<?php

// src/Form/ContactType.php
namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Contact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control', 'id' => 'nom', 'required' => true],
            ])
            ->add('prenom', null, [
                'attr' => ['class' => 'form-control', 'id' => 'prenom', 'required' => true],
            ])
            ->add('email', null, [
                'attr' => ['class' => 'form-control', 'id' => 'email', 'required' => true],
            ])
            ->add('telephone', null, [
                'attr' => ['class' => 'form-control', 'id' => 'telephone', 'required' => true],
            ])
            ->add('address', null, [
                'attr' => ['class' => 'form-control', 'id' => 'address', 'required' => true],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select', 'id' => 'categorie', 'required' => true],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->attachTimestamps(...))
            ;
        }
    
        public function attachTimestamps(PostSubmitEvent $event)
        {
            $data = $event->getData();
            
            if(!($data instanceof Contact)){
                return;
            }
            
            $data->setMiseAJourContact(new \DateTime());
            if(!($data->getId()))
            {
                $data->setCreerContact(new \DateTime());
            }
        }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
