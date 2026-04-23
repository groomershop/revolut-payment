<?php

namespace Revolut\Payment\Api;

interface FastCheckoutManagementInterface
{
    /**
     * Creates card for Express checkout
     *
     * @api
     * @param String $revolutPublicId
     * @param String $productForm
     * @param bool $revolutPayFastCheckout
     * @return \Revolut\Payment\Api\Data\FastCheckoutResponseDataInterface
     */
    public function addToCart(String $revolutPublicId, String $productForm, bool $revolutPayFastCheckout = false);

    /**
     * Handle address validation webhook callbacks
     *
     * @api
     * @param String $orderId
     * @param \Revolut\Payment\Api\Data\AddressDataInterface $shippingAddress
     * @return \Revolut\Payment\Api\Data\FastCheckoutResponseDataInterface
     */
    public function addressValidationWebhook(
        String $orderId,
        \Revolut\Payment\Api\Data\AddressDataInterface $shippingAddress
    );

    /**
     * Loads shipping options for selected address
     *
     * @api
     * @param String $revolutPublicId
     * @param \Revolut\Payment\Api\Data\AddressDataInterface $addressData
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Revolut\Payment\Api\Data\FastCheckoutResponseDataInterface
     */
    public function loadShippingOptions(
        String $revolutPublicId,
        \Revolut\Payment\Api\Data\AddressDataInterface $addressData,
        ?\Magento\Quote\Api\Data\CartInterface $quote = null
    );
    
    /**
     * Sets selected shipping option
     *
     * @api
     * @param String $selectedCarrierId
     * @param String $revolutPublicId
     * @param \Revolut\Payment\Api\Data\AddressDataInterface $addressData
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Revolut\Payment\Api\Data\FastCheckoutResponseDataInterface
     */
    public function setShippingOption(
        String $selectedCarrierId,
        String $revolutPublicId,
        \Revolut\Payment\Api\Data\AddressDataInterface $addressData,
        ?\Magento\Quote\Api\Data\CartInterface $quote = null
    );
    
    /**
     * Validate Order order
     *
     * @api
     * @param String $publicId
     * @param \Revolut\Payment\Api\Data\ValidateOrderRequestDataInterface $address
     * @param String $carrierId
     * @return \Revolut\Payment\Api\Data\FastCheckoutResponseDataInterface
     */
    public function validateOrder(
        String $publicId,
        ?\Revolut\Payment\Api\Data\ValidateOrderRequestDataInterface $address = null,
        ?String $carrierId = null
    );
    
    /**
     * Get params
     *
     * @api
     * @param String $location
     * @return \Revolut\Payment\Api\Data\FastCheckoutParametersResponseDataInterface
     */
    public function getParams(String $location);
}
