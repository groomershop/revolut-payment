/*browser:true*/
/*global define*/

define([
  'jquery',
  'Revolut_Payment/js/view/payment/method-renderer/revolut-payment-component-core',
  'Magento_Checkout/js/model/full-screen-loader',
  'ko',
  'mage/storage',
  'Magento_Checkout/js/model/url-builder',
  window.checkoutConfig.payment.revolut.revolutSdk,
  window.checkoutConfig.payment.revolut.revolutBannerSdk,
], function (
  $,
  Component,
  fullScreenLoader,
  ko,
  storage,
  urlBuilder,
  RevolutCheckout,
  RevolutUpsell,
) {
  'use strict'
  return Component.extend({
    defaults: {
      template: 'Revolut_Payment/payment/revolut-pay',
      revolutSdk: window.checkoutConfig.payment.revolut.revolutSdk,
      revolutPay: ko.observable(null),
      errorWidgetTarget: '#show-error-pay-error',
    },

    createRevolutWidgetInstance: function () {
      let payInstance = this.revolutPay()
      let self = this

      if (payInstance !== null) {
        payInstance.destroy()
      }

      payInstance = RevolutCheckout.payments({
        locale: this.getPaymentConfig('locale'),
        publicToken: this.getPaymentConfig('publicKey'),
      })

      const context = 'checkout'
      const pageUrl = this.getPaymentConfig('checkoutUrl')
      const revolutPayRedirectUrl = this.getPaymentConfig('redirectUrl')

      const currency = this.getCurrency()
      const totalAmount = this.getTotalAmount()
      const target = document.getElementById('revolut-pay-element')
      
      if (!totalAmount || !currency || !target) return

      const paymentOptions = {
        currency: currency,
        totalAmount: totalAmount,
        validate: () => {
          return self.handleValidate()
        },
        createOrder: () => {
          return self.createOrUpdateRevolutOrder()
        },
        customer: {
          name: self.getBillingName(),
          email: self.getBillingEmail(),
          phone: self.getBillingPhone(),
        },
        __metadata: {
          environment: 'magento',
          context: context,
          origin_url: this.getPaymentConfig('originUrl'),
        },
        mobileRedirectUrls: {
          success: revolutPayRedirectUrl,
          failure: pageUrl,
          cancel: pageUrl,
        },
        buttonStyle: {
          cashback: this.getPaymentConfig('revButtonStyle', {}).cashback,
          cashbackCurrency: this.getCurrency(),
          radius: 'none',
          height: '50px',
        },
        __features: {
          skipOrderAmountCheck: true,
        },
      }

      payInstance.revolutPay.mount(target, paymentOptions)

      payInstance.revolutPay.on('payment', function (event) {
        switch (event.type) {
          case 'success':
            self.handleRevolutPaySuccess()
            break
          case 'error':
            self.handleError({
              message: [event.error.message].filter(Boolean).join(' '),
            })
            break
          case 'cancel':
            self.handleCancel()
            break
        }
      })

      self.revolutPay(payInstance)
      fullScreenLoader.stopLoader()
    },

    getCode: function () {
      return 'revolut_pay'
    },
    renderInformationalIcon: function () {
      const target = document.getElementById('revolut-pay-informational-icon')
      if (!target) return
      const informationalBannersData = this.getPaymentConfig('informationalBannersData')
      const locale = this.getPaymentConfig('locale')
      const publicKey = this.getPaymentConfig('publicKey')

      if (
        !informationalBannersData ||
        informationalBannersData.revolutPayTitleVariant === 'disabled'
      )
        return

      if (!locale || !publicKey) return

      const instanceUpsell = RevolutUpsell({
        locale,
        publicToken: publicKey,
      })

      instanceUpsell.promotionalBanner.mount(target, {
        amount: this.getTotalAmount(),
        variant:
          informationalBannersData.revolutPayTitleVariant === 'cashback'
            ? 'link'
            : informationalBannersData.revolutPayTitleVariant,
        currency: this.getCurrency(),
        style: {
          text:
            informationalBannersData.revolutPayTitleVariant === 'cashback'
              ? 'cashback'
              : null,
          color: 'blue',
        },
        __metadata: { channel: 'magento2' },
      })
    },
  })
})
