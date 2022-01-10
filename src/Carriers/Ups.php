<?php

namespace Webkul\UpsShipping\Carriers;

use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Shipping\Carriers\AbstractShipping;

/**
 * Ups Shipping Shipping.
 *
 */
class Ups extends AbstractShipping
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'ups';

    /**
     * Returns rate for flatrate
     *
     * @return array
     */
    public function calculate()
    {
        if (! core()->getConfigData('sales.carriers.ups.active'))
            return false;

        $shippingMethods    = [];
        $rates              = [];
        $shippingHelper     = app('Webkul\UpsShipping\Helpers\ShippingMethodHelper');
        $getCommonServices  = app('Webkul\UpsShipping\Repositories\UpsRepository');
        $data               = $shippingHelper->getAllCartProducts();
        $serviceData        = $getCommonServices->getCommonMethods($data['response']);

        $shippingRate = session()->get('shipping_rates');

        if ( isset($data['response']) && $data['response'] && !$data['errorResponse'] && !empty($serviceData) ) {
            foreach ($serviceData as $key => $upsServices) {
                $rate               = 0;
                $totalShippingCost  = 0;
                $upsMethod          = $key;
                $classId            = '';

                foreach ($upsServices as $upsRate) {
                    $classId             = $upsRate['classId'];
                    $rate               += $upsRate['rate'] * $upsRate['itemQuantity'];
                    $itemShippingCost    = $upsRate['rate'] * $upsRate['itemQuantity'];

                    if ( isset($rates[$key]) ) {
                        $rates[$key] = [
                            'amount'        => core()->convertPrice($rates[$key]['amount'] + $itemShippingCost),
                            'base_amount'   => $rates[$key]['base_amount'] + $itemShippingCost
                        ];
                    } else {
                        $rates[$key] = [
                            'amount'        => core()->convertPrice($itemShippingCost),
                            'base_amount'   => $itemShippingCost
                        ];
                    }

                    $totalShippingCost  += $itemShippingCost;
                }

                $object                     = new CartShippingRate;
                $object->carrier            = 'ups';
                $object->carrier_title      = $this->getConfigData('title') . ' (' . $this->getConfigData('description') . ')';
                $object->method             = 'ups_' . $classId;
                $object->method_title       = $upsMethod;
                $object->method_description = $this->getConfigData('title') . ' (' . $this->getConfigData('description') . ')';
                $object->is_calculate_tax   = $this->getConfigData('is_calculate_tax');
                $object->price              = core()->convertPrice($totalShippingCost);
                $object->base_price         = $totalShippingCost;

                $shippingRate = session()->get('shipping_rates');

                if (! is_array($shippingRate)) {
                    $shippingRates['ups'] = $rates;
                    session()->put('shipping_rates', $shippingRates);
                } else {
                    session()->put('shipping_rates.ups', $rates);
                }

                array_push($shippingMethods, $object);
            }

            return $shippingMethods;
        } else {
            return null;
        }
    }
}