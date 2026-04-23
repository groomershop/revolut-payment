/*browser:true*/
/*global define*/

define([
  "ko",
  "jquery",
  "Revolut_Payment/js/view/payment/method-renderer/fast-checkout/revolut-fast-checkout",
], function (ko, $, Component) {
  "use strict";

  return Component.extend({
    method: window.fastCheckoutConfig.payment.revolut.RevolutPayPaymentMethodCode,

    createFastCheckoutButton: async function () {
      if (!$("#revolut-pay-fast-checkout-button").length) {
        return false;
      }

      $("#revolut-pay-fast-checkout-button").empty().append("<span></span>");

      const self = this;

      let instance = this.RevolutCheckout().payments({
        locale: this.getFastCheckoutConfig("locale"),
        publicToken: this.getFastCheckoutConfig("merchantPublicKey"),
      });

      const context = this.isCartPage() ? "cart" : "product";
      const pageUrl = this.isCartPage() ? this.getFastCheckoutConfig("cartUrl") : this.getFastCheckoutConfig("productUrl");
      const revolutPayRedirectUrl = this.getFastCheckoutConfig("revolutPayRedirectUrl");

      instance.revolutPay.mount("#revolut-pay-fast-checkout-button", {
        currency: self.currency(),
        totalAmount: self.amount(),
        requestShipping: true,
        validate() {
          return self.addToCart().then(function (result) {
            return Promise.resolve(result);
          });
        },
        createOrder: () => {
          return { publicId: self.publicId() };
        },
        buttonStyle: {
          cashback:this.getFastCheckoutConfig("revolutPayThemeConfigs", {}).cashback,
          radius: "none",
          cashbackCurrency: self.currency(),
          height: "45px",
        },
        mobileRedirectUrls: {
          success: revolutPayRedirectUrl,
          failure: pageUrl,
          cancel: pageUrl,
        },
        __metadata: {
          environment: "magento2",
          context: context,
          origin_url: this.getFastCheckoutConfig("baseUrl"),
        },
      });

      instance.revolutPay.on("payment", function (event) {
        switch (event.type) {
          case "success":
            $("body").loader("show");
            self.validateOrder(null, true).then(() => {
              self.processOrder();
            });
            break;
          case "error":
            self.displayError(event.error.message);
            break;
        }
      });
    },

    getData: function () {
      return {
        method: this.method,
        additional_data: {
          publicId: this.publicId(),
          setAgreement: true,
        },
      };
    },
  });
});
