/*browser:true*/
/*global define*/

define([
  'jquery',
  'Revolut_Payment/js/view/payment/method-renderer/revolut-payment-component-core',
  'Magento_Checkout/js/model/full-screen-loader',
  'ko',
  window.checkoutConfig.payment.revolut.revolutSdk,
], function ($, Component, fullScreenLoader, ko, RevolutCheckout) {
  'use strict'

  return Component.extend({
    defaults: {
      template: 'Revolut_Payment/payment/revolut-payment-request',
      paymentOptionName: ko.observable('Pay'),
      revolutSdk: window.checkoutConfig.payment.revolut.revolutSdk,
      paymentRequest: ko.observable(null),
      errorWidgetTarget: '#payment-request-error-widget',
    },

    createRevolutWidgetInstance: function () {
      const self = this

      if (self.paymentRequest()) {
        self.paymentRequest().destroy()
      }

      const { paymentRequest } = RevolutCheckout.payments({
        locale: this.getPaymentConfig('locale'),
        publicToken: this.getPaymentConfig('publicKey'),
      })

      const totalAmount = this.getTotalAmount()
      const currency = this.getCurrency()
      const target = document.getElementById('revolut-payment-request-element')

      if (!totalAmount || !currency || !target) return

      const request = paymentRequest.mount(target, {
        currency: currency,
        amount: totalAmount,
        requestShipping: false,
        buttonStyle: this.getPaymentConfig('prButtonStyle', {}),
        createOrder: () => {
          return self.createOrUpdateRevolutOrder()
        },
        onSuccess: () => {
          self.handleSuccess()
        },
        validate: address => {
          return self.handleValidate()
        },
        onClick: () => {
          if (!self.validate() || !self.customValidations()) {
            setTimeout(() => {
              request.destroy()
              self.paymentRequest(null)
              self.createRevolutWidgetInstance()
            }, 500)
          }
        },
        onError: error => {
          self.handleError([error])
        },
      })

      request.canMakePayment().then(result => {
        let methodName = result == 'googlePay' ? 'Google Pay' : 'Apple Pay'
        result == 'googlePay'
          ? $('.revolut-apple-pay-logo').hide()
          : $('.revolut-google-pay-logo').hide()
        self.paymentOptionName(methodName)
        if (result) {
          request.render()
        } else {
          request.destroy()
        }
      })
      self.paymentRequest(request)
      fullScreenLoader.stopLoader()
    },

    getCode: function () {
      return 'revolut_payment_request'
    },
  })
})
