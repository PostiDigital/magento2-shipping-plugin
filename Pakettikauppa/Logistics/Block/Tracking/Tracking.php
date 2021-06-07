<?php
namespace Pakettikauppa\Logistics\Block\Tracking;

use Pakettikauppa\Logistics\Helper\Api;
use Magento\Framework\View\Element\Template\Context;

class Tracking extends \Magento\Framework\View\Element\Template
{
    protected $code;

    function __construct(
      Context $context,
      Api $apiHelper
    ) {
      parent::__construct($context);
      $this->code = $this->getRequest()->getParam('code');
      $this->apiHelper = $apiHelper;
    }

    public function getTracking(){
      return $this->code;
    }

    public function getTrackingStatus(){
      $tracking = $this->apiHelper->getTracking($this->code);
      if(isset($tracking)){
        return array_reverse($tracking);
      }else{
        return false;
      }
    }
}
