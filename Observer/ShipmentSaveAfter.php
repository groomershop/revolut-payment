<?php

namespace Revolut\Payment\Observer;

use Magento\Framework\Event\Observer;
use Revolut\Payment\Model\RevolutOrder;
use Revolut\Payment\Model\Helper\Logger;
use Magento\Framework\Event\ObserverInterface;

class ShipmentSaveAfter implements ObserverInterface
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var RevolutOrder
     */
    protected $revolutOrder;

    /**
     * ShipmentSaveAfter constructor.
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
     * Execute observer
     *
     * @param Observer $observer
     * @return void|null
     */
    public function execute(Observer $observer)
    {
        try {
            $track = $observer->getEvent()->getTrack();
            $shipment = $track->getShipment();
            $order = $shipment->getOrder();
            $incrementId = $order->getIncrementId();
            $trackingNumber = $track->getTrackNumber();
            $carrierTitle = !empty($track->getTitle()) ? $track->getTitle(): strtoupper($track->getCarrierCode());
            $this->revolutOrder->retrieveBy('increment_order_id', $incrementId)
            ->saveShippingInfo($order, $trackingNumber, $carrierTitle);
        } catch (\Exception $e) {
            $this->logger->debug('ShipmentSaveAfter error: ' .  $e->getMessage());
        }
    }
}
