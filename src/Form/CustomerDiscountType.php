<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Form;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerDiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('validFrom', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => "yyyy-MM-dd HH:mm"
            ])
            ->add('validUntil', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => "yyyy-MM-dd HH:mm"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerDiscount::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mind_body_payments_bundle_customer_discount_type';
    }
}
