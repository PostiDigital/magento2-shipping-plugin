<?php
namespace Pakettikauppa\Logistics\Model\Rate;

class Result extends \Magento\Shipping\Model\Rate\Result
{
  public function sortRatesByPrice()
    {
        return $this;
    }
}
