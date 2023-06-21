<?php

namespace App\Form;

use App\Entity\Cadeau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Coupon;
use App\Repository\CouponRepository;

class Cadeau1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_cadeay')
            ->add('reccurence')
            ->add('description')
            ->add('valeur')
            ->add('coupon', EntityType::class, [
                'class' => coupon::class,
                'query_builder' => function (CouponRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->andWhere('c.type = :type')
                        ->setParameter('type', 'vip');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cadeau::class,
        ]);
    }
}
