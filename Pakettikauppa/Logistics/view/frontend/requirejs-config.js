var config = {
  "map": {
    "*": {
      "Magento_Checkout/template/shipping.html":
          "Pakettikauppa_Logistics/template/shipping.html"
    }
  },
  config: {
    mixins: {
      'Magento_Checkout/js/model/shipping-rates-validation-rules': {
        'Pakettikauppa_Logistics/js/model/shipping-rates-validation-rules-mixin': true
      }
    }
  }
};
