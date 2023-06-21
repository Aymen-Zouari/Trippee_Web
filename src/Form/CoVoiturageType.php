<?php

namespace App\Form;

use App\Entity\CoVoiturage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;





class CoVoiturageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add(
                'depart',
                ChoiceType::class,
                array(
                    'choices' => array(
                        '' => '',
                        'Aryanah' => 'Aryanah',
                        'Bizerte' => 'Bizerte',
                        'Beja' => 'Beja',
                        'Tunis' => 'Tunis',
                        'Sfax' => 'Sfax',
                        'Kairouan' => 'Kairouan',
                        'Jandouba' => 'Jandouba',
                        'Ben Arous' => 'Ben Arous',
                        'Manouba' => 'Manouba',
                        'Nabeul' => 'Nabeul',
                        'Zaghouan' => 'Zaghouan',

                    )
                )
            )

            ->add(
                'destination',
                ChoiceType::class,
                array(
                    'choices' => array(
                        '' => '',
                        'Aryanah' => 'Aryanah',
                        'Bizerte' => 'Bizerte',
                        'Beja' => 'Beja',
                        'Tunis' => 'Tunis',
                        'Sfax' => 'Sfax',
                        'Kairouan' => 'Kairouan',
                        'Jandouba' => 'Jandouba',
                        'Ben Arous' => 'Ben Arous',
                        'Manouba' => 'Manouba',
                        'Nabeul' => 'Nabeul',
                        'Zaghouan' => 'Zaghouan',
                    )
                )
            )

            ->add('date_dep', DateTimeType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Date  non valide'
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date  doit être dans le futur'
                    ])
                ],
                'date_widget' => 'single_text',
                'time_widget' => 'single_text'
            ])

            ->add(
                'nmbr_place',
                ChoiceType::class,
                array(
                    'choices' => array(
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                    )
                )
            )
           // ->add('id_ch')


            ->add('cov_img', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1000024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPEG or PNG image',
                    ])
                ],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CoVoiturage::class,
        ]);
    }
}
