/*browser:true*/
/*global define*/

define([
  'jquery',
  'Revolut_Payment/js/view/payment/method-renderer/revolut-payment-component-core',
  'Magento_Checkout/js/model/full-screen-loader',
  'ko',
  window.checkoutConfig.payment.revolut.revolutSdk,
  window.checkoutConfig.payment.revolut.revolutBannerSdk,
], function ($, Component, fullScreenLoader, ko, RevolutCheckout, RevolutUpsell) {
  'use strict'
  return Component.extend({
    defaults: {
      template: 'Revolut_Payment/payment/revolut-card',
      revolutCard: ko.observable(null),
      instanceUpsell: ko.observable(null),
      cardholderNameField: ko.observable(false),
      errorWidgetTarget: '#show-error-card-error',
    },

    createRevolutWidgetInstance: function () {
      this.createOrUpdateRevolutOrder().then(() => {
        this.createRevolutCardBannerInstance()
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

    createRevolutCardBannerInstance: function () {
      if (!this.getPaymentConfig('informationalBannersData', {}).isGatewayBannerEnabled) {
        return
      }
      let instanceUpsell = this.instanceUpsell()

      if (instanceUpsell != null) {
        instanceUpsell.destroy()
      }

      instanceUpsell = RevolutUpsell({
        locale: this.getPaymentConfig('locale'),
        publicToken: this.getPaymentConfig('publicKey'),
      })

      instanceUpsell.cardGatewayBanner.mount(
        document.getElementById('revolut-upsell-banner-element'),
        {
          orderToken: this.publicId(),
        },
      )

      this.instanceUpsell(instanceUpsell)
    },

    getCode: function () {
      return 'revolut_card'
    },
  })
})
