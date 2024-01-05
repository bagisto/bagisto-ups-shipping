<?php

namespace Webkul\UpsShipping\Repositories;

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
     * @param $cartItems
     * 
     * @return mixed
     */
    public function getSellerAdminData($cartItems) 
    {
        $adminProducts = [];

        foreach ($cartItems as $item) {
            array_push($adminProducts, $item);
        }

        return $adminProducts;
    }

    /**
     * Get the Allowde Services
     * @param $allowedServices
     * @param $service
     * 
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
        }

        return false;
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
            }
            $allowedMethods[] = $allowedMethod;
        }

        if (isset($allowedMethods)) {
            return $this->getCommonMethods($allowedMethods);
        }

        return false;   
    }

    /**
     * get the Common method
     *
     * @param $Methods
     */
    public function getCommonMethods($methods)
    {
        $countMethods = count($methods);

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

        if (empty($finalServices)) {
            return false;
        }

        return $finalServices;
    }
}

