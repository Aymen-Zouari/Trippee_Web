<?php
namespace App\Form;


use App\Entity\Client;
use App\Entity\Chauffeur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Locateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('Password', PasswordType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('Role', ChoiceType::class, [
                'choices' => [
                    'Client' => 'Client',
                    'Chauffeur' => 'Chauffeur',
                    'Locateur' => 'Locateur',
                    'Admin' => 'Admin',
                ],
                'label' => 'Je suis un',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
