<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Date is not validated'
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'the start date must be greater than the system date'
                    ])
                ],
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd'
            ])
            ->add('dateFin', DateTimeType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Date is not validated'
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'the end date must be greater than the system date'
                    ])
                ],
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd'
            ])
            ->add('Voiture', HiddenType::class, [
                'data' => $options['id'],
                'mapped' => false,

            ])
      ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('id');
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'id' => null,
        ]);
    }
}