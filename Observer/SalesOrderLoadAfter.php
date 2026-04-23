<?php
namespace Revolut\Payment\Observer;

use Magento\Framework\Event\Observer;
use Revolut\Payment\Model\RevolutOrder;
use Revolut\Payment\Model\Helper\ConstantValue;
use Revolut\Payment\Model\Ui\ConfigProvider;
use Revolut\Payment\Model\Helper\Logger;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Throwable;

class SalesOrderLoadAfter implements ObserverInterface
{
    /**
     * @var RevolutOrder
     */
    protected $revolutOrder;
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * SalesOrderLoadAfter constructor.
     *
     * @param RevolutOrder $revolutOrder
     * @param Logger $logger
     */
    public function __construct(
        RevolutOrder $revolutOrder,
        Logger $logger
    ) {
        
        $this->revolutOrder = $revolutOrder;
        $this->logger = $logger;
    }

    /**
     * SalesOrderLoadAfter execute.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            if (! ($order instanceof Order)) {
                return;
            }
    
            if ($order->getStatus() !== ConstantValue::MAGENTO_PAYMENT_REVIEW_STATUS) {
                return;
            }
    
            $payment = $order->getPayment();
            if (! ($payment instanceof Payment)) {
                return;
            }
    
            $paymentMethod = $payment->getMethod();
    
            if (!in_array($paymentMethod, [ConfigProvider::CODE,
            ConfigProvider::REVOLUT_PAY_CODE,
            ConfigProvider::REVOLUT_PAYMENT_REQUEST_CODE])) {
                return;
            }
    
            $publicId = $payment->getAdditionalInformation('publicId');
            
            if (! $publicId) {
                return;
            }
    
            $revolutOrder = $this->revolutOrder->retrieveBy('public_id', $publicId);
            if ($revolutOrder->isPaymentCompleted()) {
                $payment->accept();
                $order->save();
            }
        } catch (Throwable $e) {
            $this->logger->debug("SalesOrderLoadAfter Error : " . $e->getMessage());
        }
    }
}
