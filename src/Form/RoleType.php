<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Role;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;



class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
  
        $builder
            ->add('libelle', ChoiceType::class, [
                'choices' => [
                    'Client' => 'client',
                    'Locateur' => 'locateur',
                    'Chauffeur' => 'chauffeur'
                ],
                'label' => 'Role',
            ])
            ->add('idUser', HiddenType::class, [
                'data' => $options['id_user'],
                'mapped' => false,
                
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('id_user');
        $resolver->setDefaults([
            'data_class' => Role::class,
            'id_user' => null,
        ]);
    }
}
