<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $serviceChoices = [];

        foreach ($options['services'] as $service) {
            $serviceChoices[$service['name']] = $service['id'];
        }

        $builder->add('service', ChoiceType::class, [
            'choices' => $serviceChoices,
            'multiple' => false,
            'expanded' => true
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'services' => []
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mind_body_payments_bundle_widget_checkout_form';
    }
}
