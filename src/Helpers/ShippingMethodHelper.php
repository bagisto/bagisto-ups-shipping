<?php

namespace Webkul\UpsShipping\Helpers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartAddressRepository as CartAddress;
use Webkul\UpsShipping\Repositories\UpsRepository as UpsRepository;
use Webkul\Core\Repositories\ChannelRepository as Channel;


class ShippingMethodHelper
{
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * RateServiceWsdl
     *
     * @var string
     */
    protected $rateServiceWsdl;

    /**
     * ShipServiceWsdl
     *
     * @var string
     */
    protected $shipServiceWsdl;

    /**
     * Create a new controller instance.
     *
     * @param \Webkul\Checkout\Repositories\CartAddressRepository  $cartAddress;
     * @param \Webkul\Core\Repositories\ChannelRepository $channel
     * @param \Webkul\Shipping\Repositories\UpsRepository $upsRepository;
     */
    public function __construct(
       protected CartAddress $cartAddress,
       protected UpsRepository $upsRepository,
       protected Channel $channel
    )
    {
        $this->_config = request('_config');

        $this->cartAddress = $cartAddress;

        $this->upsRepository = $upsRepository;

        $this->channel = $channel;
    }

    /**
     * display methods
     *
     * @return array
    */
    public function getAllCartProducts($address)
    {
        $data = $this->_createSoapClient($address);

        return $data;
    }

