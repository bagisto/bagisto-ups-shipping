<?php

namespace Webkul\UpsShipping\Carriers;

use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Shipping\Carriers\AbstractShipping;
use Webkul\Checkout\Facades\Cart;

/**
 * Ups Shipping.
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
        $uspsMethod = '';

        $shippingMethods = [];

        $rates = [];

        $shippingHelper = app('Webkul\UpsShipping\Helpers\ShippingMethodHelper');

        $cart = Cart::getCart();
        
        $address = $cart->shipping_address;
      
        $data = $shippingHelper->getAllCartProducts($address);

        $marketplaceShipping = session()->get('marketplace_shipping_rates');

        if (! $this->isAvailable())
            return false;

        if (isset ($data) && $data == true) 
        {

            foreach ($data as $key => $fedexServices) 
            {
                $rate = 0;
                $totalShippingCost = 0;
                $upsMethod = $key;
                $methodCode = $key;

                foreach ($fedexServices as $methods => $upsRate) 
                {
                    $rate += $upsRate['rate'] * $upsRate['itemQuantity'];
                    $sellerId = $upsRate['marketplace_seller_id'];

                    $itemShippingCost =  $upsRate['rate'] * $upsRate['itemQuantity'];

                    $rates[$key][$sellerId] = [
                        'amount' => core()->convertPrice($itemShippingCost),
                        'base_amount' => $itemShippingCost
                    ];

                    if (isset ($rates[$key][$sellerId])) 
                    {
                        $rates[$key][$sellerId] = [
                                'amount' => core()->convertPrice($rates[$key][$sellerId]['amount'] + $itemShippingCost),
                                'base_amount' => $rates[$key][$sellerId]['base_amount'] + $itemShippingCost
                            ];
                    }

                    $totalShippingCost += $itemShippingCost;
                }
               
                $object = new CartShippingRate;

                $object->carrier = 'mpups';
                $object->carrier_title  = $this->getConfigData('title');
                $object->method  = 'mpups_' . '' . $methodCode;
                $object->method_title   = $this->getConfigData('title');
                $object->method_description = $upsMethod;

                $object->price = core()->convertPrice($totalShippingCost);
                $object->base_price = $totalShippingCost;

                $marketplaceShippingRates = session()->get('marketplace_shipping_rates');

                if (! is_array($marketplaceShipping)) 
                {
                    $marketplaceShippingRates['mpupsshipping'] = ['mpupsshipping' => $rates];
                    session()->put('marketplace_shipping_rates', $marketplaceShippingRates);

                } else {
                    $marketplaceFedexShipping = ['mpupshipping' => $rates];
                }

                array_push($shippingMethods, $object);
            }

            if (isset ($marketplaceFedexShipping)) 
            {
                session()->put('marketplace_shipping_rates.mpupshipping', $marketplaceFedexShipping);
            }

            return $shippingMethods;
        }
    }
}
