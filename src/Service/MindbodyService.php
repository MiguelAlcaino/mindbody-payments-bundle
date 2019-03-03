<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 09/10/17
 * Time: 22:28
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use GuzzleHttp\Client;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionItem;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Exception\InvalidItemInShoppingCartException;
use MiguelAlcaino\MindbodyPaymentsBundle\Exception\NoProgramsInTransactionRecordException;
use MiguelAlcaino\MindbodyPaymentsBundle\Exception\NotValidLoginException;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Exception\MindbodyServiceException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MindbodyService
{
    /**
     * @var MB_API
     */
    private $mb;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $adminUser;

    /**
     * @var string
     */
    private $adminPassword;

    /**
     * @var array
     */
    private $siteIds;

    /**
     * @var string
     */
    private $sourceName;

    /**
     * @var  string
     */
    private $sourcePassword;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $enabledPaymentNames;

    /**
     * @var FromSessionService
     */
    private $fromSessionService;

    /**
     * MindBodyService constructor.
     *
     * @param MB_API                $mb
     * @param CacheInterface        $cache
     * @param LoggerInterface       $logger
     * @param ParameterBagInterface $params
     * @param FromSessionService    $fromSessionService
     */
    public function __construct(
        MB_API $mb,
        CacheInterface $cache,
        LoggerInterface $logger,
        ParameterBagInterface $params,
        FromSessionService $fromSessionService
    ) {
        $this->mb                  = $mb;
        $this->cache               = $cache;
        $this->logger              = $logger;
        $this->fromSessionService  = $fromSessionService;
        $this->adminUser           = $params->get('mindbody_admin_user');
        $this->adminPassword       = $params->get('mindbody_admin_password');
        $this->sourceName          = $params->get('mindbody_source_name');
        $this->sourcePassword      = $params->get('mindbody_source_password');
        $this->siteIds             = $params->get('mindbody_site_ids');
        $this->enabledPaymentNames = $params->get('enabled_payment_names');
    }

    /**
     * Returns an array of Mindbody services
     *
     * @param array $locations  Formatted locations array
     * @param bool  $userChache Whether to use cache or not
     *
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getServices($locations, $userChache = true)
    {
        if ($this->cache->has('mindbody.services') && $userChache) {
            $services = $this->cache->get('mindbody.services');
        } else {
            $services = [];
            foreach ($locations as $location) {
                $services[] = $this->mb->GetServices(
                    [
                        'SellOnline'          => true,
                        'LocationID'          => $location['id'],
                        'HideRelatedPrograms' => true,
                    ]
                );
            }
            $this->cache->set('mindbody.services', $services, 604800);
        }

        return $services;
    }

    /**
     * Returns an array with formatted Mindbody Services
     *
     * @param bool $useCache
     *
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getFormattedServices($useCache = true)
    {
        $services          = $this->getServices($this->getFormattedLocations(), $useCache);
        $formattedServices = [];
        foreach ($services[0]['GetServicesResult']['Services']['Service'] as $service) {
            $formattedServices[] = [
                'name'  => $service['Name'],
                'price' => $service['OnlinePrice'],
                'id'    => $service['ID'],
            ];
        }

        usort(
            $formattedServices,
            function ($a, $b) {
                $pos_a = $a['price'];
                $pos_b = $b['price'];

                return $pos_a - $pos_b;
            }
        );

        return $formattedServices;
    }

    /**
     * Returns an array similar to getFormattedServices, but it will remove the 'Online Store' location
     *
     * @return array
     */
    public function getRealLocations()
    {
        $locations = $this->getFormattedLocations();

        foreach ($locations as $key => $location) {
            if (array_key_exists('name', $location)) {
                if ($location['name'] === 'Online Store') {
                    unset($locations[$key]);
                }
            } elseif (array_key_exists('Name', $location)) {
                if ($location['Name'] === 'Online Store') {
                    unset($locations[$key]);
                }
            }
        }

        return $locations;
    }

    /**
     * Returns an array with id and name of the different Mindbody locations
     *
     * @return array
     */
    public function getFormattedLocations()
    {
        $locations          = $this->getLocations();
        $formattedLocations = [];
        if (array_key_exists('SiteID', $locations['GetLocationsResult']['Locations']['Location'])) {
            $formattedLocations = [
                [
                    'id'   => $locations['GetLocationsResult']['Locations']['Location']['ID'],
                    'name' => $locations['GetLocationsResult']['Locations']['Location']['Name'],
                ],
            ];
        } else {
            foreach ($locations['GetLocationsResult']['Locations']['Location'] as $location) {
                $formattedLocations[] = [
                    'id'   => $location['ID'],
                    'name' => $location['Name'],
                ];
            }
        }

        return $formattedLocations;
    }

    /**
     * Get a cached array of Mindbody locations
     *
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getLocations()
    {
        if ($this->cache->has('mindbody.locations')) {
            $locations = $this->cache->get('mindbody.locations');
        } else {
            $locations = $this->mb->GetLocations();
            $this->cache->set('mindbody.locations', $locations, 604800);
        }

        return $locations;
    }

    /**
     * Returns a specific location by a given $id
     *
     * @param int|string $id
     *
     * @return null
     */
    public function getLocationNameById($id)
    {
        $locations = $this->getFormattedLocations();
        foreach ($locations as $location) {
            if ($location['id'] === $id) {
                return $location['name'];
            }
        }

        return null;
    }

    /**
     * Returns a client's last purchases done between yesterday and now
     *
     * @param int|string $clientId
     *
     * @return array
     * @throws \Exception
     */
    public function getClientPurchases($clientId)
    {
        $clientPurchasesResult = $this->mb->GetClientPurchases(
            [
                'ClientID'  => $clientId,
                'StartDate' => (new \DateTime())->sub(new \DateInterval('P1D'))->format('Y-m-d'),
            ]
        );

        return $clientPurchasesResult;
    }

    /**
     * Returns the last purchase of a client in Mindbody by a given client's $id
     *
     * @param int|string $clientId
     *
     * @return mixed
     * @throws \Exception
     */
    public function getClientLastPurchase($clientId)
    {
        $clientPurchasesResult = $this->getClientPurchases($clientId);

        if (array_key_exists('Sale', $clientPurchasesResult['GetClientPurchasesResult']['Purchases']['SaleItem'])) {
            $purchase = $clientPurchasesResult['GetClientPurchasesResult']['Purchases']['SaleItem'];
        } else {
            $purchase = end($clientPurchasesResult['GetClientPurchasesResult']['Purchases']['SaleItem']);
        }

        return $purchase;
    }

    /**
     * Updates client's preferred location in Mindbody
     *
     * @param $clientId
     * @param $locationId
     * @param $country
     * @param $state
     * @param $city
     */
    public function updateClientLocation($clientId, $locationId, $country, $state, $city)
    {
        $clientArray = [
            'UserCredentials' => [
                'Username'   => $this->adminUser,
                'Password'   => $this->adminPassword,
                'SiteIDs'    => $this->siteIds,
                'LocationID' => 0,
            ],
            'Clients'         => [
                'Client' => [
                    'ID' => $clientId,
                ],
            ],
        ];

        if (!is_null($locationId)) {
            $clientArray['Clients']['Client']['HomeLocation']['ID'] = $locationId;
        }

        if (!is_null($country) || !is_null($state) || !is_null($city)) {
            $clientArray['Clients']['Client']['Country'] = $country;
            $clientArray['Clients']['Client']['State']   = $state;
            $clientArray['Clients']['Client']['City']    = $city;
        }

        $this->mb->AddOrUpdateClients($clientArray);
    }

    /**
     * Returns a collection of Mindbody clients by a given array of ids
     *
     * @param array $userIds
     *
     * @return mixed
     */
    public function getClients(array $userIds)
    {
        return $this->mb->GetClients(
            [
                'UserCredentials' => [
                    'Username'   => $this->adminUser,
                    'Password'   => $this->adminPassword,
                    'SiteIDs'    => $this->siteIds,
                    'LocationID' => 0,
                ],
                'ClientIDs'       => $userIds,
            ]
        );
    }

    /**
     * Returns a collection of Mindbody clients by a given $searched text string. This string can be a part of a
     * name or email
     *
     * @param string $searchedText
     *
     * @return mixed
     */
    public function getClientsBySearchText(string $searchedText)
    {
        return $this->mb->GetClients(
            [
                'UserCredentials' => [
                    'Username'   => $this->adminUser,
                    'Password'   => $this->adminPassword,
                    'SiteIDs'    => $this->siteIds,
                    'LocationID' => 0,
                ],
                'SearchText'      => $searchedText,
                'Fields'          => 'Clients.CustomClientFields',
            ]
        );
    }

    /**
     * Returns a formatted array of Mindbody clients by a given $searchedText
     *
     * @param string $searchedText
     *
     * @return array
     */
    public function getClientsBySearchTextFormatted(string $searchedText)
    {
        $clients = $this->getClientsBySearchText($searchedText);
        if (count($clients['GetClientsResult']['Clients']) === 0) {
            return [];
        } else {
            if (array_key_exists('FirstName', $clients['GetClientsResult']['Clients']['Client'])) {
                return [$clients['GetClientsResult']['Clients']['Client']];
            } else {
                return $clients['GetClientsResult']['Clients']['Client'];
            }
        }
    }

    /**
     * Returns the calculation of the shopping cart
     *
     * @param int|string       $clientId      - Client's mindbody id
     * @param int|string|array $itemId        - Item's id
     * @param int|string|null  $promotionCode - optional promo code
     *
     * @return mixed
     * @throws InvalidItemInSHoppingCartException
     */
    public function calculateShoppingCart($clientId, $itemId, $promotionCode = null)
    {
        $client = new \SoapClient(
            'https://api.mindbodyonline.com/0_5_1/SaleService.asmx?WSDL', [
                'soap_version'   => SOAP_1_1,
                'trace'          => true,
                'stream_context' => stream_context_create(
                    [
                        'ssl' => [
                            'crypto_method'     => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                            'ciphers'           => 'SHA256',
                            'verify_peer'       => false,
                            'verify_peer_name'  => false,
                            'allow_self_signed' => true,
                        ],
                    ]
                ),
            ]
        );

        $cartItems = [];
        if (!is_array($itemId)) {
            $originalItemId = $itemId;

            $itemId   = [];
            $itemId[] = [
                'id'       => $originalItemId,
                'quantity' => 1,
                'type'     => 'Service',
            ];
        }

        foreach ($itemId as $item) {
            $cartItems[] = [
                'Item'           => new \SoapVar(
                    [
                        'ID' => $item['id'],
                    ],
                    SOAP_ENC_ARRAY,
                    $item['type'],
                    'http://clients.mindbodyonline.com/api/0_5_1'
                ),
                'Quantity'       => array_key_exists('quantity', $item) ? $item['quantity'] : 1,
                'DiscountAmount' => 0,
            ];
        }

        $request = [
            'Request' => [
                'SourceCredentials' => [
                    "SourceName" => $this->sourceName,
                    "Password"   => $this->sourcePassword,
                    "SiteIDs"    => $this->siteIds,
                ],
                'ClientID'          => $clientId,
                'UserCredentials'   => [
                    'Username'   => $this->adminUser,
                    'Password'   => $this->adminPassword,
                    'SiteIDs'    => $this->siteIds,
                    'LocationID' => 0,
                ],
                'InStore'           => true,
                'Test'              => true,
                'Fields'            => ['paymentcheck'],
                'CartItems'         => [
                    'CartItem' => $cartItems,
                ],
            ],
        ];

        $result = $client->CheckoutShoppingCart($request);
        $result = json_decode(json_encode($result), 1);

        if ($result['CheckoutShoppingCartResult']['ErrorCode'] === 905) {
            throw new InvalidItemInSHoppingCartException(
                $result['CheckoutShoppingCartResult']['Message'],
                $result['CheckoutShoppingCartResult']['ErrorCode']
            );
        }

        return $result;
    }

    /**
     * Returns an array of Mindbody client's services
     *
     * @param int|string $clientId
     * @param array      $programIds     Array with programs ids. The return of the getPrograms method should be used here
     * @param bool       $showActiveOnly Whether or not to return just the active services
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClientServicesFormatted($clientId, $programIds = [], $showActiveOnly = true)
    {
        $clientServices = $this->getClientServicesSoap($clientId, $programIds, $showActiveOnly);

        $clientServicesFormatted = [];
        //        dump($clientServices);
        if (count($clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']) > 0) {
            if (
            array_key_exists(
                'Current',
                $clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']['ClientService']
            )
            ) {
                $clientService             = $clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']['ClientService'];
                $clientServicesFormatted[] = [
                    'current'        => $clientService['Current'],
                    'id'             => $clientService['ID'],
                    'name'           => $clientService['Name'],
                    'paymentDate'    => $clientService['PaymentDate'],
                    'activeDate'     => $clientService['ActiveDate'],
                    'expirationDate' => $clientService['ExpirationDate'],
                ];
            } else {
                foreach ($clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']['ClientService'] as $clientService) {
                    $clientServicesFormatted[] = [
                        'current'        => $clientService['Current'],
                        'id'             => $clientService['ID'],
                        'name'           => $clientService['Name'],
                        'paymentDate'    => $clientService['PaymentDate'],
                        'activeDate'     => $clientService['ActiveDate'],
                        'expirationDate' => $clientService['ExpirationDate'],
                    ];
                }
            }
        }

        return $clientServicesFormatted;
    }

    /**
     * Returns the array response corresponding to Mindbody clients Services using native php Soap client
     *
     * @param int|string $clientId
     * @param array      $programIds     Mindbody programs ids if not given the site's programs will be used
     * @param bool       $showActiveOnly Whether or not to return just the active services
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClientServicesSoap($clientId, $programIds = [], $showActiveOnly = true)
    {
        $soapPrograms = '';
        if (count($programIds) > 0) {
            $programs = $this->getPrograms();

            foreach ($programs as $program) {
                $soapPrograms .= '<int>' . $program['id'] . '</int>';
            }
        } else {
            foreach ($programIds as $programId) {
                $soapPrograms .= '<int>' . $programId . '</int>';
            }
        }

        $body       = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://clients.mindbodyonline.com/api/0_5_1">
                <soapenv:Header/>
                <soapenv:Body>
                   <GetClientServices>
                      <Request>
                         <SourceCredentials>
			               <SourceName>' . $this->sourceName . '</SourceName>
			               <Password>' . $this->sourcePassword . '</Password>
			               <SiteIDs>
			                  <int>' . $this->siteIds[0] . '</int>
			               </SiteIDs>
			            </SourceCredentials>
                         <ClientID>' . $clientId . '</ClientID>
                         <ProgramIDs>' . $soapPrograms . '</ProgramIDs>
                         <ShowActiveOnly>' . ($showActiveOnly ? 'true' : 'false') . '</ShowActiveOnly>
                      </Request>
                   </GetClientServices>
                </soapenv:Body>
             </soapenv:Envelope>
';
        $httpClient = new Client();
        $result     = $httpClient->request(
            'POST',
            'https://api.mindbodyonline.com/0_5_1/ClientService.asmx',
            [
                'body'    => $body,
                'headers' => [
                    "Content-Type" => "text/xml; charset=utf-8",
                    'SOAPAction'   => 'http://clients.mindbodyonline.com/api/0_5_1/GetClientServices',
                    'Host'         => 'api.mindbodyonline.com',
                ],
            ]
        );

        $response = $result->getBody()->getContents();

        $xml    = new \SimpleXMLElement($response);
        $output = $xml->xpath('//soap:Body')[0];
        $array  = json_decode(json_encode((array)$output), true);

        return $array;
    }

    /**
     * Executes a Mindbody purchase using the native Soap client
     *
     * @param int|string $clientId
     * @param array      $productItems
     * @param int|string $paymentMethodId - Mindbody's payment method id. If its value is "CashInfo" it will pay with Cash
     * @param int|string $grandTotal
     * @param bool       $test
     * @param null       $promotionCode
     * @param int        $discountAmount
     *
     * @return mixed
     * @throws MindbodyServiceException
     */
    public function purchaseShoppingCartWithSoap(
        $clientId,
        array $productItems,
        $paymentMethodId,
        $grandTotal,
        $test = true,
        $promotionCode = null,
        $discountAmount = 0
    ) {
        if ($paymentMethodId === 'CashInfo') {
            $paymentInfo = new \SoapVar(
                [
                    'Amount' => $grandTotal,
                ],
                SOAP_ENC_ARRAY,
                'CashInfo',
                'http://clients.mindbodyonline.com/api/0_5_1'
            );
        } else {
            $paymentInfo = new \SoapVar(
                [
                    'ID'     => $paymentMethodId,
                    'Amount' => $grandTotal,
                ],
                SOAP_ENC_ARRAY,
                'CustomPaymentInfo',
                'http://clients.mindbodyonline.com/api/0_5_1'
            );
        }

        $client = new \SoapClient(
            'https://api.mindbodyonline.com/0_5_1/SaleService.asmx?WSDL', [
                'soap_version'   => SOAP_1_1,
                'trace'          => true,
                'stream_context' => stream_context_create(
                    [
                        'ssl' => [
                            'crypto_method'     => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                            'ciphers'           => 'SHA256',
                            'verify_peer'       => false,
                            'verify_peer_name'  => false,
                            'allow_self_signed' => true,
                        ],
                    ]
                ),
            ]
        );

        $cartItems = [];

        foreach ($productItems as $item) {
            $cartItems[] = [
                'Item'           => new \SoapVar(
                    [
                        'ID'                 => $item['id'],
                        'DiscountPercentage' => 0,
                        'SellOnline'         => false,
                    ],
                    SOAP_ENC_ARRAY,
                    $item['type'],
                    'http://clients.mindbodyonline.com/api/0_5_1'
                ),
                'Quantity'       => array_key_exists('quantity', $item) ? $item['quantity'] : 1,
                'DiscountAmount' => 0,
            ];
        }

        $request = [
            'Request' => [
                'SourceCredentials' => [
                    "SourceName" => $this->sourceName,
                    "Password"   => $this->sourcePassword,
                    "SiteIDs"    => $this->siteIds,
                ],
                'ClientID'          => $clientId,
                'UserCredentials'   => [
                    'Username'   => $this->adminUser,
                    'Password'   => $this->adminPassword,
                    'SiteIDs'    => $this->siteIds,
                    'LocationID' => 0,
                ],
                'InStore'           => true,
                'CartItems'         => [
                    'CartItem' => $cartItems,
                ],
                'Payments'          => [
                    'PaymentInfo' => $paymentInfo,
                ],
                'Test'              => $test,
                'SendEmail'         => !$test,
            ],
        ];
        if (!is_null($promotionCode)) {
            $shoppingCartArray['Request']['PromotionCode'] = $promotionCode;
        }

        $numberOfTries  = 3;
        $counterOfTries = 0;

        do {
            $result = $client->CheckoutShoppingCart($request);

            $checkoutShoppingCartRequest = json_decode(json_encode($result), 1);

            //            dump($checkoutShoppingCartRequest);
            try {
                if (!array_key_exists('CheckoutShoppingCartResult', $checkoutShoppingCartRequest)) {
                    throw new \Exception('CheckoutShoppingCartResult not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('ShoppingCart', $checkoutShoppingCartRequest['CheckoutShoppingCartResult'])) {
                    throw new \Exception('ShoppingCart not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('GrandTotal', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart'])) {
                    throw new \Exception('GrandTotal not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('ID', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart'])) {
                    throw new \Exception('ID not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('CartItems', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart'])) {
                    throw new \Exception('CartItems not inside the result of the CheckoutShoppingCart error');
                }
                $loopAgain = false;
            } catch (\Exception $exception) {
                $loopAgain = true;
                $counterOfTries++;
            }
        } while ($loopAgain && $counterOfTries < $numberOfTries);

        if ($numberOfTries === $counterOfTries) {
            throw (new MindbodyServiceException('Number of tries of CheckoutChoppingCart has been reached'))
                ->setMerchantResponse(json_encode($checkoutShoppingCartRequest));
        } else {
            return $checkoutShoppingCartRequest;
        }
    }

    /**
     * Executes a Mindbody purchase. It only accepts one item
     *
     * @param int|string $clientId
     * @param int        $itemId
     * @param int|string $paymentMethodId - Mindbody's payment method id. If its value is "CashInfo" it will pay with Cash
     * @param int|string $grandTotal
     * @param bool       $test
     * @param null       $promotionCode
     * @param int        $discountAmount
     *
     * @return mixed
     * @throws MindbodyServiceException
     */
    public function purchaseShoppingCart(
        $clientId,
        $itemId,
        $paymentMethodId,
        $grandTotal,
        $test = true,
        $promotionCode = null,
        $discountAmount = 0
    ) {
        if (is_null($itemId)) {
            throw new \Exception('Item can not be null', 100);
        }

        $shoppingCartArray = [
            'ClientID'        => $clientId,
            'UserCredentials' => [
                'Username'   => $this->adminUser,
                'Password'   => $this->adminPassword,
                'SiteIDs'    => $this->siteIds,
                'LocationID' => 0,
            ],
            'CartItems'       => [
                'CartItem' => [
                    'Quantity'       => 1,
                    'Item'           => new \SoapVar(
                        [
                            'ID'                 => $itemId,
                            'DiscountPercentage' => 0,
                            'SellOnline'         => true,
                        ],
                        SOAP_ENC_ARRAY,
                        'Service',
                        'http://clients.mindbodyonline.com/api/0_5_1'
                    ),
                    'DiscountAmount' => $discountAmount,
                ],
            ],
            'Payments'        => [
                'PaymentInfo' => new \SoapVar(
                    [
                        'ID'     => $paymentMethodId,
                        'Amount' => $grandTotal,
                    ],
                    SOAP_ENC_ARRAY,
                    'CustomPaymentInfo',
                    'http://clients.mindbodyonline.com/api/0_5_1'
                ),
            ],
            'Test'            => $test,
            'SendEmail'       => !$test,
        ];

        //        die('<pre>'.print_r($shoppingCartArray, true));

        if (!is_null($promotionCode)) {
            $shoppingCartArray['PromotionCode'] = $promotionCode;
        }
        $numberOfTries  = 3;
        $counterOfTries = 0;

        /*
         * This api will be executed 3 times maximum if one of them fails.
         * If the first does not fail it will continue in its normal behaviour.
         */
        do {
            $checkoutShoppingCartRequest = $this->mb->CheckoutShoppingCart($shoppingCartArray);
            $counterOfTries++;

            try {
                if (!array_key_exists('CheckoutShoppingCartResult', $checkoutShoppingCartRequest)) {
                    throw new \Exception('CheckoutShoppingCartResult not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('ShoppingCart', $checkoutShoppingCartRequest['CheckoutShoppingCartResult'])) {
                    throw new \Exception('ShoppingCart not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('GrandTotal', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart'])) {
                    throw new \Exception('GrandTotal not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('ID', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart'])) {
                    throw new \Exception('ID not inside the result of the CheckoutShoppingCart error');
                } elseif (!array_key_exists('CartItems', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart'])) {
                    throw new \Exception('CartItems not inside the result of the CheckoutShoppingCart error');
                }
                $loopAgain = false;
            } catch (\Exception $exception) {
                $loopAgain = true;
            }
        } while ($loopAgain || $numberOfTries === $counterOfTries);

        if ($numberOfTries === $counterOfTries) {
            throw (new MindbodyServiceException('Number of tries of CheckoutChoppingCart has been reached'))
                ->setMerchantResponse(json_encode($checkoutShoppingCartRequest));
        } else {
            return $checkoutShoppingCartRequest;
        }
    }

    public function getMindBody()
    {
        return $this->mb;
    }

    /**
     * Returns the Mindbody client's services by a given $clientId
     *
     * @param int|string $clientId
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClientServices($clientId)
    {
        $programs = $this->getPrograms();

        $soapPrograms = '';

        foreach ($programs as $program) {
            $soapPrograms .= '<int>' . $program['id'] . '</int>';
        }

        $body       = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://clients.mindbodyonline.com/api/0_5_1">
                <soapenv:Header/>
                <soapenv:Body>
                   <GetClientServices>
                      <Request>
                         <SourceCredentials>
			               <SourceName>' . $this->sourceName . '</SourceName>
			               <Password>' . $this->sourcePassword . '</Password>
			               <SiteIDs>
			                  <int>' . $this->siteIds[0] . '</int>
			               </SiteIDs>
			            </SourceCredentials>
                         <ClientID>' . $clientId . '</ClientID>
                         <ProgramIDs>' . $soapPrograms . '</ProgramIDs>
                      </Request>
                   </GetClientServices>
                </soapenv:Body>
             </soapenv:Envelope>
';
        $httpClient = new Client();
        $result     = $httpClient->request(
            'POST',
            'https://api.mindbodyonline.com/0_5_1/ClientService.asmx',
            [
                'body'    => $body,
                'headers' => [
                    "Content-Type" => "text/xml; charset=utf-8",
                    'SOAPAction'   => 'http://clients.mindbodyonline.com/api/0_5_1/GetClientServices',
                    'Host'         => 'api.mindbodyonline.com',
                ],
            ]
        );

        $xml = new XmlParser($result->getBody()->getContents());
        foreach ($xml->data['soap:Envelope']['soap:Body']['GetClientServicesResponse']['GetClientServicesResult'] as $getClientServicesResult) {
            if (array_key_exists('ClientServices', $getClientServicesResult)) {
                if (is_null($getClientServicesResult['ClientServices'])) {
                    return [];
                } else {
                    if (array_key_exists('ClientService', $getClientServicesResult['ClientServices'])) {
                        return $this->convertXmlResultIntoAssocArray($getClientServicesResult['ClientServices']['ClientService']);
                    } else {
                        //                    return end($getClientServicesResult['ClientServices']);
                        return $this->convertXmlResultIntoAssocArray(end($getClientServicesResult['ClientServices'])['ClientService']);
                    }
                }
            }
        }
        throw new \Exception('It has been an error while trying to parse the XML document');
    }

    /**
     * Verifies if a client has an active membership now
     *
     * @param int|string $clientId
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function clientHasAnActiveService($clientId)
    {
        $mindbodyClientServices = $this->getClientServices($clientId);

        if (!empty($mindbodyClientServices)) {
            $expirationDate = new \DateTime($mindbodyClientServices['ExpirationDate']);
            $now            = new \DateTime();
            if ($expirationDate > $now) {
                return true;
            }
        }

        return false;
    }

    /**
     * Executes the calls to make the actual purchase in mindbody using the native Soap client.
     * It will call CheckoutShoppingCart, ClientLastPurchases and ClientServices
     * And finally it will populate the passed TransactionRecord $transactionRecord instance provided
     *
     * @param TransactionRecord $transactionRecord
     * @param string|int        $mindbodyClientId
     * @param array             $shoppingCartItems
     * @param string|int        $mindbodyPaymentMethodId
     * @param string|int        $mindbodyGrandTotal
     * @param string|int        $mindbodyDiscountCode
     * @param string|int        $discountAmount
     *
     * @throws \Exception
     * @return TransactionRecord
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makePurchaseWithSoap(
        $transactionRecord,
        $mindbodyClientId,
        $shoppingCartItems,
        $mindbodyPaymentMethodId,
        $mindbodyGrandTotal,
        $mindbodyDiscountCode,
        $discountAmount = 0
    ) {
        $transactionRecord
            ->setMindbodyCheckoutFail(false);

        //Purchasing service in Mindbody
        $checkoutShoppingCartRequest = [];
        try {
            $checkoutShoppingCartRequest = $this->purchaseShoppingCartWithSoap(
                $mindbodyClientId,
                $shoppingCartItems,
                $mindbodyPaymentMethodId,
                $mindbodyGrandTotal,
                false,
                $mindbodyDiscountCode,
                $discountAmount
            );
            //            dump($checkoutShoppingCartRequest);
            $transactionRecord
                ->setDiscountCode($mindbodyDiscountCode)
                ->setDiscountAmount($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['DiscountTotal'])
                ->setSubTotal($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['SubTotal'])
                ->setAmount($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal'])
                ->setMerchantId($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['ID'])
                ->setMerchantResponse(json_encode($checkoutShoppingCartRequest))
                ->setTaxAmount($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['TaxTotal']);

            $transactionRecord->wipeTransactionItems();

            if (array_key_exists('Item', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['CartItems']['CartItem'])) {
                //                dump('Item condition');
                $cartItems = [$checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['CartItems']['CartItem']];
            } else {
                //                dump('ELSE Item condition');
                $cartItems = $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['CartItems']['CartItem'];
            }

            foreach ($cartItems as $cartItem) {
                //                dump($cartItem);
                $transactionItem = (new TransactionItem())
                    ->setPrice($cartItem['Item']['Price'])
                    ->setMerchantId($cartItem['Item']['ID'])
                    ->setName($cartItem['Item']['Name'])
                    ->setQuantity($cartItem['Quantity'])
                    ->setCreated();
                if (array_key_exists('ProgramID', $cartItem['Item'])) {
                    $transactionItem
                        ->setType('Service')
                        ->setProgramId($cartItem['Item']['ProgramID']);
                } else {
                    $transactionItem->setType('Product');
                }
                $transactionRecord->addTransactionItem($transactionItem);
            }
        } catch (MindbodyServiceException $exception) {
            $this->logger->error('MindbodyServiceException Error in Mindbody service makePurchase', $checkoutShoppingCartRequest);
            $transactionRecord
                ->setMindbodyCheckoutFail(true)
                ->setMerchantResponse($exception->getMerchantResponse());
        } catch (\Exception $exception) {
            if ($exception->getCode() === 100) {
                throw new \Exception('The process can not be completed because of there is no item id');
            } else {
                $this->logger->error('Exception Error in Mindbody service makePurchase', $checkoutShoppingCartRequest);
                $this->logger->error($exception->getMessage());
                $transactionRecord->setMindbodyCheckoutFail(true);
            }
        }

        if (!$transactionRecord->getMindbodyCheckoutFail()) {
            //Getting Mindbody client's last purchase (this one) to have the purchase Id
            try {
                $transactionRecord->setMindbodyLastPurchaseFail(false);
                $clientPurchases = $this->getClientPurchases($mindbodyClientId);
                //                dump('clientPurchases');
                //                dump($clientPurchases);
                $purchase = $this->getClientLastPurchase($mindbodyClientId);
                //                dump('purchase');
                //                dump($purchase);
                $transactionRecord->setMerchantPurchaseId($purchase['Sale']['ID']);
                foreach ($transactionRecord->getTransactionItems() as $transactionItem) {
                    $transactionItem->setSaleDatetime(new \DateTime($purchase['Sale']['SaleDateTime']));
                }
            } catch (\Exception $exception) {
                $transactionRecord->setMindbodyLastPurchaseFail(true);
            }

            //Getting Mindbody client's services to show the expiration date of the purchased service
            try {
                $transactionRecord->setMindbodyExpirationDateFail(false);
                $clientServices    = $this->getClientServicesFormatted($mindbodyClientId, [23]);
                $transactionRecord = $this->matchClientServicesWithTransactionItems($transactionRecord, $clientServices);
                //                $lastClientService = $this->getClientServicesFormatted($mindbodyClientId);
                //
                //                if (array_key_exists('ExpirationDate', $lastClientService) && array_key_exists('ActiveDate', $lastClientService)) {
                //                    $transactionRecord->setServiceExpirationDate(new \DateTime($lastClientService['ExpirationDate']));
                //                    $transactionRecord->setServiceActivationDate(new \DateTime($lastClientService['ActiveDate']));
                //                } else {
                //                    throw new \Exception('Expiration date or Active date are not coming inside the response of the API endpoint');
                //                }

            } catch (\Exception $exception) {
                $this->logger->error(
                    'Exception Error in Mindbody service makePurchase when trying to get clients last purchase',
                    $checkoutShoppingCartRequest
                );
                $transactionRecord->setMindbodyExpirationDateFail(true);
            }
        }

        return $transactionRecord;
    }

    /**
     * Executes the calls to make the actual purchase in mindbody.
     * It will call CheckoutShoppingCart, ClientLastPurchases and ClientServices
     * And finally it will populate the passed TransactionRecord $transactionRecord instance provided
     *
     * @param TransactionRecord $transactionRecord
     * @param string|int        $mindbodyClientId
     * @param string            $mindbodyServiceId
     * @param string|int        $mindbodyPaymentMethodId
     * @param string|int        $mindbodyGrandTotal
     * @param string|int        $mindbodyDiscountCode
     * @param string|int        $discountAmount
     *
     * @throws \Exception
     * @return TransactionRecord
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makePurchase(
        $transactionRecord,
        $mindbodyClientId,
        $mindbodyServiceId,
        $mindbodyPaymentMethodId,
        $mindbodyGrandTotal,
        $mindbodyDiscountCode,
        $discountAmount = 0
    ) {
        $transactionRecord
            ->setMindbodyCheckoutFail(false);

        //Purchasing service in Mindbody
        $checkoutShoppingCartRequest = [];
        try {
            $checkoutShoppingCartRequest = $this->purchaseShoppingCart(
                $mindbodyClientId,
                $mindbodyServiceId,
                $mindbodyPaymentMethodId,
                $mindbodyGrandTotal,
                false,
                $mindbodyDiscountCode,
                $discountAmount
            );

            $transactionRecord
                ->setDiscountCode($mindbodyDiscountCode)
                ->setDiscountAmount($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['DiscountTotal'])
                ->setSubTotal($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['SubTotal'])
                ->setAmount($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal'])
                ->setMerchantId($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['ID'])
                ->setMerchantResponse(json_encode($checkoutShoppingCartRequest))
                ->setTaxAmount($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['TaxTotal']);

            $transactionRecord->wipeTransactionItems();

            foreach ($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['CartItems'] as $cartItem) {
                $transactionItem = (new TransactionItem())
                    ->setPrice($cartItem['Item']['Price'])
                    ->setMerchantId($cartItem['Item']['ID'])
                    ->setName($cartItem['Item']['Name'])
                    ->setQuantity($cartItem['Quantity'])
                    ->setSaleDatetime(new \DateTime());
                $transactionRecord->addTransactionItem($transactionItem);
            }
        } catch (MindbodyServiceException $exception) {
            $this->logger->error('MindbodyServiceException Error in Mindbody service makePurchase', $checkoutShoppingCartRequest);
            $transactionRecord
                ->setMindbodyCheckoutFail(true)
                ->setMerchantResponse($exception->getMerchantResponse());
        } catch (\Exception $exception) {
            if ($exception->getCode() === 100) {
                throw new \Exception('The process can not be completed because of there is no item id');
            } else {
                $this->logger->error('Exception Error in Mindbody service makePurchase', $checkoutShoppingCartRequest);
                $transactionRecord->setMindbodyCheckoutFail(true);
            }
        }

        if (!$transactionRecord->getMindbodyCheckoutFail()) {
            //Getting Mindbody client's last purchase (this one) to have the purchase Id
            try {
                $transactionRecord->setMindbodyLastPurchaseFail(false);
                $purchase = $this->getClientLastPurchase($mindbodyClientId);

                $transactionRecord->setMerchantPurchaseId($purchase['Sale']['ID']);
            } catch (\Exception $exception) {
                $transactionRecord->setMindbodyLastPurchaseFail(true);
            }

            //Getting Mindbody client's services to show the expiration date of the purchased service
            try {
                $transactionRecord->setMindbodyExpirationDateFail(false);
                $lastClientService = $this->getClientServices($mindbodyClientId);

                if (array_key_exists('ExpirationDate', $lastClientService) && array_key_exists('ActiveDate', $lastClientService)) {
                    $transactionRecord->setServiceExpirationDate(new \DateTime($lastClientService['ExpirationDate']));
                    $transactionRecord->setServiceActivationDate(new \DateTime($lastClientService['ActiveDate']));
                } else {
                    throw new \Exception('Expiration date or Active date are not coming inside the response of the API endpoint');
                }
            } catch (\Exception $exception) {
                $this->logger->error(
                    'Exception Error in Mindbody service makePurchase when trying to get clients last purchase',
                    $checkoutShoppingCartRequest
                );
                $transactionRecord->setMindbodyExpirationDateFail(true);
            }
        }

        return $transactionRecord;
    }

    /**
     * Returns an array of formatted custom payment methods
     *
     * @return array
     */
    public function getFormattedCustomPaymentMethods()
    {
        $customPaymentMethods = $this->mb->GetCustomPaymentMethods();

        $formattedCustomPaymentMethods = [];
        //            throw new \Exception(print_r($customPaymentMethods,true));
        if ($customPaymentMethods['GetCustomPaymentMethodsResult']['ResultCount'] === 1) {
            $customPaymentMethod                                         = $customPaymentMethods['GetCustomPaymentMethodsResult']['PaymentMethods']['CustomPaymentInfo'];
            $formattedCustomPaymentMethods[$customPaymentMethod['Name']] = $customPaymentMethod['ID'];
        } else {
            foreach ($customPaymentMethods['GetCustomPaymentMethodsResult']['PaymentMethods']['CustomPaymentInfo'] as $customPaymentMethod) {
                $formattedCustomPaymentMethods[$customPaymentMethod['Name']] = $customPaymentMethod['ID'];
            }
        }

        return $formattedCustomPaymentMethods;
    }

    /**
     * Returns an array of valid custom payment methods
     *
     * @return array
     */
    public function getValidFormatterCustomPaymentMethods()
    {
        $paymentMethods      = $this->getFormattedCustomPaymentMethods();
        $validPaymentMethods = [];

        foreach ($paymentMethods as $key => $paymentMethod) {
            if (in_array($key, $this->enabledPaymentNames)) {
                $validPaymentMethods[] = [
                    'name' => $key,
                    'id'   => $paymentMethod,
                ];
            }
        }

        return $validPaymentMethods;
    }

    /**
     * Returns the Mindbody response of ValidateLogin if the credentials provided are correct, otherwise it will
     * throw an Exception
     *
     * @param string $email
     * @param string $password
     *
     * @return mixed
     * @throws NotValidLoginException
     */
    public function validateLogin(string $email, string $password)
    {
        $validateLogin = $this->mb->ValidateLogin(
            [
                'Username' => $email,
                'Password' => $password,
            ]
        );

        if (!empty($validateLogin['ValidateLoginResult']['GUID'])) {
            return $validateLogin;
        } else {
            throw new NotValidLoginException();
        }
    }

    /**
     * Returns a formatted array of Online's Mindbody Programs
     *
     * @param bool $useCache
     *
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getPrograms($useCache = true)
    {
        if ($this->cache->has('mindbody.site.programs') && $useCache) {
            $formattedPrograms = $this->cache->get('mindbody.site.programs');
        } else {
            $programs = $this->mb->GetPrograms(
                [
                    'OnlineOnly'   => true,
                    'ScheduleType' => 'All',
                ]
            );

            $formattedPrograms = [];

            if (array_key_exists('ID', $programs['GetProgramsResult']['Programs']['Program'])) {
                $formattedPrograms = [
                    [
                        'id'           => $programs['GetProgramsResult']['Programs']['Program']['ID'],
                        'name'         => $programs['GetProgramsResult']['Programs']['Program']['Name'],
                        'scheduleType' => $programs['GetProgramsResult']['Programs']['Program']['ScheduleType'],
                        'cancelOffset' => $programs['GetProgramsResult']['Programs']['Program']['CancelOffset'],
                    ],
                ];
            } else {
                foreach ($programs['GetProgramsResult']['Programs']['Program'] as $program) {
                    $formattedPrograms[] = [
                        'id'           => $program['ID'],
                        'name'         => $program['Name'],
                        'scheduleType' => $program['ScheduleType'],
                        'cancelOffset' => $program['CancelOffset'],
                    ];
                }
            }
            $this->cache->set('mindbody.site.programs', $formattedPrograms, 604800);
        }

        return $formattedPrograms;
    }

    /**
     * Returns an array of Mindbody's required client fields in the registration form
     *
     * @param bool $useCache
     *
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getRequiredClientFields($useCache = true)
    {
        if ($this->cache->has('mindbody.client.requiredFields') && $useCache) {
            $formattedFields = $this->cache->get('mindbody.client.requiredFields');
        } else {
            $requiredClientFields = $this->mb->GetRequiredClientFields();
            $formattedFields      = [];

            foreach ($requiredClientFields['GetRequiredClientFieldsResult']['RequiredClientFields']['string'] as $field) {
                $formattedFields[] = $field;
            }
            $this->cache->set('mindbody.client.requiredFields', $formattedFields, 604800);
        }

        return $formattedFields;
    }

    /**
     * It adds or update a Client given an array of $userData
     *
     * @param array $userData
     *
     * @return mixed
     */
    public function addOrUpdateClients(array $userData)
    {
        $newClientResult = $this->mb->AddOrUpdateClients(
            [
                'UserCredentials' => [
                    'Username'   => $this->adminUser,
                    'Password'   => $this->adminPassword,
                    'SiteIDs'    => $this->siteIds,
                    'LocationID' => 0,
                ],
                'Clients'         => [
                    'Client' => $userData,
                ],
            ]
        );

        return $newClientResult;
    }

    /**
     *
     * @param bool $useCache
     *
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getClientReferralTypes($useCache = true)
    {
        if ($this->cache->has('mindbody.client.referralTypes') && $useCache) {
            $formattedReferralTypes = $this->cache->get('mindbody.client.referralTypes');
        } else {
            $requiredClientFields   = $this->mb->GetClientReferralTypes();
            $formattedReferralTypes = [];
            foreach ($requiredClientFields['GetClientReferralTypesResult']['ReferralTypes']['string'] as $field) {
                $formattedReferralTypes[] = $field;
            }
            $this->cache->set('mindbody.client.referralTypes', $formattedReferralTypes, 604800);
        }

        return $formattedReferralTypes;
    }

    /**
     * Searchs a Mindbody client by a given email address
     *
     * @param string $email
     *
     * @return mixed
     * @throws \Exception
     */
    public function searchClientByEmail(string $email)
    {
        //TODO: Add email validation
        $result = $this->mb->GetClients(
            [
                'UserCredentials' => [
                    'Username'   => $this->adminUser,
                    'Password'   => $this->adminPassword,
                    'SiteIDs'    => $this->siteIds,
                    'LocationID' => 0,
                ],
                'SearchText'      => $email,
            ]
        );

        if (count($result['GetClientsResult']['Clients']) > 0) {
            throw new \Exception('Email already exist');
        } else {
            return $result;
        }
    }

    /**
     * Reads a user coming from a mindbody response and creates a new user in the system
     *
     * @param $Client
     */
    public function createCustomerFromMindbodyClientResponse($Client)
    {
    }

    /**
     * Returns an array of Mindbody products
     *
     * @return array
     */
    public function getProducts()
    {
        $result = $this->mb->GetProducts(
            [
                'SellOnline' => true,
            ]
        );

        return $result;
    }

    /**
     * Returns a formatted array of Mindbody products
     *
     * @return array
     */
    public function getFormattedProducts()
    {
        $mindbodyProducts = $this->getProducts();
        $products         = [];
        foreach ($mindbodyProducts['GetProductsResult']['Products']['Product'] as $product) {
            $products[] = [
                'name'             => $product['Name'],
                'price'            => $product['Price'],
                'id'               => $product['ID'],
                'shortDescription' => array_key_exists('ShortDesc', $product) ? $product['ShortDesc'] : null,
                'longDescription'  => array_key_exists('LongDesc', $product) ? $product['LongDesc'] : null,
                'onlinePrice'      => $product['OnlinePrice'],
                'groupId'          => $product['GroupID'],
            ];
        }

        return $products;
    }

    /**
     * It will return an array with the programs' ids of this transaction
     *
     * @param TransactionRecord $transactionRecord
     *
     * @return array
     * @throws NoProgramsInTransactionRecordException
     */
    public function getProgramIdsFromTransactionRecord(TransactionRecord $transactionRecord)
    {
        $programIds = [];
        foreach ($transactionRecord->getTransactionItems() as $transactionItem) {
            if ($transactionItem->getProgramId() !== null) {
                $programIds[] = $transactionItem->getProgramId();
            }
        }

        if (count($programIds) === 0) {
            throw new NoProgramsInTransactionRecordException();
        }

        return $programIds;
    }

    /**
     * Matches  TransactionItem's name with Mindbody client's services. This method is used to get he id of the
     * Mindbody's client service
     *
     * @param TransactionRecord $transactionRecord
     * @param array             $clientServices
     *
     * @return TransactionRecord
     */
    public function matchClientServicesWithTransactionItems(TransactionRecord $transactionRecord, $clientServices)
    {
        //        dump($clientServices);
        foreach ($transactionRecord->getTransactionItems() as $transactionItem) {
            if ($transactionItem->getType() === 'Service') {
                foreach ($clientServices as $clientService) {
                    if (
                        $clientService['name'] === $transactionItem->getName()
                        && (new \DateTime($clientService['paymentDate']))->format('Y-m-d') === $transactionItem->getSaleDatetime()->format('Y-m-d')
                    ) {
                        $transactionItem
                            ->setMindbodyServiceId($clientService['id'])
                            ->setExpirationDate(new \DateTime($clientService['expirationDate']))
                            ->setActiveDate(new \DateTime($clientService['activeDate']));
                    }
                }
            }
        }

        return $transactionRecord;
    }

    /**
     * Converts arrays with one item inside into a key of the parent one
     *
     * @param $xmlResultArray
     *
     * @return array
     */
    private function convertXmlResultIntoAssocArray($xmlResultArray)
    {
        $assocArray = [];
        foreach ($xmlResultArray as $masterKey => $item) {
            if (is_array($item)) {
                foreach ($item as $key => $subItem) {
                    if (is_array($subItem)) {
                        $assocArray[$key] = $this->convertXmlResultIntoAssocArray($subItem);
                    } else {
                        $assocArray[$key] = $subItem;
                    }
                }
            } else {
                $assocArray[$masterKey] = $item;
            }
        }

        return $assocArray;
    }
}