<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 07/12/17
 * Time: 17:04
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use DevinCrossman\Mindbody\MB_API as ParentMBApi;

class MB_API extends ParentMBApi
{
    const API_VERSION = "0_5_1";
    public $soapOptions = ['soap_version' => SOAP_1_1, 'trace' => true];
    /*
    ** Uncomment if you need user credentials
    protected $userCredentials = array(
        "Username"=>'REPLACE_WITH_YOUR_USERNAME',
        "Password"=>'REPLACE_WITH_YOUR_PASSWORD',
        "SiteIDs"=>array('REPLACE_WITH_YOUR_SITE_ID')
    );
    */
    public    $debugSoapErrors        = true;
    protected $client;
    protected $sourceCredentials      = [
        "SourceName" => 'REPLACE_WITH_YOUR_SOURCENAME',
        "Password"   => 'REPLACE_WITH_YOUR_PASSWORD',
        "SiteIDs"    => ['REPLACE_WITH_YOUR_SITE_ID'],
    ];
    protected $appointmentServiceWSDL = "https://api.mindbodyonline.com/0_5/AppointmentService.asmx?WSDL";
    protected $classServiceWSDL       = "https://api.mindbodyonline.com/0_5/ClassService.asmx?WSDL";
    protected $clientServiceWSDL      = "https://api.mindbodyonline.com/0_5/ClientService.asmx?WSDL";
    protected $dataServiceWSDL        = "https://api.mindbodyonline.com/0_5/DataService.asmx?WSDL";
    protected $saleServiceWSDL        = "https://api.mindbodyonline.com/0_5/SaleService.asmx?WSDL";
    protected $siteServiceWSDL        = "https://api.mindbodyonline.com/0_5/SiteService.asmx?WSDL";
    protected $staffServiceWSDL       = "https://api.mindbodyonline.com/0_5/StaffService.asmx?WSDL";
    protected $apiMethods             = [];
    protected $apiServices            = [];

    /*
    ** initializes the apiServices and apiMethods arrays
    */

    public function __construct($sourceCredentials = [])
    {
        // set apiServices array with Mindbody WSDL locations
        $this->apiServices = [
            'AppointmentService' => $this->appointmentServiceWSDL,
            'ClassService'       => $this->classServiceWSDL,
            'ClientService'      => $this->clientServiceWSDL,
            'DataService'        => $this->dataServiceWSDL,
            'SaleService'        => $this->saleServiceWSDL,
            'SiteService'        => $this->siteServiceWSDL,
            'StaffService'       => $this->staffServiceWSDL,
        ];
        // set apiMethods array with available methods from Mindbody services
        foreach ($this->apiServices as $serviceName => $serviceWSDL) {
            $this->client     = new \SoapClient($serviceWSDL, $this->soapOptions);
            $this->apiMethods = array_merge(
                $this->apiMethods,
                [
                    $serviceName => array_map(
                        function ($n) {
                            $start  = 1 + strpos($n, ' ');
                            $end    = strpos($n, '(');
                            $length = $end - $start;

                            return substr($n, $start, $length);
                        },
                        $this->client->__getFunctions()
                    ),
                ]
            );
        }
        // set sourceCredentials
        if (!empty($sourceCredentials)) {
            if (!empty($sourceCredentials['SourceName'])) {
                $this->sourceCredentials['SourceName'] = $sourceCredentials['SourceName'];
            }
            if (!empty($sourceCredentials['Password'])) {
                $this->sourceCredentials['Password'] = $sourceCredentials['Password'];
            }
            if (!empty($sourceCredentials['SiteIDs'])) {
                if (is_array($sourceCredentials['SiteIDs'])) {
                    $this->sourceCredentials['SiteIDs'] = $sourceCredentials['SiteIDs'];
                } elseif (is_numeric($sourceCredentials['SiteIDs'])) {
                    $this->sourceCredentials['SiteIDs'] = [$sourceCredentials['SiteIDs']];
                }
            }
        }
    }

    public function FunctionDataXml()
    {
        $passed       = func_get_args();
        $request      = empty($passed[0]) ? null : $passed[0];
        $returnObject = empty($passed[1]) ? null : $passed[1];
        $debugErrors  = empty($passed[2]) ? null : $passed[2];
        $data         = $this->callMindbodyService('DataService', 'FunctionDataXml', $request);
        $xmlString    = $this->getXMLResponse();
        $sxe          = new \SimpleXMLElement($xmlString);
        $sxe->registerXPathNamespace("mindbody", "http://clients.mindbodyonline.com/api/0_5_1");
        $res = $sxe->xpath("//mindbody:FunctionDataXmlResponse");
        if ($returnObject) {
            return $res[0];
        } else {
            $arr = $this->replace_empty_arrays_with_nulls(json_decode(json_encode($res[0]), 1));
            if (is_array($arr['FunctionDataXmlResult']['Results']['Row'])) {
                $arr['FunctionDataXmlResult']['Results']['Row'] = $this->makeNumericArray($arr['FunctionDataXmlResult']['Results']['Row']);
            }

            return $arr;
        }
    }

}
