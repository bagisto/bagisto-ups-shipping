<?php

namespace Webkul\UpsShipping\Repositories;

/**
 * UPS Reposotory
 *
 * @author    Naresh Verma <naresh.verma327@webkul.com>
 * @copyright 2019 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class UpsRepository
{
    /**
     * SellerProduct Repository object
     *
     * @var array
     */
    protected $productRepository;


    /**
     * Get the Admin Product
     *
     * @return mixed
     */
    public function getSellerAdminData($cartItems) {
        $adminProducts = [];
        foreach ($cartItems as $item) {
            array_push($adminProducts, $item);
        }
        return $adminProducts;
    }

    /**
     * Get the Allowde Services
     * @param $allowedServices
     * @return $secvices
     */
    public function validateAllowedMethods($service, $allowedServices)
    {
        $count = 0;
        $totalCount = count($allowedServices);

        foreach ($allowedServices as $methods) {
            if ( in_array($service, $methods) ) {
                $count += 1;
            }
        }
        if ( $count == $totalCount ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the Common Services for all the cartProduct
     * @param $allServices
     */
    public function getAllowedMethods($allServices) {

        $allowedServices = explode(",", core()->getConfigData('sales.carriers.ups.services'));

        foreach ($allServices as $services) {
            $allowedMethod =[];
            foreach ($services as $service) {

                foreach ($service as $serviceType =>$upsService) {
                    if (in_array($serviceType , $allowedServices)) {
                        $allowedMethod[] = [
                            $serviceType => $upsService
                        ];
                    } else {
                        $notAllowed[] = [
                            $serviceType => $upsService
                        ];
                    }
                }
            }

            if ($allowedMethod == null) {
                continue;
            } else {
                $allowedMethods[] = $allowedMethod;
            }

        }

        if (isset($allowedMethods)) {

            return $this->getCommonMethods($allowedMethods);
        } else {
            return false;
        }
    }


    /**
     * get the Common method
     *
     * @param $Methods
     */
    public function getCommonMethods($methods)
    {
        $avilableServicesArray  = []; 
        $countMethods           = count($methods);

        foreach ($methods as $fedexMethods) {
            foreach ($fedexMethods as $key => $fedexMethod) {
                $avilableServicesArray[] = $key;
            }
        }

        $countServices = array_count_values($avilableServicesArray);
        $finalServices = [];

        foreach ($countServices as $serviceType => $servicesCount) {

            foreach ($methods as $fedexMethods) {
                foreach ($fedexMethods as $type => $fedexMethod) {
                    if ($serviceType == $type && $servicesCount == $countMethods) {

                        $finalServices[$serviceType][] =$fedexMethod;
                    }
                }
            }

            if ($finalServices == null) {
                continue;
            }
        }

        if (!empty($finalServices)) {
            return $finalServices;
        } else {
            return false;
        }
    }
}

