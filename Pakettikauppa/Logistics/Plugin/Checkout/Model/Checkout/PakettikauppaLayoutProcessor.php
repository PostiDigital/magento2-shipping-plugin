<?php
namespace Pakettikauppa\Logistics\Plugin\Checkout\Model\Checkout;

use Magento\Checkout\Model\Cart;

class PakettikauppaLayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
     public function __construct(
         Cart $cart
     ) {
         $this->cart = $cart;
     }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        if ($this->cart) {
          $zip_pickup = $this->cart->getQuote()->getData('pickuppoint_zip');
          if ($zip_pickup) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['before-shipping-method-form']['children']['pickuppoint-form-container']
            ['children']['pickuppoint-form-fieldset']['children']['pickuppoint-zip']['value'] = $zip_pickup;
            return $jsLayout;
          }else {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['before-shipping-method-form']['children']['pickuppoint-form-container']
            ['children']['pickuppoint-form-fieldset']['children']['pickuppoint-zip']['value'] = '';
            return $jsLayout;
          }
        }
    }
}
