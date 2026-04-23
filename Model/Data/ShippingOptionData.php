<?php

namespace Revolut\Payment\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Revolut\Payment\Api\Data\ShippingOptionDataInterface;

class ShippingOptionData extends AbstractExtensibleObject implements ShippingOptionDataInterface
{
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
        return $this->setData(self::KEY_ID, $id);
    }

    /**
     * Get Label
     *
     * @return string|mixed
     */
    public function getLabel()
    {
        return $this->_get(self::KEY_LABEL);
    }
    
    /**
     * Set Label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    /**
     * Get Detail
     *
     * @return string|mixed
     */
    public function getDetail()
    {
        return $this->_get(self::KEY_DETAIL);
    }
    
    /**
     * Set Detail
     *
     * @param string $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        return $this->setData(self::KEY_DETAIL, $detail);
    }
    
    /**
     * Get Amount
     *
     * @return int|mixed
     */
    public function getAmount()
    {
        return $this->_get(self::KEY_AMOUNT);
    }
    
    /**
     * Set Amount
     *
     * @param string $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->setData(self::KEY_AMOUNT, $amount);
    }
}
