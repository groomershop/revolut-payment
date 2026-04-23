<?php

namespace Revolut\Payment\Model;

use Exception;
use Throwable;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Magento\Framework\Model\Context;
use Revolut\Payment\Model\Helper\Logger;
use Magento\Framework\Model\AbstractModel;
use Revolut\Payment\Model\Helper\ConstantValue;
use Revolut\Payment\Model\Helper\Api\RevolutOrderApi;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Revolut\Payment\Gateway\Helper\AmountProvider;
use Magento\Framework\Exception\LocalizedException;

class RevolutOrder extends AbstractModel
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    
    /**
     * @var AmountProvider
     */
    private $amountProvider;

    /**
     * @var Session
     */
    protected $customerSession;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var array
     */
    public $revolutOrder = null;

    /**
     * @var RevolutOrderApi
     */
    protected $revolutOrderApi;

    /**
     * RevolutOrder constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Session $customerSession
     * @param RevolutOrderApi $revolutOrderApi
     * @param Logger $logger
     * @param AmountProvider $amountProvider
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $customerSession,
        RevolutOrderApi $revolutOrderApi,
        Logger $logger,
        AmountProvider $amountProvider,
        ProductRepositoryInterface $productRepository,
        ?AbstractResource $resource = null
    ) {
        $this->logger = $logger;
        $this->revolutOrderApi = $revolutOrderApi;
        $this->customerSession = $customerSession;
        $this->amountProvider = $amountProvider;
        $this->productRepository = $productRepository;
        parent::__construct($context, $registry, $resource);
    }

    /**
     * Construct
     */
    public function _construct()
    {
        $this->_init(\Revolut\Payment\Model\ResourceModel\RevolutOrder::class);
    }
    
    /**
     * Create order
     *
     * @param array $params
     * @param int $quoteId
     * @param int $customerId
     * @param int $storeId
     * @param bool $fastCheckout
     * @return RevolutOrder|null
     */
    public function create($params, $quoteId, $customerId, $storeId, $fastCheckout)
    {
        try {
            if (!empty($this->getRevolutOrderId())) {
                return $this->update($params, $fastCheckout);
            }

            $this->logger->debug('create new order: ' . $quoteId);

            $revolutOrder = $this->revolutOrderApi->create($params, $storeId);
            $this->setRevolutOrderId($revolutOrder['id']);
            $this->setIncrementOrderId('');
            $this->setQuoteId($quoteId);
            $this->setCustomerId($customerId);
            $this->setStoreId($storeId);
            $this->setIsFastCheckout((int)$fastCheckout);
            $this->setPublicId($revolutOrder['public_id']);
            $this->setCurrency($params['currency']);
            $this->setOrderAmount($params['amount']);
            $this->save();
            $this->revolutOrder = $revolutOrder;
            return $this;
        } catch (Exception $e) {
            $this->logger->debug('can not create order: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update order
     *
     * @param array $params
     * @param bool $fastCheckout
     * @return RevolutOrder|null
     */
    public function update($params, $fastCheckout)
    {
        try {
            $this->logger->debug('update order params: ' . json_encode($params));
            
            $this->logger->debug('update action: OrderId: ' .
            $this->getRevolutOrderId() . ' - StoreId: ' . $this->getStoreId());
            
            $revolutOrder = $this->revolutOrderApi->retrieve($this->getRevolutOrderId(), $this->getStoreId());
            
            if (empty($revolutOrder['state']) || $revolutOrder['state'] != 'PENDING') {
                throw new LocalizedException(__('Can not update order, orderId: ' . $this->getRevolutOrderId()));
            }

            $revolutOrder = $this->revolutOrderApi->update($this->getRevolutOrderId(), $params, $this->getStoreId());

            if (empty($revolutOrder['id'])) {
                throw new LocalizedException(__('Can not load order: ' .
                $this->getRevolutOrderId() . ' - storeId: ' . $this->getStoreId() .
                ' : ' . json_encode($revolutOrder)));
            }

            if ($this->getOrderAmount() != $params['amount'] ||
             $this->getCurrency() != $params['currency'] ||
              (int)$this->getIsFastCheckout() != (int)$fastCheckout
              ) {
                $this->setCurrency($params['currency']);
                $this->setOrderAmount($params['amount']);
                $this->setIsFastCheckout((int)$fastCheckout);
                $this->save();
            }

            $this->revolutOrder = $revolutOrder;
            return $this;
        } catch (Exception $e) {
            $this->logger->debug('can not update order: ' . $e->getMessage());
        }

        return null;
    }
    
    /**
     * Cancel order
     *
     * @param string $publicId
     * @return RevolutOrder
     */
    public function cancel($publicId)
    {
        try {
            $this->load($publicId, 'public_id'); // @phpstan-ignore-line

            if (empty($this->getRevolutOrderId())) {
                throw new LocalizedException(__('Cancel action: Can not load order with public_id: ' . $publicId));
            }

            $this->logger->debug('Cancel action: OrderId: ' .
            $this->getRevolutOrderId() . ' - StoreId: ' . $this->getStoreId());
            
            $this->revolutOrder = $this->revolutOrderApi->cancel($this->getRevolutOrderId(), $this->getStoreId());
            return $this;
        } catch (Exception $e) {
            $this->logger->debug('can not create order: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Refund order
     *
     * @param array $params
     * @return RevolutOrder
     */
    public function refund($params)
    {
        $this->load($params['publicId'], 'public_id');
        
        if (empty($this->getRevolutOrderId())) {
            throw new Exception('Refund action: Can not load order with public_id: ' . $params['publicId']);
        }

        $this->logger->debug('Refund action: OrderId: ' .
        $this->getRevolutOrderId() . ' - StoreId: ' . $this->getStoreId());

        $this->revolutOrder = $this->revolutOrderApi->refund(
            $this->getRevolutOrderId(),
            $params['amount'],
            $params['currency'],
            $this->getStoreId()
        );

        return $this;
    }

     /**
      * Capture order
      *
      * @param array $params
      * @return RevolutOrder
      */
    public function capture($params)
    {
        $this->load($params['publicId'], 'public_id');
        
        if (empty($this->getRevolutOrderId())) {
            throw new Exception('Capture action: Can not load order with public_id: ' . $params['publicId']);
        }

        $this->logger->debug('Capture action: OrderId: ' .
        $this->getRevolutOrderId() . ' - StoreId: ' . $this->getStoreId());

        $this->revolutOrder = $this->revolutOrderApi->capture(
            $this->getRevolutOrderId(),
            $params['amount'],
            $this->getStoreId()
        );
        return $this;
    }
    
     /**
      * Get Order model by key
      *
      * @param string $key
      * @param mixed $value
      * @return RevolutOrder
      */
    public function retrieveBy($key, $value)
    {
        $this->load($value, $key); // @phpstan-ignore-line
        
        if (empty($this->getRevolutOrderId())) {
            throw new LocalizedException(__('RetrieveBy action: Can not load order with ' . $key . ': ' . $value));
        }

        $this->logger->debug('RetrieveBy action: OrderId: ' .
        $this->getRevolutOrderId() . ' - StoreId: ' . $this->getStoreId());

        $this->revolutOrder = $this->revolutOrderApi->retrieve(
            $this->getRevolutOrderId(),
            $this->getStoreId()
        );

        return $this;
    }
    
     /**
      * Get Order model
      *
      * @return RevolutOrder|null
      */
    public function retrieve()
    {
        if (empty($this->getRevolutOrderId())) {
            return null;
        }
        $this->logger->debug('Retrieve action: OrderId: ' .
        $this->getRevolutOrderId() . ' - StoreId: ' . $this->getStoreId());

        $this->revolutOrder = $this->revolutOrderApi->retrieve(
            $this->getRevolutOrderId(),
            $this->getStoreId()
        );

        return $this;
    }
    
     /**
      * Update merchant order id.
      *
      * @param string $magentoOrderId
      * @return RevolutOrder|null
      */
    public function updateMerchantOrderId($magentoOrderId)
    {
        if (empty($this->getRevolutOrderId())) {
            return null;
        }

        $this->setIncrementOrderId($magentoOrderId);
        $this->save();

        $this->logger->debug('UpdateMerchantOrderId action: OrderId: ' .
        $this->getRevolutOrderId() . ' - StoreId: ' . $this->getStoreId());

        $this->revolutOrder = $this->revolutOrderApi->updateMerchantOrderId(
            $this->getRevolutOrderId(),
            $magentoOrderId,
            $this->getStoreId()
        );

        return $this;
    }
 
    /**
     * Save line items.
     *
     * @param object $magentoOrder
     * @return RevolutOrder|null
     */
    public function saveLineItems($magentoOrder)
    {
        if (empty($this->getRevolutOrderId())) {
            return null;
        }

        try {
            $orderUpdate = [];
            $lineItems = $this->getLineItems($magentoOrder);
            $shipping = $this->getShippingDetails($magentoOrder);

            if (!empty($lineItems)) {
                $orderUpdate['line_items'] = $lineItems;
            }
            
            if (!empty($shipping)) {
                $orderUpdate['shipping'] = $shipping;
            }

            if (empty($orderUpdate)) {
                return null;
            }
   
            $this->revolutOrder = $this->revolutOrderApi->updateLineItems(
                $this->getRevolutOrderId(),
                $orderUpdate,
                $this->getStoreId()
            );
        } catch (Throwable $e) {
            $this->logger->debug('saveLineItems error: ' .  $e->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Save Shipping info.
     *
     * @param object $magentoOrder
     * @param string $trackingNumber
     * @param string $carrierTitle
     * @return RevolutOrder|null
     */
    public function saveShippingInfo($magentoOrder, $trackingNumber, $carrierTitle)
    {
        if (empty($this->getRevolutOrderId())) {
            return null;
        }

        try {
            $orderUpdate = [
                'shipping' => $this->getShippingDetails($magentoOrder, $trackingNumber, $carrierTitle),
            ];

            $this->revolutOrder = $this->revolutOrderApi->updateLineItems(
                $this->getRevolutOrderId(),
                $orderUpdate,
                $this->getStoreId()
            );
        } catch (\Exception $e) {
            $this->logger->debug('saveShippingInfo error: ' .  $e->getMessage());
        }
        
        return $this;
    }

    /**
     * Collect Order line items.
     *
     * @param object $order
     * @return array
     */
    public function getLineItems($order)
    {
        try {
            $lineItems = [];
            $currency = $order->getOrderCurrencyCode();
            
            foreach ($order->getAllVisibleItems() as $item) {
                $productId = $item->getProductId();
                $product = $this->productRepository->getById($productId);
                $productName = $product->getName();
                $productType = $product->getTypeId() === 'virtual' ? 'service' : 'physical';
                $quantity = $item->getQtyOrdered();
                $unitPriceAmount = $this->amountProvider->convert($item->getPriceInclTax(), $currency);
                $totalAmount = $this->amountProvider->convert($item->getRowTotalInclTax(), $currency);
                
                $description = $product->getDescription();
                if (! empty($description)) {
                    $description = substr($product->getDescription(), 0, 1024);
                }

                $productUrl = $product->getProductUrl();
               
                $discounts = [];
                $discountAmount = $this->amountProvider->convert($item->getDiscountAmount(), $currency);

                if ($discountAmount > 0) {
                    $discounts[] = [
                        'name' => 'Discount',
                        'amount' => $discountAmount
                    ];
                }

                $taxes = [];
                $taxAmount = $this->amountProvider->convert($item->getTaxAmount(), $currency);

                if ($taxAmount > 0) {
                    $taxes[] = [
                        'name' => 'Tax',
                        'amount' => $taxAmount
                    ];
                }

                $lineItems[] = [
                    'name' => $productName,
                    'type' => $productType,
                    'unit_price_amount' => $unitPriceAmount,
                    'total_amount' => $totalAmount,
                    'quantity' => [
                        'value' => $quantity
                    ],
                    'external_id' => $productId,
                    'discounts' => $discounts,
                    'taxes' => $taxes,
                    'description' => $description,
                    'url' => $productUrl
                ];
            }

            return $lineItems;
        } catch (Throwable $e) {
            $this->logger->debug('getLineItems error: ' .  $e->getMessage());
        }

        return [];
    }
    
    /**
     * Collect Shipping info.
     *
     * @param object $order
     * @param string $trackingNumber
     * @param string $carrierTitle
     * @return array
     */
    public function getShippingDetails($order, $trackingNumber = '', $carrierTitle = '')
    {
        try {
            $shippingAddress = $order->getShippingAddress();

            if (empty($shippingAddress)) {
                return [];
            }

            $shipping = [
                'address' => [
                    'street_line_1' => $shippingAddress->getStreetLine(1),
                    'street_line_2' => $shippingAddress->getStreetLine(2),
                    'region' => $shippingAddress->getRegion(),
                    'city' => $shippingAddress->getCity(),
                    'country_code' => $shippingAddress->getCountryId(),
                    'postcode' => $shippingAddress->getPostcode()
                ],
                'contact' => [
                    'name' => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
                    'email' => $order->getCustomerEmail(),
                    'phone' => $shippingAddress->getTelephone()
                ]
            ];

            if (!empty($trackingNumber) && !empty($carrierTitle)) {
                $shipping['shipments'] = [[
                    'tracking_number' => $trackingNumber,
                    'shipping_company_name' => $carrierTitle,
                ]];
            }

            return $shipping;
        } catch (Throwable $e) {
            $this->logger->debug('getShippingDetails error: ' .  $e->getMessage());
        }

        return [];
    }
    
    /**
     * Check Payment completed
     *
     * @return bool
     */
    public function isPaymentCompleted()
    {
        if (empty($this->getRevolutOrderId())) {
            return false;
        }

        $this->retrieve();
        
        $this->logger->debug('isPaymentCompleted action: OrderId: ' .
        $this->revolutOrder['id'] . ' state: ' . $this->revolutOrder['state']);

        return $this->revolutOrder['state'] == ConstantValue::ORDER_COMPLETED;
    }
    
    /**
     * Check if payment type is Card
     *
     * @return bool
     */
    public function isCardPayment()
    {
        if (empty($this->getRevolutOrderId())) {
            return false;
        }

        $this->retrieve();

        return isset($this->revolutOrder['payments'][0]['payment_method']['type']) &&
        (strtoupper($this->revolutOrder['payments'][0]['payment_method']['type'])
        == ConstantValue::CARD_PAYMENT_METHOD_TYPE);
    }
    
    /**
     * Check if payment type is Card
     *
     * @return bool
     */
    public function isRevolutPayPayment()
    {
        if (empty($this->getRevolutOrderId())) {
            return false;
        }

        $this->retrieve();

        return isset($this->revolutOrder['payments'][0]['payment_method']['type']) &&
            (strtoupper($this->revolutOrder['payments'][0]['payment_method']['type'])
            == ConstantValue::REVOLUT_PAY_PAYMENT_METHOD_TYPE);
    }
    
    /**
     * Check Can Capture the order
     *
     * @return bool
     */
    public function canCapture()
    {
        if (empty($this->getRevolutOrderId())) {
            return false;
        }

        $this->retrieve();
        $this->logger->debug('canCapture action: OrderId: ' .
        $this->revolutOrder['id'] . ' state: ' . $this->revolutOrder['state']);

        return $this->revolutOrder['state'] == ConstantValue::ORDER_AUTHORISED;
    }
}
