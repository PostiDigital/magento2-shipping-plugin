<?php
namespace Pakettikauppa\Logistics\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderDetailsView extends \Magento\Backend\Block\Template
{

    protected $orderRepository;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ){
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    public function getPickuppointDetails(){
        $data = [];
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($order_id);
        $shipping_method_code = $order->getData('paketikauppa_smc');
        //return empty if not pickup point
        if ($shipping_method_code != "2103"){
            return [];
        }
        $data['pickup_point_provider'] = $order->getData('pickup_point_provider');
        $data['pickup_point_location'] = $order->getData('pickup_point_location');
        $data['pickup_point_name'] = $order->getData('pickup_point_name');
        $data['pickup_point_street_address'] = $order->getData('pickup_point_street_address');
        $data['pickup_point_postcode'] = $order->getData('pickup_point_postcode');
        $data['pickup_point_city'] = $order->getData('pickup_point_city');
        $data['pickup_point_country'] = $order->getData('pickup_point_country');
        $data['pickup_point_description'] = $order->getData('pickup_point_description');
        
        return $data;
    }
}
