<?php


namespace Pakettikauppa\Logistics\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Pakettikauppa\Logistics\Helper\Api;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $dev;

    public function __construct(
        Cart $cart,
        Context $context,
        API $api
    ) {
        $this->dev = false;
        $this->api = $api;
        $this->cart = $cart;
        parent::__construct($context);
    }
    /**
     * Index action
     *
     * @return $this
     */
    public function execute()
    {
        if ($_POST) {
          $quote = $this->cart->getQuote();
          $zipcode = $_POST['zip_code'];
          $quote->setData('pickuppoint_zip',$zipcode);
          $shipping_address = $quote->getShippingAddress();
          $shipping_address->setCollectShippingRates(true)
                          ->collectShippingRates();
          $quote->save();
          echo "Success";
        }else{
          // DEVELOPER MODE ADDED
          // TO ENABLE IT CHANGE $this->dev to true;
          if($this->dev){
            if(isset($_GET['home'])){
              echo json_encode($this->api->getHomeDelivery(true));
            }else{
              echo json_encode($this->api->getPickuppoints('90940'));
            }
          }
        }
    }
}
