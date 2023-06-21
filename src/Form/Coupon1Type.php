<?php

namespace App\Form;

use App\Entity\Coupon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class Coupon1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date_debut', null, [
            
            'data' => new \DateTime()
            
        ])
            ->add('date_experatio')
            ->add('taux')
            ->add('code_coupon')
            ->add('nbr_utilisation')
            ->add('type')
            ->add('generate_code', ButtonType::class, [
                'label' => 'Générer',
                'attr' => [
                    'onclick' => 'generateCode()',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coupon::class,
        ]);
    }
}
