<?php

namespace App\Form;

use App\Entity\Abonnement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class AbonnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Bronze' => 'Bronze',
                    'Gold' => 'Gold',
                    'Platinum' => 'Platinum',
                ],
                'label' => 'Type',
                'required' => true,
                'placeholder' => 'Choose a type',
            ])
            ->add('dateachat', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['readonly' => true],
            ])
            ->add('dateexpiration', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['readonly' => true],
            ])
            ->add('prix');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Abonnement::class,
        ]);
    }
}
