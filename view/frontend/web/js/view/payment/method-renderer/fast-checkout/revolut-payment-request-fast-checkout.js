/*browser:true*/
/*global define*/

define([
  'ko',
  'jquery',
  'Revolut_Payment/js/view/payment/method-renderer/fast-checkout/revolut-fast-checkout',
], function (ko, $, Component) {
  'use strict'

  return Component.extend({
    method:
      window.fastCheckoutConfig.payment.revolut.RevolutPaymentRequestPaymentMethodCode,

    createFastCheckoutButton: async function () {
      if (!$('#revolut-payment-request-fast-checkout-button').length) {
        return false
      }

      if (this.paymentRequest()) {
        this.paymentRequest().destroy()
      }

      $('#revolut-payment-request-fast-checkout-button').empty().append('<span></span>')

      const self = this
      const RC = await this.RevolutCheckout()(self.publicId())

      const buttonStyle = this.getFastCheckoutConfig(
        'revolutPaymentRequestThemeConfigs',
        {},
      )
      buttonStyle['height'] = '45px'
      console.log(buttonStyle)

      let paymentRequest = RC.paymentRequest({
        target: document.querySelector(
          `#revolut-payment-request-fast-checkout-button span`,
        ),
        requestShipping: true,
        shippingOptions: self.shippingOptions,
        onClick() {},
        onShippingOptionChange: selectedShippingOption => {
          return self.setShippingOption(selectedShippingOption)
        },
        onShippingAddressChange: selectedShippingAddress => {
          return self.addToCart().then(() => {
            return self.loadShippingOptions(selectedShippingAddress)
          })
        },
        onSuccess: () => {
          return self.processOrder()
        },
        validate: address => {
          $('body').loader('show')
          return self.validateOrder(address)
        },
        onError: error => {
          self.displayError(error)
        },
        buttonStyle: buttonStyle,
      })

      paymentRequest.canMakePayment().then(result => {
        if (result) {
          paymentRequest.render()
        } else {
          paymentRequest.destroy()
        }
      })

      this.paymentRequest(paymentRequest)
    },
  })
})
