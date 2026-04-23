define([], function () {
  'use strict'

  return {
    getConfig: function (configStore, configKey, defaultValue, isPaymentConfig) {
      try {
        
        let config = configStore.payment.revolut[configKey]

        if( ! isPaymentConfig ) {
          config = configStore[configKey]
        }

        if (typeof config !== 'undefined') {
          return config
        }
        return defaultValue
      } catch (error) {
        return defaultValue
      }
    },

    getPaymentConfig: function (key, defaultValue) {
      return this.getConfig(window.checkoutConfig, key, defaultValue, true)
    },

    getCheckoutConfig: function (key, defaultValue) {
      return this.getConfig(window.checkoutConfig, key, defaultValue, false)
    },

    getFastCheckoutConfig: function (key, defaultValue) {
      return this.getConfig(window.fastCheckoutConfig, key, defaultValue, true)
    },

    getMinorAmount: function (amount, currency) {
      const options = { style: 'currency', currency }
      const numberFormat = new Intl.NumberFormat('en-GB', options)
      const parts = numberFormat.formatToParts(amount)
      const fractionPart = parts.find(part => part.type === 'fraction')
      const fractionalLength = fractionPart ? fractionPart.value.length : 0
      const minorUnitFactor = Math.pow(10, fractionalLength)

      return amount * minorUnitFactor
    },
  }
})
