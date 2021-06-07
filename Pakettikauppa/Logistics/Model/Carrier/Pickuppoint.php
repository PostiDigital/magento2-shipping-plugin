<?php
namespace Pakettikauppa\Logistics\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Pickuppoint extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'pktkppickuppoint';

    public function __construct(
    \Psr\Log\LoggerInterface $logger,
    \Magento\Framework\Registry $registry,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Pakettikauppa\Logistics\Helper\Data $dataHelper,
    \Pakettikauppa\Logistics\Helper\Api $apiHelper,
    \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
    \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
    \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
    \Magento\Sales\Model\Order\Shipment\Track $trackFactory,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Checkout\Model\Session $session,
    array $data = []
  ) {
        $this->session = $session;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->apiHelper = $apiHelper;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->trackFactory = $trackFactory;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getAllowedMethods()
    {
        return ['pktkppickuppoint' => $this->getConfigData('name')];
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $result = $this->rateResultFactory->create();
        $data = [];
        if (null !== $this->registry->registry('pktkpicons')) {
            $data = $this->registry->registry('pktkpicons');
            $this->registry->unregister('pktkpicons');
        }
        $zip = $this->dataHelper->getZip();
        if ($zip) {
            $pickuppoints = $this->apiHelper->getPickuppoints($zip);
            if (!empty($pickuppoints) && count($pickuppoints) > 0) {
                $cart_value = $request->getPackageValueWithDiscount();

                foreach ($pickuppoints as $pp) {
                    $carrier_code = $this->dataHelper->getCarrierCode($pp->provider, 'pickuppoint');
                    if ($carrier_code) {
                        $enabled = $this->scopeConfig->getValue('carriers/' . $carrier_code . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    } else {
                        $enabled = 0;
                    }

                    if ($enabled == 1) {
                        $method = $this->rateMethodFactory->create();
                        $method->setCarrier('pktkppickuppoint');
                        $method->setCarrierTitle($pp->provider . " - " . $pp->name . ": " . $pp->street_address . ", " . $pp->postcode . ", " . $pp->city);
                        if (property_exists($pp, 'provider_logo')) {
                            $data[$pp->provider . " - " . $pp->name . ": " . $pp->street_address . ", " . $pp->postcode . ", " . $pp->city] = '<img src="' . $pp->provider_logo . '" alt="' . $pp->provider . '"/>';
                        }
                        $db_price =  $this->scopeConfig->getValue('carriers/' . $carrier_code . '/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $db_title =  $this->scopeConfig->getValue('carriers/' . $carrier_code . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $conf_price = $this->getConfigData('price');
                        $conf_title = $this->getConfigData('title');
                        if ($db_price == '') {
                            $price = $conf_price;
                        } else {
                            $price = $db_price;
                        }
                        if ($db_title == '') {
                            $title = $conf_title;
                        } else {
                            $title = $db_title;
                        }

                        // // DISCOUNT PRICE
                        $minimum =  $this->scopeConfig->getValue('carriers/' . $carrier_code . '/cart_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $new_price =  $this->scopeConfig->getValue('carriers/' . $carrier_code . '/new_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        if ($cart_value && $minimum && $cart_value >= $minimum) {
                            $price = $new_price;
                        }
                        
                        if ($request->getFreeShipping()){
                            $price = 0;
                        }

                        $method->setMethod($pp->pickup_point_id);
                        $method->setMethodTitle($title);
                        $method->setPrice($price);

                        $method->setCost($price);
                        $result->append($method);
                    }
                }
            }
        }
        $this->registry->register('pktkpicons', $data);
        return $result;
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    public function getTrackingInfo($tracking)
    {
        $title = 'Posti';
        $base_url = $this->storeManager->getStore()->getBaseUrl() . 'logistics/tracking/';
        $track = $this->trackFactory;
        $track->setUrl($base_url . '?code=' . $tracking)
          ->setCarrierTitle($title)
          ->setTracking($tracking);
        return $track;
    }
}
