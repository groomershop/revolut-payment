<?php

namespace Revolut\Payment\Api\Data;

/**
 * Interface ValidateOrderRequestDataInterface
 * @api
 */
interface ValidateOrderRequestDataInterface
{

    public const KEY_ID = 'id';

    public const KEY_EMAIL = 'email';

    public const KEY_SHIPPING_ADDRESS = 'shipping_address';
   
    public const KEY_BILLING_ADDRESS = 'billing_address';
    
    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail();
    
    /**
     * Set Email
     *
     * @param String|mixed $email
     * @return $this
     */
    public function setEmail($email);
    
    /**
     * Get Id
     *
     * @return string
     */
    public function getId();
    
    /**
     * Set Id
     *
     * @param String|mixed $id
     * @return $this
     */
    public function setId($id);
    
    /**
     * Get ShippingAddress
     *
     * @return \Revolut\Payment\Api\Data\AddressDataInterface
     */
    public function getShippingAddress();
    
    /**
     * Set ShippingAddress
     *
     * @param \Revolut\Payment\Api\Data\AddressDataInterface|mixed $address
     * @return $this
     */
    public function setShippingAddress($address);
    
    /**
     * Get BillingAddress
     *
     * @return \Revolut\Payment\Api\Data\AddressDataInterface
     */
    public function getBillingAddress();
    
    /**
     * Set BillingAddress
     *
     * @param \Revolut\Payment\Api\Data\AddressDataInterface|mixed $address
     * @return $this
     */
    public function setBillingAddress($address);
}
