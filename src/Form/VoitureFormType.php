<?php

namespace App\Form;

use App\Entity\Voiture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Controller\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;


class VoitureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matricule')
            ->add('marque', ChoiceType::class,
                array(
                    'choices' => array(
                        'BMW' => 'BMW',
                        'Mercedes' => 'Mercedes',
                        'Audi' => 'Audi',
                        'clio' => 'clio',
                        'porshe' => 'porshe',
                        'peugeot' => 'peugeot',
                        'hamer' => 'hamer',
                    )
                ))
            ->add('puissance', ChoiceType::class,
                array(
                    'choices' => array(
                        '5ch' => '5ch',
                        '6ch' => '6ch',
                        '7ch' => '7ch',
                        '8ch' => '8ch',
                        '9ch' => '9ch',
                        '10ch' => '10ch',
                        '11ch' => '11ch',
                        '12ch' => '12ch',
                        '13ch' => '13ch',

                    )
                ))
            ->add('prixJours')
            ->add('picture', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please upload an image',
                    ]),
                    new File([
                        'maxSize' => '1000024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPEG or PNG image',
                    ])
                ],
            ])
            ->add('energie', ChoiceType::class,
                array(
                    'choices' => array(
                        'essence' => 'essence',
                        'gazoil' => 'gazoil',
                        'gpl' => 'gpl',

                    )
                ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voiture::class,
        ]);
    }
}
