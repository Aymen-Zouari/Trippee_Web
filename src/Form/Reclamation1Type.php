<?php

namespace App\Form;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\File as AssertFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Reclamation1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fileContents = file('C:\Users\aymen\Desktop\Integration\src\Form\choices.txt', FILE_IGNORE_NEW_LINES);
        $choices = array();
        foreach ($fileContents as $line) {
            // Trim any whitespace from the beginning and end of the line
            $line = trim($line);
        
            // Use the line as both the label and the value for the choice
            $choices[$line] = $line;
        }
        $choices['Other'] = 'Other';
        
        $builder
            ->add('type', ChoiceType::class,
            array(
                'choices' => $choices ))
            ->add('commentaire')
            ->add('date_creation')

            ->add('image', FileType::class, [
                'label' => 'Image (JPG, JPEG, PNG or GIF file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new AssertFile([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPG, JPEG, PNG or GIF)',
                    ])
                ],
            ])
            
            
          
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
