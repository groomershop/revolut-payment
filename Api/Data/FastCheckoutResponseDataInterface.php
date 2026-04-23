<?php

namespace Revolut\Payment\Api\Data;

/**
 * Interface FastCheckoutResponseDataInterface
 * @api
 */
interface FastCheckoutResponseDataInterface
{

    public const KEY_SUCCESS = 'success';

    public const KEY_VALID = 'valid';
   
    public const KEY_CURRENCY = 'currency';

    public const KEY_MESSAGE = 'message';
    
    public const KEY_SHIPPING_OPTIONS = 'delivery_methods';
    
    public const KEY_TOTAL = 'total';
    
    /**
     * Get Success
     *
     * @return bool
     */
    public function getSuccess();
    
    /**
     * Set Success
     *
     * @param bool|mixed $success
     * @return $this
     */
    public function setSuccess($success);
    
    /**
     * Get Valid
     *
     * @return bool
     */
    public function getValid();
    
    /**
     * Get Currency
     *
     * @return bool
     */
    public function getCurrency();
    
    /**
     * Set Currency
     *
     * @param string|mixed $currency
     * @return $this
     */
    public function setCurrency($currency);
    
    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage();
    
    /**
     * Set Message
     *
     * @param string|mixed $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get DeliveryMethods
     *
     * @return \Revolut\Payment\Api\Data\ShippingOptionDataInterface[]
     */
    public function getDeliveryMethods();
    
    /**
     * Set DeliveryMethods
     *
     * @param \Revolut\Payment\Api\Data\ShippingOptionDataInterface[]|mixed $shippingOptions
     * @return $this
     */
    public function setDeliveryMethods($shippingOptions);
    
    /**
     * Get Total
     *
     * @return \Revolut\Payment\Api\Data\TotalsDataInterface
     */
    public function getTotal();
    
    /**
     * Set Total
     *
     * @param \Revolut\Payment\Api\Data\TotalsDataInterface|mixed $total
     * @return $this
     */
    public function setTotal($total);
}
