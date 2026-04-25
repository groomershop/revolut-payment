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
      template: 'Revolut_Payment/payment/revolut-card',
      revolutCard: ko.observable(null),
      cardholderNameField: ko.observable(false),
      errorWidgetTarget: '#show-error-card-error',
    },

    createRevolutWidgetInstance: function () {
      this.createOrUpdateRevolutOrder().then(() => {
        this.cardholderNameField(this.getPaymentConfig('cardholderNameField'))

        let cardInstance = this.revolutCard()
        let self = this

        if (cardInstance !== null) {
          cardInstance.destroy()
        }

        cardInstance = RevolutCheckout(this.publicId()).createCardField({
          target: document.getElementById('revolut-card-element'),
          locale: this.getPaymentConfig('locale'),
          onSuccess() {
            self.handleSuccess()
          },
          onError(messages) {
            self.handleError(messages)
          },
          onValidation(messages) {
            self.showErrorMessage(messages)
          },
          onCancel() {
            self.handleCancel()
          },
        })

        this.revolutCard(cardInstance)
        fullScreenLoader.stopLoader()
      })
    },

    getCode: function () {
      return 'revolut_card'
    },
  })
})
