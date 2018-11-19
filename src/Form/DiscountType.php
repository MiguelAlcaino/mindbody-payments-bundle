<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Form;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Discount $discount */
        $discount = $builder->getData();

        $builder
            ->add('beforeOrAfter', ChoiceType::class, [
                'choices' => [
                    'Antes' => 'before',
                    'Después' => 'after'
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true
            ])
            ->add('discountPercentage', NumberType::class, [
                'data' => is_null($discount) ? 10 : $discount->getDiscountPercentage(),
                'required' => true
            ])
            ->add('days', NumberType::class, [
                'data' => is_null($discount) ? 3 : $discount->getDays(),
                'required' => true
            ])
            ->add('sendAgainEveryNumber', null, [
                'data' => is_null($discount) ? 12 : $discount->getSendAgainEveryNumber(),
                'required' => true
            ])
            ->add('additionalDays', null, [
                'data' => is_null($discount) ? 1 : $discount->getAdditionalDays(),
                'required' => true
            ])
            ->add('products', EntityType::class, [
                'data' => $options['defaultProducts'],
                'class' => Product::class,
                'expanded' => true,
                'multiple' => true,
                'mapped' => false
            ])
            ->add('emailBody', HiddenType::class)
            ->add('emailSubject', TextType::class, [
                'data' => is_null($discount) ? 'Tienes un descuento en Cyglo!' : $discount->getEmailSubject()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Discount::class,
            'defaultProducts' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mind_body_payments_bundle_discount_type';
    }
}
