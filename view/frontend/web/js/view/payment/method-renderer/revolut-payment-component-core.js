/*browser:true*/
/*global define*/

define([
  'jquery',
  'Magento_Payment/js/view/payment/cc-form',
  'Magento_Checkout/js/model/url-builder',
  'mage/storage',
  'Magento_Checkout/js/model/full-screen-loader',
  'ko',
  'Magento_Checkout/js/model/quote',
  'Magento_Checkout/js/model/totals',
  'Magento_Checkout/js/action/redirect-on-success',
  'Magento_CheckoutAgreements/js/model/agreement-validator',
  'Magento_Checkout/js/model/payment/additional-validators',
  'Magento_Checkout/js/action/set-payment-information',
  'Magento_Customer/js/customer-data',
  'Magento_CheckoutAgreements/js/model/agreements-assigner',
  'mage/url',
  'Revolut_Payment/js/utils/revolut-helper',
  'uiRegistry',
], function (
  $,
  Component,
  urlBuilder,
  storage,
  fullScreenLoader,
  ko,
  quote,
  orderTotals,
  redirectOnSuccessAction,
  agreementValidator,
  additionalValidators,
  setPaymentInformation,
  customerData,
  agreementsAssigner,
  mageUrl,
  revolutHelper,
  uiRegistry,
) {
  'use strict'
  return Component.extend({
    defaults: {
      showPostCode: window.checkoutConfig.payment.revolut.showPostCode,
      fireCheckoutValidator: ko.observable(null),
      revolutCard: ko.observable(null),
      isPaymentTotalUpdateAllowed: ko.observable(true),
      updateOrderTotals: ko.observable(Promise.resolve()),
      publicId: ko.observable(null),
      publicKey: ko.observable(null),
      displayAmexLogo: ko.observable(false),
      errorCreateRevolutOrder: ko.observable(null),
      retryOrderPlaceActionCount: ko.observable(0),
      cardholderNameField: ko.observable(false),
      errorWidgetTarget: '#show-error-card-error',
      orderCanceledMsg:
        'An error occurred while processing your order. Your payment has been canceled, please review order details and try again.',
      retryOrderPlaceActionLimit: 4,
      retryOrderPlaceActionDelay: 2,
    },

    quoteTotals: quote.getTotals(),

    getTotalAmount: function () {
      if (!orderTotals.totals() || !this.getCurrency()) return 0
      const grandTotal = parseFloat(orderTotals.getSegment('grand_total').value)
      return this.getMinorAmount(grandTotal, this.getCurrency())
    },

    getCurrency: function () {
      if (!orderTotals.totals()) return ''
      return orderTotals.totals().quote_currency_code
    },

    getPaymentConfig: function (key, defaultValue = '') {
      return revolutHelper.getPaymentConfig(key, (defaultValue = ''))
    },
    getMinorAmount: function (amount, currency) {
      return revolutHelper.getMinorAmount(amount, currency)
    },
    initialize: function () {
      $(`script[src="${window.checkoutConfig.payment.revolut.revolutSdk}"]`).attr(
        'id',
        'revolut-checkout',
      )
this._super()
      let self = this
      var currentQuoteTotals = quote.totals()
      this.checkAmexLogo()
      this.mayebSetFireCheckoutValidator()

      this.quoteTotals.subscribe(function (totals) {
        if (!this.publicId() || !this.isPaymentTotalUpdateAllowed()) {
          return
        }

        if (currentQuoteTotals.grand_total === totals.grand_total) {
          return
        }

        currentQuoteTotals = totals

        self.createRevolutWidgetInstance()
      }, this)
    },


    createOrUpdateRevolutOrder: function () {
      let self = this
      return new Promise((resolve, reject) => {
        storage
          .post(urlBuilder.createUrl('/revolut/order/create', {}))
          .done(function (response) {
            if (!response.success) {
              reject()
              self.errorCreateRevolutOrder(response.message)
              self.messageContainer.addErrorMessage({
                message: $.mage.__(response.message),
              })
              fullScreenLoader.stopLoader()
              return
            }
            self.publicId(response.public_id)
            resolve({ publicId: self.publicId() })
          })
          .fail(() => {
            reject()
            self.errorCreateRevolutOrder('Something went wrong!')
            self.messageContainer.addErrorMessage({
              message: $.mage.__('Something went wrong!'),
            })
            fullScreenLoader.stopLoader()
          })
      })
    },

    createRevolutForm: function (errorMsg = '') {
      let self = this
      fullScreenLoader.startLoader()
      self.createRevolutWidgetInstance()
      if (errorMsg && typeof errorMsg !== 'object') {
        self.handleError({
          message: errorMsg,
        })
      }
      self.isPlaceOrderActionAllowed(true)
    },

    selectRevolutPaymentMethod: function () {
      $(this.errorWidgetTarget).hide()
      this.createRevolutForm()
    },

    checkAmexLogo: function () {
      const availableCardBrands = this.getPaymentConfig('availableCardBrands')

      if (
        availableCardBrands &&
        Array.isArray(availableCardBrands) &&
        availableCardBrands.includes('amex')
      ) {
        this.displayAmexLogo(true)
      }
    },

    initObservable: function () {
      this._super()
      return this
    },

    placeOrder: function (data, event) {
      let self = this

      if (event) {
        event.preventDefault()
      }

      if (
        !this.isPlaceOrderActionAllowed() ||
        !this.validate() ||
        !this.customValidations()
      ) {
        return false
      }

      if (this.errorCreateRevolutOrder()) {
        this.messageContainer.addErrorMessage({
          message: $.mage.__(this.errorCreateRevolutOrder()),
        })

        fullScreenLoader.stopLoader()

        return false
      }

      this.isPlaceOrderActionAllowed(false)
      fullScreenLoader.startLoader()

      $.when(setPaymentInformation(this.messageContainer, this.getData()))
        .done(function () {
          self.updateOrderTotals().then(function () {
            self.createOrUpdateRevolutOrder().then(() => {
              self.revolutCard().submit({
                email: self.getBillingEmail(),
                phone: self.getBillingPhone(),
                name: self.getBillingName(),
                orderUpdated: true,
                billingAddress: self.getFormattedBillingAddress(),
              })
            })
          })
        })
        .fail(function (response) {
          fullScreenLoader.stopLoader()
        })

      return true
    },

    getFormattedBillingAddress: function () {
      let address = quote.billingAddress()

      if (!address) return null

      let streetLine = undefined
      if (address.street && address.street.length > 0) {
        streetLine = address.street[0]
      }

      if (!address.countryId || !address.postcode) {
        return null
      }

      return {
        countryCode: address.countryId,
        region: address.regionCode,
        city: address.city,
        streetLine1: streetLine,
        postcode: address.postcode,
      }
    },

    getBillingName: function () {
      let billingAddress = quote.billingAddress()

      if (!billingAddress) return null

      if (this.cardholderNameField()) {
        return $('#revolut-card-cardholder-name').val()
      }

      if (billingAddress.firstname && billingAddress.lastname) {
        return `${billingAddress.firstname} ${billingAddress.lastname}`
      }

      return ''
    },

    getBillingEmail: function () {
      if (quote.guestEmail) {
        return quote.guestEmail
      } else if (
        revolutHelper.getCheckoutConfig('customerData') &&
        revolutHelper.getCheckoutConfig('customerData').email
      ) {
        return revolutHelper.getCheckoutConfig('customerData').email
      }

      return ''
    },

    getBillingPhone: function () {
      let billingAddress = quote.billingAddress()
      if (!billingAddress) return null

      if (billingAddress.telephone) return billingAddress.telephone

      let shippingAddress = quote.shippingAddress()
      if (shippingAddress.telephone) return shippingAddress.telephone

      return ''
    },

    validateBillingAddressForm: function () {
      if (quote.billingAddress() != null) {
        return true
      }
      const billingAddressComponent = uiRegistry.get(
        'checkout.steps.billing-step.payment.payments-list.' + this.getCode() + '-form',
      )
      if (!billingAddressComponent) {
        this.messageContainer.addErrorMessage({
          message: $.mage.__('Please verify billing address fields and retry again'),
        })
        return false
      }

      billingAddressComponent.updateAddress()
      return false
    },

    customValidations: function (elm) {
      return (
        agreementValidator.validate() &&
        additionalValidators.validate() &&
        this.validateBillingAddressForm() &&
        this.validateFireCheckout()
      )
    },

    mayebSetFireCheckoutValidator: function (elm) {
      var checkoutConfig = window.checkoutConfig
      if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
        return true
      }

      let self = this

      require(['Swissup_Firecheckout/js/model/validator'], function (
        FireCheckoutValidator,
      ) {
        self.fireCheckoutValidator(FireCheckoutValidator)
      })
    },

    validateFireCheckout: function (elm) {
      var checkoutConfig = window.checkoutConfig
      if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
        return true
      }

      if (this.fireCheckoutValidator()) {
        return this.fireCheckoutValidator().validate()
      }

      return false
    },

    context: function () {
      return this
    },

    getCode: function () {
      return 'revolut'
    },

    isActive: function () {
      return true
    },

    getData: function () {
      return {
        method: this.getCode(),
        additional_data: {
          publicId: this.publicId(),
        },
      }
    },

    showErrorMessage: function (messages) {
      if (
        !messages ||
        (Array.isArray(messages) &&
          !messages.length &&
          this.orderCanceledMsg == $(this.errorWidgetTarget).text())
      ) {
        return
      }

      this.isPaymentTotalUpdateAllowed(true)
      $(this.errorWidgetTarget).hide()
      $('#revolutForm').removeClass('revolut-cardfield-error')
      $('#revolut-cardholder-name-container').removeClass('revolut-cardholder-name-error')

      let errorMessage = ''

      if (messages && Array.isArray(messages)) {
        let errorMessages = []
        messages.forEach(function (message) {
          if (message) {
            errorMessages.push(message.message)
          }
        })
        errorMessage = errorMessages.join(', ')
      } else {
        if (messages.message) {
          errorMessage = messages.message
        }
      }

      if (errorMessage) {
        $(this.errorWidgetTarget).text(errorMessage)
        $(this.errorWidgetTarget).show()
        $('#revolutForm').addClass('revolut-cardfield-error')
        $('#revolut-cardholder-name-container').addClass('revolut-cardholder-name-error')

        if (
          $('#revolut-card-cardholder-name').val() &&
          $('#revolut-card-cardholder-name').val().trim().split(/\s+/).length >= 2
        ) {
          $('#revolut-cardholder-name-container').removeClass(
            'revolut-cardholder-name-error',
          )
        }
        fullScreenLoader.stopLoader()
      }

      this.isPlaceOrderActionAllowed(true)
    },

    handleError: function (messages) {
      this.showErrorMessage(messages)
      fullScreenLoader.stopLoader()
    },

    handleSuccess: function () {
      let self = this
      this.isPaymentTotalUpdateAllowed(false)
      self.updateOrderTotals().then(function () {
        self
          .getPlaceOrderDeferredObject()
          .fail(function (response) {
            self.logError(response)
            self.retryingOrderPlace()

            if (self.retryOrderPlaceActionCount() < self.retryOrderPlaceActionLimit) {
              let retryDelay =
                self.retryOrderPlaceActionCount() * self.retryOrderPlaceActionDelay * 1000

              fullScreenLoader.startLoader()
              self.isPlaceOrderActionAllowed(false)
              setTimeout(function () {
                fullScreenLoader.stopLoader()
                self.handleSuccess()
              }, retryDelay)

              return
            }

            self.cancelPayment(response)
            self.isPlaceOrderActionAllowed(false)
            self.retryOrderPlaceActionCount(0)
          })
          .done(function () {
            self.afterPlaceOrder()
            redirectOnSuccessAction.execute()
          })
      })
    },

    handleRevolutPaySuccess: function () {
      let self = this
      this.isPaymentTotalUpdateAllowed(false)
      self.updateOrderTotals().then(function () {
        self
          .placeMagentoOrder()
          .then(response => {
            self.afterPlaceOrder()
            var clearData = {
              selectedShippingAddress: null,
              shippingAddressFromData: null,
              newCustomerShippingAddress: null,
              selectedShippingRate: null,
              selectedPaymentMethod: null,
              selectedBillingAddress: null,
              billingAddressFromData: null,
              newCustomerBillingAddress: null,
            }

            customerData.set('checkout-data', clearData)
            customerData.invalidate(['cart'])
            redirectOnSuccessAction.execute()
          })
          .catch(response => {
            self.retryingOrderPlace()

            if (self.retryOrderPlaceActionCount() < self.retryOrderPlaceActionLimit) {
              let retryDelay =
                self.retryOrderPlaceActionCount() * self.retryOrderPlaceActionDelay * 1000

              fullScreenLoader.startLoader()
              self.isPlaceOrderActionAllowed(false)
              setTimeout(function () {
                fullScreenLoader.stopLoader()
                self.handleRevolutPaySuccess()
              }, retryDelay)

              return
            }

            self.cancelPayment(response)
            self.isPlaceOrderActionAllowed(false)
            self.retryOrderPlaceActionCount(0)
          })
      })
    },

    placeMagentoOrder: function () {
      const self = this
      fullScreenLoader.startLoader()

      return new Promise(function (resolve, reject) {
        let headers = { 'Content-Type': 'application/json' }
        let payload = self.getData()

        agreementsAssigner(payload)

        fetch(mageUrl.build(urlBuilder.createUrl('/revolut/payment-information', {})), {
          method: 'POST',
          headers: headers,
          body: JSON.stringify({ paymentMethod: payload }),
        })
          .then(response => {
            return response.json()
          })
          .then(response => {
            fullScreenLoader.stopLoader()

            if (response && response.success) {
              resolve(response)
            }

            reject(response)
          })
      })
    },

    retryingOrderPlace: function () {
      let retryOrderPlaceAction = this.retryOrderPlaceActionCount() + 1
      this.retryOrderPlaceActionCount(retryOrderPlaceAction)
    },

    cancelPayment: function (technicalError) {
      const self = this
      fullScreenLoader.startLoader()

      fetch(mageUrl.build(urlBuilder.createUrl('/revolut/order/cancel', {})), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          public_id: self.publicId(),
          cancel_reason: JSON.stringify(technicalError),
        }),
      }).then(response => {
        window.createRevolutPayments = null
        self.createRevolutForm(self.orderCanceledMsg)
        fullScreenLoader.stopLoader()
      })
    },

    logError: function (errorMsg) {
      fetch(mageUrl.build(urlBuilder.createUrl('/revolut/log', {})), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(errorMsg),
      })
    },

    handleValidate: function () {
      let self = this
      this.isPaymentTotalUpdateAllowed(true)
      return new Promise(function (resolve, reject) {
        if (!self.validate() || !self.customValidations()) {
          return resolve(false)
        }

        if (self.isPlaceOrderActionAllowed()) {
          $.when(setPaymentInformation(self.messageContainer, self.getData()))
            .done(function () {
              return resolve(true)
            })
            .fail(function (response) {
              return resolve(false)
            })

          return
        }

        fullScreenLoader.stopLoader()
        reject('Place Order action is not allowed.')
      })
    },

    handleCancel: function () {
      this.isPaymentTotalUpdateAllowed(true)
      fullScreenLoader.stopLoader()
    },
  })
})
