<?php

namespace Revolut\Payment\Block\OrderConfirmation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context ;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Locale\Resolver;

use Revolut\Payment\Model\Helper\Logger;
use Revolut\Payment\Model\Helper\ConstantValue;
use Revolut\Payment\Model\RevolutOrderFactory;
use Revolut\Payment\Model\Helper\Api\RevolutApi;
use Revolut\Payment\Gateway\Config\Config as RevolutConfig;
use \Revolut\Payment\Model\Ui\ConfigProvider;

class RewardBannerBlock extends Template
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RevolutConfig
     */
    protected $revolutConfigHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    
    /**
     * @var RevolutApi
     */
    public $revolutApi;
    
    /**
     * @var Session
     */
    public $checkoutSession;
    
    /**
     * @var RevolutOrderFactory
     */
    public $revolutOrderFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var Resolver
     */
    protected $localeResolver;

    /**
     * RewardBannerBlock constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context
     * @param RevolutConfig $revolutConfigHelper
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     * @param RevolutApi $revolutApi
     * @param RevolutOrderFactory $revolutOrderFactory
     * @param OrderFactory $orderFactory
     * @param Logger $logger
     * @param Resolver $localeResolver
     * @param array $data
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Context $context,
        RevolutConfig $revolutConfigHelper,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        RevolutApi $revolutApi,
        RevolutOrderFactory $revolutOrderFactory,
        OrderFactory $orderFactory,
        Logger $logger,
        Resolver $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->revolutConfigHelper = $revolutConfigHelper;
        $this->storeManager = $storeManager;
        $this->revolutApi = $revolutApi;
        $this->checkoutSession = $checkoutSession;
        $this->revolutOrderFactory = $revolutOrderFactory;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get OrderById
     *
     * @param int $orderId
     * @return false|OrderInterface
     */
    public function getOrderById($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
                return false;
        }
        return  $order;
    }
    
    /**
     * Get CmsBlock
     *
     * @param string $identifier
     * @return \Magento\Cms\Block\Block
     */
    public function getCmsBlock($identifier)
    {
        $cmsBlock = $this->_layout->createBlock(\Magento\Cms\Block\Block::class)
            ->setBlockId($identifier)
            ->toHtml();
        return $cmsBlock;
    }

    /**
     * Get RevolutBannerSdk
     *
     * @return string
     */
    public function getRevolutBannerSdk()
    {
        return $this->revolutConfigHelper->getBannerSdkUrl($this->storeManager->getStore()->getId());
    }
    
    /**
     * Get MerchantPublicKey
     *
     * @return string|mixed
     */
    public function getMerchantPublicKey()
    {
        return $this->revolutApi->getMerchantPublicKey($this->storeManager->getStore()->getId());
    }

    /**
     * Get LocaleCode
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->storeManager->getStore()->getLocale();
    }

    /**
     * Get StoreLocale
     *
     * @return string|false
     */
    public function getStoreLocale()
    {
        $currentLocaleCode = $this->localeResolver->getLocale(); // en_EN
        return strstr($currentLocaleCode, '_', true);
    }

    /**
     * Get OrderDetails
     *
     * @return string|false
     */
    public function getOrderDetails()
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode(); // @phpstan-ignore-line
           
            $billingAddress = $order->getBillingAddress();
            $phone = $billingAddress->getTelephone(); // @phpstan-ignore-line
            $email = $order->getCustomerEmail();
            $incrementOrderId = $order->getIncrementId();
    
            $revolutOrderFactory = $this->revolutOrderFactory->create();
            $revolutOrder = $revolutOrderFactory->load($incrementOrderId, 'increment_order_id'); // @phpstan-ignore-line
        
            return \json_encode([
                'RevolutPaymentRequestMethodCode' => ConfigProvider::REVOLUT_PAYMENT_REQUEST_CODE,
                'RevolutPayMethodCode' => ConfigProvider::REVOLUT_PAY_CODE,
                'RevolutCardMethodCode' => ConfigProvider::CODE,
                'currency' => $order->getOrderCurrencyCode(),
                'paymentMethodCode' => $paymentMethodCode,
                'orderPublicId' => $revolutOrder->getPublicId(),
                'phone' => $phone,
                'email' => $email,
                'merchantPublicKey' => $this->getMerchantPublicKey(),
                'revolutBannerSdk' => $this->getRevolutBannerSdk(),
                'locale' => $this->getStoreLocale(),
                'isRewardBannerActive' => $this->revolutConfigHelper->isGatewayBannerEnabled(
                    $this->storeManager->getStore()->getId()
                )
            ]);
        } catch (\Exception $e) {
            $this->logger->debug('RewardBanner::getOrderDetails - ' . $e->getMessage());
            return \json_encode([
                'isRewardBannerActive' => false
            ]);
        } catch (\Error $e) {
            $this->logger->debug('RewardBanner::getOrderDetails - ' . $e->getMessage());
            return \json_encode([
                'isRewardBannerActive' => false
            ]);
        }
    }
}
