<?php

namespace App\Form;

use App\Entity\Product;
use App\Service\CountryAndCitiesService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardWithLimitedDiscountType extends CreditCardType
{
    public function __construct(ParameterBagInterface $params, CountryAndCitiesService $countryAndCitiesService)
    {
        parent::__construct($params, $countryAndCitiesService);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (count($options['products']) == 1) {
            $productTypeClass = HiddenType::class;
            $productsTypeOptions = [
                'data' => $options['products'][0]->getMerchantId(),
                'label' => $options['products'][0]->getName()
            ];
        } else {
            $productTypeClass = EntityType::class;
            $productsTypeOptions = [
                'class' => Product::class,
                'expanded' => true,
                'multiple' => false,
                'choices' => $options['products'],
                'data' => $options['mainProduct'],
                'choice_value' => 'merchantId',
                'mapped' => false,
                'required' => true
            ];
        }

        $builder
            ->add('products', $productTypeClass, $productsTypeOptions)
            ->remove('discountCode');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'products' => [],
            'mainProduct' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mind_body_payments_bundle_credit_card_with_limited_discount_type';
    }
}
