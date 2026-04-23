<?php

namespace Revolut\Payment\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Revolut\Payment\Api\Data\FastCheckoutResponseDataInterface;

class FastCheckoutResponseData extends AbstractExtensibleObject implements FastCheckoutResponseDataInterface
{
    /**
     * Get Success
     *
     * @return bool|mixed
     */
    public function getSuccess()
    {
        return $this->_get(self::KEY_SUCCESS);
    }
    
    /**
     * Set Success
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->setData(self::KEY_SUCCESS, $success);
        return $this;
    }
    
    /**
     * Get Valid
     *
     * @return bool
     */
    public function getValid()
    {
        return !empty($this->getDeliveryMethods());
    }

    /**
     * Get Currency
     *
     * @return string|mixed
     */
    public function getCurrency()
    {
        return $this->_get(self::KEY_CURRENCY);
    }
    
    /**
     * Set Currency
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->setData(self::KEY_CURRENCY, $currency);
        return $this;
    }

    /**
     * Get Message
     *
     * @return string|mixed
     */
    public function getMessage()
    {
        return $this->_get(self::KEY_MESSAGE);
    }
    
    /**
     * Set Message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::KEY_MESSAGE, $message);
        return $this;
    }

    /**
     * Get DeliveryMethods
     *
     * @return \Revolut\Payment\Api\Data\ShippingOptionDataInterface[]|mixed
     */
    public function getDeliveryMethods()
    {
        return $this->_get(self::KEY_SHIPPING_OPTIONS);
    }
    
    /**
     * Set DeliveryMethods
     *
     * @param \Revolut\Payment\Api\Data\ShippingOptionDataInterface[] $shippingOptions
     * @return $this
     */
    public function setDeliveryMethods($shippingOptions)
    {
        return $this->setData(self::KEY_SHIPPING_OPTIONS, $shippingOptions);
    }
    
    /**
     * Get Total
     *
     * @return \Revolut\Payment\Model\Data\TotalsData|mixed
     */
    public function getTotal()
    {
        return $this->_get(self::KEY_TOTAL);
    }
    
    /**
     * Set Total
     *
     * @param \Revolut\Payment\Model\Data\TotalsData $total
     * @return $this
     */
    public function setTotal($total)
    {
        return $this->setData(self::KEY_TOTAL, $total);
    }
}
