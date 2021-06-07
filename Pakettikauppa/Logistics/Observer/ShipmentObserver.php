<?php
namespace Pakettikauppa\Logistics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Pakettikauppa\Logistics\Helper\Api;
use Pakettikauppa\Logistics\Helper\Data;
use Psr\Log\LoggerInterface;

class ShipmentObserver implements ObserverInterface
{
    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    protected $_order;

    public function __construct(
        OrderInterface $order,
        LoggerInterface $logger,
        TrackFactory $trackFactory,
        Api $apiHelper,
        Data $dataHelper
    ) {
        $this->_order = $order;
        $this->logger = $logger;
        $this->apiHelper = $apiHelper;
        $this->dataHelper = $dataHelper;
        $this->trackFactory = $trackFactory;
    }
    public function execute(Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();
            $shipping_method = $shipment->getOrder()->getData('shipping_method');

            if ($this->dataHelper->isPakettikauppa($shipping_method)) {
                if (count($shipment->getAllTracks())==0) {
                    $code = $this->dataHelper->getMethod($shipping_method);
                    if ($code=='pktkphomedelivery') {
                        $carrier = $shipment->getOrder()->getData('home_delivery_service_provider');
                    }
                    if ($code=='pktkppickuppoint') {
                        $carrier = $shipment->getOrder()->getData('pickup_point_provider');
                    }
                    $orderId = $shipment->getOrder()->getID();
                    $order = $this->_order->load($orderId);
                    $tracking_number = $this->apiHelper->createShipment($order);
                    $name = $this->dataHelper->getCurrentCarrierTitle($shipping_method);

                    $data = [
                  'carrier_code' => $code,
                  'title' => $name,
                  'number' => $tracking_number
              ];
                    $track = $this->trackFactory->create()->addData($data);
                    $shipment->addTrack($track);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
