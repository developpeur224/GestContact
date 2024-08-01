<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password')
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('imageFile', FileType::class, [
                'mapped'=> false,
            ])
            // ->add('imageFile', VichImageType::class, [
            //     'required' => false,
            //     'allow_delete' => true,
            //     'delete_label' => 'Changer l\'image',
            //     'download_label' => false,
            //     'download_uri' => false,
            //     'image_uri' => false,
            //     'asset_helper' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
