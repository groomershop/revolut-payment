<?php

namespace Revolut\Payment\Api;

use Magento\Quote\Api\Data\PaymentInterface;

interface OrderManagementInterface
{
    /**
     * Create Order
     *
     * @api
     * @return \Revolut\Payment\Api\Data\OrderManagementResponseDataInterface
     */
    public function create();

    /**
     * Cancel Order
     *
     * @api
     * @param String $publicId
     * @param String $cancelReason
     * @return \Revolut\Payment\Api\Data\OrderManagementResponseDataInterface
     */
    public function cancel(String $publicId, String $cancelReason);
    
    /**
     * Handle webhook callbacks
     *
     * @api
     * @param String $orderId
     * @param String $event
     * @return string[]
     */
    public function webhook(String $orderId, String $event);
    
    /**
     * Create Magento order
     *
     * @api
     * @param PaymentInterface $paymentMethod
     * @return \Revolut\Payment\Api\Data\OrderManagementResponseDataInterface
     */
    public function placeOrder(PaymentInterface $paymentMethod);
}
