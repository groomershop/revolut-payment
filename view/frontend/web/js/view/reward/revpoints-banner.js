define([
  'jquery',
  'uiComponent',
  'Magento_Checkout/js/model/quote',
  'Revolut_Payment/js/utils/revolut-helper',
  window.checkoutConfig.payment.revolut.revolutBannerSdk,
], function ($, Component, quote, revolutHelper, RevolutUpsell) {
  'use strict'

  return Component.extend({
    initialize: function () {
      $(`script[src="${window.checkoutConfig.payment.revolut.revolutBannerSdk}"]`).attr(
        'id',
        'revolut-checkout',
      )
      this._super()
    },

    getTotalAmount: function (currency) {
      const quoteTotals = quote.totals()
      const totalAmount = quoteTotals ? quoteTotals.grand_total : 0
      return revolutHelper.getMinorAmount(totalAmount, currency)
    },

    initCheckoutPageInformationalBanner: function () {
      if (
        !revolutHelper.getPaymentConfig('isCheckoutPageActivated', false) ||
        !revolutHelper.getPaymentConfig('informationalBannersData', {})
          .isRevolutInformationalBannerEnabled
      )
        return

      this.mountRevolutPointsBanner({
        publicKey: revolutHelper.getPaymentConfig('publicKey'),
        locale: revolutHelper.getPaymentConfig('locale'),
        storeCurrencyCode: revolutHelper.getPaymentConfig('informationalBannersData', {})
          .storeCurrencyCode,
      })
    },

    initFastCheckoutInformationalBanner: function (
      revolutPayEnabled,
      informationalBannerConfig,
    ) {
      if (
        !revolutPayEnabled ||
        !informationalBannerConfig ||
        !informationalBannerConfig.isRevolutInformationalBannerEnabled
      )
        return
      this.mountRevolutPointsBanner(informationalBannerConfig)
    },

    mountRevolutPointsBanner: function (informationalBannerConfig) {
      const { publicKey, locale, storeCurrencyCode } = informationalBannerConfig
      if (!publicKey || !locale || !storeCurrencyCode) return

      const target = document.getElementById('revolut-points-banner')
      if (!target) return

      const instanceUpsell = RevolutUpsell({
        locale: locale,
        publicToken: publicKey,
      })

      instanceUpsell.promotionalBanner.mount(target, {
        currency: storeCurrencyCode,
        amount: this.getTotalAmount(storeCurrencyCode),
        variant: 'banner',
        __metadata: { channel: 'magento2' },
      })
    },
  })
})
