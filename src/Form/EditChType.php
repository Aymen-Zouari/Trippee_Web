<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Chauffeur;
use App\Entity\Utilisateur;
use App\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\UtilisateurType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class EditChType extends AbstractType
{

   
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('img',FileType::class, [
                'label' => 'Importer une image',
                'required' => false,
                'mapped' => false,
                ])        
            ->add('cin', TextType::class)
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)        
            ->add('num_permis')
            ->add('gsm')
            ->add('email')
            ->add('idRole',HiddenType::class, [
                'data' => $options['id_role'],
                'mapped' => false,
            ]);
           
            
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['id_role']);
        $resolver->setDefaults([
            'data_class' => Chauffeur::class,
            'id_role' => null,
            
          
        ]);
    }

    
}
