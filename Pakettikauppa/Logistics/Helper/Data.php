<?php
namespace Pakettikauppa\Logistics\Helper;

use Magento\Checkout\Model\Cart;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        Cart $cart,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession
    ) {
        $this->cart = $cart;
        $this->backendQuoteSession = $backendQuoteSession;
    }

    public function getCarrierCode($carrier, $name)
    {
        $carrier = preg_replace('/[^\00-\255]+/u', '', strtolower(preg_replace('/\s+/', '', $carrier)));
        $name = preg_replace('/[^\00-\255]+/u', '', strtolower(preg_replace('/\s+/', '', preg_replace('/\-/', '', $name))));
        $code = $carrier . '_' . $name;
        if ($code == 'posti_palautus') {
            return false;
        } else {
            return $code;
        }
    }

    public function getZip()
    {
        $zip = false;

        if ($zip = $this->cart->getQuote()->getData('pickuppoint_zip')) {
            return $zip;
        } elseif ($zip = $this->cart->getQuote()->getShippingAddress()->getPostcode()) {
            return $zip;
        } elseif ($zip = $this->backendQuoteSession->getQuote()->getShippingAddress()->getPostcode()) {
            return $zip;
        }
        return $zip;
    }

    public function getCountry() {
        $country = false;

        if ($country = $this->cart->getQuote()->getShippingAddress()->getCountryId()) {
            return $country;
        } elseif ($country = $this->backendQuoteSession->getQuote()->getShippingAddress()->getCountryId()) {
            return $country;
        }
        return $country;
    }

    public function getPickupPointServiceCode($data, $provider)
    {
        switch($provider) {
            case 'Posti':
                return 2103;
            case 'Matkahuolto':
                return 90080;
            case 'DB Schenker':
                return 80010;
            default:
                return 0;
        }
        /*
        $result = 0;
        foreach ($data as $d) {
            if ($d->service_provider == $provider) {
                if (count($d->additional_services)>0) {
                    foreach ($d->additional_services as $service) {
                        if ($service->service_code == '2106') {
                            $result = $d->shipping_method_code;
                            break;
                        }
                    }
                }
            }
        }
        return $result;
        */
    }

    public function getMethod($code)
    {
        if (strpos($code, 'pktkppickuppoint') !== false) {
            return 'pktkppickuppoint';
        }
        if (strpos($code, 'pktkphomedelivery') !== false) {
            return 'pktkphomedelivery';
        }
    }

    public function isPakettikauppa($code)
    {
        if (strpos($code, 'pktkppickuppoint') !== false || strpos($code, 'pktkphomedelivery') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function getCurrentCarrierTitle($code)
    {
        $methods = $this->cart->getQuote()->getShippingAddress()->getShippingRatesCollection()->getData();
        foreach ($methods as $method) {
            if ($method['code'] == $code) {
                if ($method['carrier'] == 'pktkphomedelivery') {
                    $title = $method['method_title'];
                }
                if ($method['carrier'] == 'pktkppickuppoint') {
                    $title =  $method['method_title'];
                }
            }
        }
        if (isset($title)) {
            return $title;
        } else {
            return 'Unknown';
        }
    }

    public function getShipmentStatusText($code)
    {
        switch ($code) {
          case "13":
              $status = "Item is collected from sender - picked up";
              break;
          case "20":
              $status = "Exception";
              break;
          case "22":
              $status = "Item has been handed over to the recipient";
              break;
          case "31":
              $status = "Item is in transport";
              break;
          case "38":
              $status = "C.O.D payment is paid to the sender";
              break;
          case "45":
              $status = "Informed consignee of arrival";
              break;
          case "48":
              $status = "Item is loaded onto a means of transport";
              break;
          case "56":
              $status = "Item not delivered – delivery attempt made";
              break;
          case "68":
              $status = "Pre-information is received from sender";
              break;
          case "71":
              $status = "Item is ready for delivery transportation";
              break;
          case "77":
              $status = "Item is returning to the sender";
              break;
          case "91":
              $status = "Item is arrived to a post office";
              break;
          case "99":
              $status = "Outbound";
              break;
          default:
              $status = "Unknown";
      }
        return $status;
    }

    public function getTrackingCode($order)
    {
        $track_data = $order->getTracksCollection()->getData();
        $track_number = $track_data[0]['track_number'];

        return $track_number;
    }

    public function getTrackingUrl($order)
    {
        $track_data = $order->getTracksCollection()->getData();
        $track_number = $track_data[0]['track_number'];
        return '/pub/labels/' . $track_number . '.pdf';
    }
}
