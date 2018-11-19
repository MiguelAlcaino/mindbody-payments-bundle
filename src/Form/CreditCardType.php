<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Form;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\CountryAndCitiesService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Luhn;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreditCardType extends AbstractType
{

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var CountryAndCitiesService
     */
    protected $countryAndCitiesService;

    /**
     * CreditCardType constructor.
     * @param ParameterBagInterface $params
     * @param CountryAndCitiesService $countryAndCitiesService
     */
    public function __construct(ParameterBagInterface $params, CountryAndCitiesService $countryAndCitiesService)
    {
        $this->params = $params;
        $this->countryAndCitiesService = $countryAndCitiesService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $paymentMethods = $this->filterPaymentMethods($options['customPaymentMethods']);
        $locations = $options['locations'];
        $years = range(date('Y'), date('Y') + 10);
        $installments = range(1, 36);
        /** @var Customer $customer */
        $customer = $options['customer'];

        if (count($paymentMethods) === 1) {
            $builder->add('paymentMethods', HiddenType::class, [
                'required' => true,
                'data' => current($paymentMethods)
            ]);
        } elseif (count($paymentMethods) > 1) {
            $builder->add('paymentMethods', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => $paymentMethods,
                'data' => current($paymentMethods),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, seleccione un metodo de pago.'
                    ])
                ]
            ]);
        }

        $builder
            ->add('cardHolderName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debes completar el nombre en la tarjeta.'
                    ]),
                    new Length([
                        'minMessage' => 'El nombre en la tarjeta debe contener más de 2 letras.',
                        'min' => 2
                    ])
                ]
            ])
            ->add('cardNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Luhn([
                        'message' => 'El número de tarjeta es inválido.'
                    ]),
                    new Length([
                        'min' => 13,
                        'max' => 19,
                        'minMessage' => 'El número de la tarjeta debe contener al menos 13 digitos',
                        'maxMessage' => 'El número de la tarjeta debe contener al máximo 19 digitos'
                    ])
                ],
                'attr' => [
                    'onkeydown' => 'return ( event.ctrlKey || event.altKey 
                    || (47<event.keyCode && event.keyCode<58 && event.shiftKey==false) 
                    || (95<event.keyCode && event.keyCode<106)
                    || (event.keyCode==8) || (event.keyCode==9) 
                    || (event.keyCode>34 && event.keyCode<40) 
                    || (event.keyCode==46) )',
                    'autocomplete' => 'off',
                    'minlength' => 13,
                    'maxlength' => 19
                ]
            ])
            ->add('month', ChoiceType::class, [
                'empty_data' => null,
                'placeholder' => 'Mes',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'invalid_message' => 'El valor del mes de expiración no es válido.',
                'choices' => [
                    '01' => '01',
                    '02' => '02',
                    '03' => '03',
                    '04' => '04',
                    '05' => '05',
                    '06' => '06',
                    '07' => '07',
                    '08' => '08',
                    '09' => '09',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor seleccione un mes de expiración.'
                    ])
                ]
            ])
            ->add('year', ChoiceType::class, [
                'empty_data' => null,
                'placeholder' => 'Año',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'invalid_message' => 'El valor del año de expiración no es válido.',
                'choices' => array_combine($years, $years),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor seleccione un año de expiración.'
                    ])
                ]
            ])
            ->add('cvc', PasswordType::class, [
                'required' => true,
                'attr' => [
                    'onkeydown' => 'return ( event.ctrlKey || event.altKey 
                    || (47<event.keyCode && event.keyCode<58 && event.shiftKey==false) 
                    || (95<event.keyCode && event.keyCode<106)
                    || (event.keyCode==8) || (event.keyCode==9) 
                    || (event.keyCode>34 && event.keyCode<40) 
                    || (event.keyCode==46) )',
                    'autocomplete' => 'off',
                    'maxlength' => 4,
                    'minlength' => 3
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, completa el CVC.'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 4,
                        'minMessage' => 'El código de seguirdad debe contener al menos 3 digitos.',
                        'maxMessage' => 'El código de seguirdad debe contener al máximo 4 digitos.'
                    ])
                ]
            ])
            ->add('installments', ChoiceType::class, [
                'empty_data' => null,
                'placeholder' => 'Cuotas',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'invalid_message' => 'El número de cuotas no es válido.',
                'choices' => array_combine($installments, $installments),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor seleccione un número de cuotas.'
                    ])
                ]
            ])
            ->add('discountCode', TextType::class, [
                'required' => false
            ]);

        if (is_null($customer->getUserCity()) || is_null($customer->getUserState()) || $customer->getUserState() == 'N/A') {
            $builder->add('country', CountryType::class, [
                'preferred_choices' => [
                    'CO',
                    'US'
                ],
                'data' => $customer->getUserCountry()
            ]);

            $builder->add('state', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'choices' => $this->countryAndCitiesService->getColombianCitiesForForm(),
                'data' => $customer->getUserState(),
                'empty_data' => null,
                'placeholder' => 'Selecciona',
                'required' => false
            ]);

            $builder->add('city', TextType::class, [
                'required' => false
            ]);
        }


        $builder->add('documentType', ChoiceType::class, [
            'multiple' => false,
            'expanded' => false,
            'required' => true,
            'choices' => [
                'Cédula de ciudadanía' => 'Cédula de ciudadanía',
                'Pasaporte' => 'Pasaporte'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Por favor seleccione un tipo de documento de identificación'
                ])
            ]
        ]);

        $builder->add('documentNumber', TextType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Por favor espribe el número del documento'
                ])
            ]
        ]);

        if (!is_null($locations)) {
            $builder->add('preferredLocations', ChoiceType::class, [
                'empty_data' => null,
                'placeholder' => 'Escoge un estudio',
                'choices' => $this->getFormattedLocations($locations),
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'invalid_message' => 'El estudio no es valido. Seleccione un estudio.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor seleccione un estudio.'
                    ])
                ]
            ]);
        }
    }

    /**
     * Filters the mindbody alternative payment methods and leaves just the ones in the
     * parameter "enabled_payment_names"
     * @param array $mindBodyPaymentMethods
     * @return array
     */
    private function filterPaymentMethods($mindBodyPaymentMethods){
        $arrayToReturn = [];
        foreach ($mindBodyPaymentMethods as $key => $mindBodyPaymentMethod){
            foreach ($this->params->get('enabled_payment_names') as $enabledPaymentMethod){
                if($enabledPaymentMethod === $key){
                    $arrayToReturn[$key] = $mindBodyPaymentMethod;
                }
            }
        }

        return $arrayToReturn;
    }

    private function getFormattedLocations($locations)
    {
        $formattedLocations = [];
        foreach ($locations as $location) {
            $formattedLocations[$location['name']] = $location['id'];
        }

        return $formattedLocations;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'customPaymentMethods' => [],
            'locations' => null,
            'customer' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mind_body_payments_bundle_credit_card_type';
    }
}