    /**
     * Soap client for wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @return \SoapClient
     */
    protected function _createSoapClient($address)
    {
        $cart = Cart::getCart();

        $defaultChannel = core()->getCurrentChannelCode();

        $adminData = $this->channel->findByField('code', $defaultChannel)->first();

        $adminName = $adminData->name;

        $adminCompany = $adminData->hostname;

        if (! core()->getConfigData('sales.carriers.ups.ups_active'))
        {

            return false;
        } else {
            $status = true;
        }

        if ($status) 
        {
            $sellerAdminData = $this->upsRepository->getSellerAdminData($cart->items()->get(), 'ups_postcode');
        }

        $sellerAdminServices = $allServices = [];
        foreach ($sellerAdminData as $cartProduct) 
        {
            $sellerAdminServices[0] = explode(",", core()->getConfigData('sales.carriers.ups.services'));
        }

        foreach ($sellerAdminData as $cartProduct) 
        {
            $weight = $this->getWeight($cartProduct->weight);

            $countryId = core()->getConfigData('sales.shipping.origin.country');

            if (! isset($cartProduct->marketplace_seller_id)) 
            {
                //if admin's product
                $zoneInformation = core()->getConfigData('sales.shipping.origin.state');
                $address1 = core()->getConfigData('sales.shipping.origin.address1');
                $city = core()->getConfigData('sales.shipping.origin.city');
            }

            // Get The User Credentials
            $accessKey = core()->getConfigData('sales.carriers.ups.access_license_key');
            $userId = core()->getConfigData('sales.carriers.ups.user_id');
            $password = core()->getConfigData('sales.carriers.ups.password');
            $url = core()->getConfigData('sales.carriers.ups.gateway_url');
            $shipperNumber = core()->getConfigData('sales.carriers.ups.shipper_number');

            $shipperNumber = $shipperNumber ? $shipperNumber: '';

            // create a simple xml object for AccessRequest & RateRequest
            $accessRequesttXML = new \SimpleXMLElement ( "<AccessRequest></AccessRequest>" );
            $rateRequestXML = new \SimpleXMLElement ( "<RatingServiceSelectionRequest></RatingServiceSelectionRequest>" );

            // create AccessRequest XML
            $accessRequesttXML->addChild ( "AccessLicenseNumber", $accessKey );
            $accessRequesttXML->addChild ( "UserId", $userId );
            $accessRequesttXML->addChild ( "Password", $password );

            // create RateRequest XML
            $request = $rateRequestXML->addChild ( 'Request' );
            $request->addChild ( "RequestAction", "Rate" );
            $request->addChild ( "RequestOption", "Shop" );

            $shipment = $rateRequestXML->addChild ( 'Shipment' );
            $shipper = $shipment->addChild ( 'Shipper' );

            if (! isset($cartProduct->marketplace_seller_id)) 
            {

                $shipper->addChild ( "Name", $adminName );
                $shipper->addChild ( "ShipperNumber", $shipperNumber );

                $shipperddress = $shipper->addChild ( 'Address' );
                $shipperddress->addChild ( "AddressLine1", $address1 ? $address1 : '' );
                $shipperddress->addChild ( "City", $city ? $city : '' );
                $shipperddress->addChild ( "PostalCode", core()->getConfigData('sales.shipping.origin.zipcode') );
                $shipperddress->addChild ( "CountryCode", $countryId );

                $shipFrom = $shipment->addChild ( 'ShipFrom' );
                $shipFrom->addChild ( "CompanyName", $adminCompany );
                $shipFromAddress = $shipFrom->addChild ( 'Address' );
                $shipFromAddress->addChild ( "AddressLine1", $address1 ? $address1 : '' );
                $shipFromAddress->addChild ( "City", $city ? $city : '' );
                $shipFromAddress->addChild ( "StateProvinceCode", $zoneInformation );
                $shipFromAddress->addChild ( "PostalCode", core()->getConfigData('sales.shipping.origin.zipcode') );
                $shipFromAddress->addChild ( "CountryCode", $countryId );
            }

            $shipTo = $shipment->addChild ( 'ShipTo' );
            $shipTo->addChild ( "CompanyName", $address->first_name . ' ' . $address->last_name );
            $shipToAddress = $shipTo->addChild ( 'Address' );
            $shipToAddress->addChild ( "AddressLine1", $address->address1 );
            $shipToAddress->addChild ( "City", $address->city );
            if ($address->country == 'PR') 
            {
                $shipToAddress->addChild ( "PostalCode", '00'. $address->postcode );
            } else {
                $shipToAddress->addChild ( "PostalCode", $address->postcode );
            }
            $shipToAddress->addChild ( "CountryCode", $address->country );
            $package = $shipment->addChild ( 'Package' );
            $packageType = $package->addChild ( 'PackagingType' );
            $packageType->addChild ( "Code", core()->getConfigData('sales.carriers.ups.container') );

            $packageWeight = $package->addChild ( 'PackageWeight' );
            $unitOfMeasurement = $packageWeight->addChild ( 'UnitOfMeasurement' );
            $unitOfMeasurement->addChild ( "Code", "LBS" );
            $packageWeight->addChild ( "Weight", $weight );

            $requestXML =  $accessRequesttXML->asXML () . $rateRequestXML->asXML ();
            $endpointurl = 'https://onlinetools.ups.com/ups.app/xml/Rate';

            try {
                $url = 'https://onlinetools.ups.com/ups.app/xml/Rate';

                $ch = curl_init();
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-type: text/xml",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                ));
                curl_setopt($ch, CURLOPT_POSTFIELDS,$requestXML);

                $response = curl_exec($ch);

                curl_close($ch);

                $upsServiceArray = simplexml_load_string($response);

                $upsServices = json_decode(json_encode($upsServiceArray));

                if ( isset($cartProduct->marketplace_seller_id)) 
                {
                    $sellerId = $cartProduct->marketplace_seller_id;
                } else {
                    $sellerId = 0;
                }

                if ($response) 
                {
                    if ( isset($upsServices->Response->ResponseStatusDescription)
                        && $upsServices->Response->ResponseStatusDescription == 'Success') 
                    {
                        if ( isset($upsServices->RatedShipment)) 
                        {
                            foreach ($upsServices->RatedShipment as $services) 
                            {

                                $serviceCode = $services->Service->Code;

                                $matchResult = $this->upsRepository->validateAllowedMethods($serviceCode, $sellerAdminServices);

                                $serviceName = $this->getServiceName($serviceCode);

                                if ($matchResult) 
                                {
                                    $cartProductServices[$serviceName] = [
                                        'classId' => $serviceCode,
                                        'rate' => $services->RatedPackage->TotalCharges->MonetaryValue,
                                        'currency' => $services->RatedPackage->TotalCharges->CurrencyCode,
                                        'weight' => $services->BillingWeight->Weight,
                                        'weightUnit' => $services->BillingWeight->UnitOfMeasurement->Code,
                                        'marketplace_seller_id' => $sellerId,
                                        'itemQuantity' => $cartProduct->quantity
                                    ];
                                }

                            }

                            if ( !empty($cartProductServices)) 
                            {
                                $allServices[] = $cartProductServices;
                            }
                        }

                    } else {
                        $errorLog = $this->getErrorLog($upsServices);
                        
                        return false;
                        //to check the error generated in the method;
                    }
                }
            } catch (\Exception $e) {

                return false;
            }
        }

        if ( !empty($allServices) ) 
        {
            return $this->upsRepository->getCommonMethods($allServices);
        } else {
            return false;
        }
    }


