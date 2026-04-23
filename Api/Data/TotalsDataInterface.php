<?php

namespace Revolut\Payment\Api\Data;

/**
 * Interface TotalsDataInterface
 * @api
 */
interface TotalsDataInterface
{
    public const KEY_AMOUNT = 'amount';

    /**
     * Get Amount
     *
     * @return int
     */
    public function getAmount();
    
    /**
     * Set Amount
     *
     * @param int|mixed $amount
     * @return $this
     */
    public function setAmount($amount);
}
