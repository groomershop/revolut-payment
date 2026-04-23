<?php

namespace Revolut\Payment\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Revolut\Payment\Api\Data\ValidateOrderRequestDataInterface;

class ValidateOrderRequestData extends AbstractExtensibleObject implements ValidateOrderRequestDataInterface
{
    /**
     * Get Email
     *
     * @return string|mixed
     */
    public function getEmail()
    {
        return $this->_get(self::KEY_EMAIL);
    }
    
    /**
     * Set Email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->setData(self::KEY_EMAIL, $email);
        return $this;
    }

    /**
     * Get Id
     *
     * @return string|mixed
     */
    public function getId()
    {
        return $this->_get(self::KEY_ID);
    }
    
    /**
     * Set Id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->setData(self::KEY_ID, $id);
        return $this;
    }

    /**
     * Get ShippingAddress
     *
     * @return \Revolut\Payment\Api\Data\AddressDataInterface|mixed
     */
    public function getShippingAddress()
    {
        return $this->_get(self::KEY_SHIPPING_ADDRESS);
    }
    
    /**
     * Set ShippingAddress
     *
     * @param \Revolut\Payment\Api\Data\AddressDataInterface $address
     * @return $this
     */
    public function setShippingAddress($address)
    {
        $this->setData(self::KEY_SHIPPING_ADDRESS, $address);
        return $this;
    }
    
    /**
     * Get BillingAddress
     *
     * @return \Revolut\Payment\Api\Data\AddressDataInterface|mixed
     */
    public function getBillingAddress()
    {
        return $this->_get(self::KEY_BILLING_ADDRESS);
    }
    
    /**
     * Set BillingAddress
     *
     * @param \Revolut\Payment\Api\Data\AddressDataInterface $address
     * @return $this
     */
    public function setBillingAddress($address)
    {
        $this->setData(self::KEY_BILLING_ADDRESS, $address);
        return $this;
    }
}
