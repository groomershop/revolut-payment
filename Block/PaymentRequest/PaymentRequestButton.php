<?php

namespace Revolut\Payment\Block\PaymentRequest;

use Revolut\Payment\Model\Source\Mode;
use Revolut\Payment\Model\Helper\ConstantValue;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Revolut\Payment\Gateway\Config\RevolutPaymentRequest\Config as RevolutPaymentRequestConfig;
use Revolut\Payment\Gateway\Config\Config as RevolutConfig;
use Revolut\Payment\Model\Helper\Api\RevolutApi;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver;

class PaymentRequestButton extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RevolutPaymentRequestConfig
     */
    protected $configHelper;
    
    /**
     * @var RevolutConfig
     */
    protected $revolutConfigHelper;

    /**
     * @var string
     */
    public $revolutSdkUrl;
    
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
        
    /**
     * @var UrlInterface
     */
    public $urlHelper;

    /**
     * @var Resolver
     */
    protected $localeResolver;

    /**
     * @var RevolutApi
     */
    private $revolutApi;
    
    /**
     * Button constructor.
     *
     * @param Template\Context $context
     * @param RevolutPaymentRequestConfig $configHelper
     * @param RevolutConfig $revolutConfigHelper
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlHelper
     * @param Resolver $localeResolver
     * @param RevolutApi $revolutApi
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        RevolutPaymentRequestConfig $configHelper,
        RevolutConfig $revolutConfigHelper,
        Registry $registry,
        StoreManagerInterface $storeManager,
        UrlInterface $urlHelper,
        Resolver $localeResolver,
        RevolutApi $revolutApi,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->configHelper = $configHelper;
        $this->revolutConfigHelper = $revolutConfigHelper;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->localeResolver = $localeResolver;
        $this->revolutApi = $revolutApi;
    }

     /**
      * Get RevolutPayPaymentMethodCode
      *
      * @return string
      */
    public function getRevolutPayPaymentMethodCode()
    {
        return \Revolut\Payment\Model\Ui\ConfigProvider::REVOLUT_PAY_CODE;
    }
    
    /**
     * Get RevolutPaymentRequestPaymentMethodCode
     *
     * @return string
     */
    public function getRevolutPaymentRequestPaymentMethodCode()
    {
        return \Revolut\Payment\Model\Ui\ConfigProvider::REVOLUT_PAYMENT_REQUEST_CODE;
    }
    
    /**
     * Get StoreCode
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
    
    /**
     * Get StoreBaseUrl
     *
     * @return string
     */
    public function getStoreBaseUrl()
    {
        return $this->urlHelper->getBaseUrl();
    }
    
    /**
     * Get RevolutPayRedirectUrl
     *
     * @return string
     */
    public function getRevolutPayRedirectUrl()
    {
        return $this->urlHelper->getUrl('revolut/process/payment');
    }
    
    /**
     * Get LocaleCode
     *
     * @return string
     */
    public function getLocaleCode()
    {
        $currentLocaleCode = $this->localeResolver->getLocale(); // en_EN
        $currentLocaleCode = strstr($currentLocaleCode, '_', true);

        if ($currentLocaleCode) {
            return $currentLocaleCode;
        }

        return '';
    }

    /**
     * Get ProductId
     *
     * @return int
     */
    public function getProductId()
    {
        $product = $this->registry->registry('product');
        if ($product) {
            return $product->getId();
        }
        return 0;
    }
    
    /**
     * Get ProductUrl
     *
     * @return string
     */
    public function getProductUrl()
    {
        $product = $this->registry->registry('product');
        if ($product) {
            return $product->getProductUrl();
        }
        return '';
    }
    
    /**
     * Get Cart PageUrl
     *
     * @return string
     */
    public function getCartPageUrl()
    {
        return $this->getUrl('checkout/cart', ['_secure' => true]);
    }

    /**
     * Get Revolut Sdk
     *
     * @return string
     */
    public function getRevolutSdk()
    {
        $mode = $this->revolutConfigHelper->getModeName($this->storeManager->getStore()->getId());
        return ConstantValue::REVOLUT_SDK_URLS[$mode];
    }

/**
     * Get Merchant Public Key
     *
     * @return mixed
     */
    public function getMerchantPublicKey()
    {
        return $this->revolutApi->getMerchantPublicKey($this->storeManager->getStore()->getId());
    }
}
