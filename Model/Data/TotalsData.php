<?php

namespace Revolut\Payment\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Revolut\Payment\Api\Data\TotalsDataInterface;

class TotalsData extends AbstractExtensibleObject implements TotalsDataInterface
{
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
     * @param int $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->setData(self::KEY_AMOUNT, $amount);
    }
}