    /**
     * Map service code
     *
     * @param $serviceCode
     */
    protected function getServiceName($serviceCode)
    {
        $mapServices = [
            '01'    => 'Next Day Air',
            '02'    => '2nd Day Air',
            '03'    => 'Ups Ground',
            '07'    => 'Ups Worldwide Express',
            '08'    => 'Ups Worldwide Expedited',
            '11'    => 'Standard',
            '12'    => '3 Day Select',
            '13'    => 'Next Day Air Saver',
            '14'    => 'Next Day Air Early A.M.',
            '54'    => 'Ups Worldwide Express Plus',
            '59'    => '2nd Day Air A.M.',
            '65'    => 'UPS World Wide Saver',
            '82'    => 'Today Standard',
            '83'    => 'Today Dedicated Courier',
            '84'    => 'Today Intercity',
            '85'    => 'Today Express',
            '86'    => 'Today Express Saver',
            '03'    => 'Ups Ground',

        ];

        foreach ($mapServices as $key => $service) 
        {
            if ($key == $serviceCode) 
            {
                return $service;
            }
        }

        return $serviceCode;
    }

    /**
     * convert current weight unit to LBS
     *
     * @param string $weight
     **/
    public function getWeight($weight) 
    {

        $coreWeightUnit = strtoupper(core()->getConfigData('general.general.locale_options.weight_unit'));

        $upsWeightUnit = strtoupper(core()->getConfigData('sales.carriers.ups.weight_unit'));

        $convertedWeight = '';

        if ($coreWeightUnit == 'LBS') 
        {
            if ($upsWeightUnit == 'LBS') 
            {
                $convertedWeight = $weight;
            } else {
                //kgs to lbs
                $convertedWeight = $weight/0.45359237;

            }
        } else {
            $convertedWeight = $weight/0.45359237;
        }

        return $convertedWeight;
    }

    /**
     * Get The Current Error
     *
     * @param string $error
     **/
    public function getErrorLog($errors) 
    {

        foreach ($errors->Response->Error as $errorLog)
        {
            $exception[] = $errorLog->ErrorDescription;
        }
        
        $status = $errors->Response->ResponseStatusDescription;
        
        if (gettype($errors->Response->Error) !== 'array') 
        {
            $status = $errors->Response->Error->ErrorSeverity;

            $exception[] = $errors->Response->Error->ErrorDescription;
        }

        $logs = ['status' => $status, 'description' => $exception];

        $shippingLog = new Logger('shipping');
        $shippingLog->pushHandler(new StreamHandler(storage_path('logs/ups.log')), Logger::INFO);
        $shippingLog->info('shipping', $logs);

        return true;
    }
}