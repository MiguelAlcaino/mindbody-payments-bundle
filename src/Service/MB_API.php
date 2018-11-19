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
    public $soapOptions = array('soap_version' => SOAP_1_1, 'trace' => true);
    /*
    ** Uncomment if you need user credentials
    protected $userCredentials = array(
        "Username"=>'REPLACE_WITH_YOUR_USERNAME',
        "Password"=>'REPLACE_WITH_YOUR_PASSWORD',
        "SiteIDs"=>array('REPLACE_WITH_YOUR_SITE_ID')
    );
    */
    public $debugSoapErrors = true;
    protected $client;
    protected $sourceCredentials = array(
        "SourceName" => 'REPLACE_WITH_YOUR_SOURCENAME',
        "Password" => 'REPLACE_WITH_YOUR_PASSWORD',
        "SiteIDs" => array('REPLACE_WITH_YOUR_SITE_ID')
    );
    protected $appointmentServiceWSDL = "https://api.mindbodyonline.com/0_5/AppointmentService.asmx?WSDL";
    protected $classServiceWSDL = "https://api.mindbodyonline.com/0_5/ClassService.asmx?WSDL";
    protected $clientServiceWSDL = "https://api.mindbodyonline.com/0_5/ClientService.asmx?WSDL";
    protected $dataServiceWSDL = "https://api.mindbodyonline.com/0_5/DataService.asmx?WSDL";
    protected $finderServiceWSDL = "https://api.mindbodyonline.com/0_5/FinderService.asmx?WSDL";
    protected $saleServiceWSDL = "https://api.mindbodyonline.com/0_5/SaleService.asmx?WSDL";
    protected $siteServiceWSDL = "https://api.mindbodyonline.com/0_5/SiteService.asmx?WSDL";
    protected $staffServiceWSDL = "https://api.mindbodyonline.com/0_5/StaffService.asmx?WSDL";
    protected $apiMethods = array();
    protected $apiServices = array();

    /*
    ** initializes the apiServices and apiMethods arrays
    */

    public function __construct(string $sourceName, string $password, array $siteIds)
    {
        $this->appointmentServiceWSDL = "https://api.mindbodyonline.com/" . self::API_VERSION . "/AppointmentService.asmx?WSDL";
        $this->classServiceWSDL = "https://api.mindbodyonline.com/" . self::API_VERSION . "/ClassService.asmx?WSDL";
        $this->clientServiceWSDL = "https://api.mindbodyonline.com/" . self::API_VERSION . "/ClientService.asmx?WSDL";
        $this->dataServiceWSDL = "https://api.mindbodyonline.com/0_5/DataService.asmx?WSDL";
        $this->finderServiceWSDL = "https://api.mindbodyonline.com/0_5/FinderService.asmx?WSDL";
        $this->saleServiceWSDL = "https://api.mindbodyonline.com/" . self::API_VERSION . "/SaleService.asmx?WSDL";
        $this->siteServiceWSDL = "https://api.mindbodyonline.com/" . self::API_VERSION . "/SiteService.asmx?WSDL";
        $this->staffServiceWSDL = "https://api.mindbodyonline.com/" . self::API_VERSION . "/StaffService.asmx?WSDL";

        parent::__construct([
            'SourceName' => $sourceName,
            'Password' => $password,
            'SiteIDs' => $siteIds
        ]);
    }

    public function FunctionDataXml()
    {
        $passed = func_get_args();
        $request = empty($passed[0]) ? null : $passed[0];
        $returnObject = empty($passed[1]) ? null : $passed[1];
        $debugErrors = empty($passed[2]) ? null : $passed[2];
        $data = $this->callMindbodyService('DataService', 'FunctionDataXml', $request);
        $xmlString = $this->getXMLResponse();
        $sxe = new \SimpleXMLElement($xmlString);
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