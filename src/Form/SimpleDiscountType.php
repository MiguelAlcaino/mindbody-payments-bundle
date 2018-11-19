<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Form;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimpleDiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('discountPercentage')
            ->add('emailSubject')
            ->add('emailBody');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Discount::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mind_body_payments_bundle_simple_discount_type';
    }
}
