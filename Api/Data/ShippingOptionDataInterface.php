<?php

namespace Revolut\Payment\Api\Data;

/**
 * Interface ShippingOptionDataInterface
 * @api
 */
interface ShippingOptionDataInterface
{
    public const KEY_ID = 'id';
    
    public const KEY_LABEL = 'label';
    
    public const KEY_DETAIL = 'detail';

    public const KEY_AMOUNT = 'amount';

    /**
     * Get Id
     *
     * @return string
     */
    public function getId();
    
    /**
     * Set Id
     *
     * @param string|mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Label
     *
     * @return string
     */
    public function getLabel();
    
    /**
     * Set Label
     *
     * @param string|mixed $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get Detail
     *
     * @return string
     */
    public function getDetail();
    
    /**
     * Set Detail
     *
     * @param string|mixed $detail
     * @return $this
     */
    public function setDetail($detail);
    
    /**
     * Get Amount
     *
     * @return int
     */
    public function getAmount();
    
    /**
     * Set Amount
     *
     * @param string|mixed $amount
     * @return $this
     */
    public function setAmount($amount);
}
