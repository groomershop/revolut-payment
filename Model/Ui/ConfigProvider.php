<?php

namespace Revolut\Payment\Model\Ui;

use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\App\Request\Http;
use Revolut\Payment\Gateway\Config\Config;
use Revolut\Payment\Gateway\Config\RevolutPaymentRequest\Config as PrConfig;
use Revolut\Payment\Gateway\Config\RevolutPay\Config as RevConfig;
use Revolut\Payment\Model\Helper\Api\RevolutApi;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;

class ConfigProvider implements ConfigProviderInterface
{
    public const GATEWAY_CODE = 'revolut';
    public const CODE = 'revolut_card';
    public const REVOLUT_PAY_CODE = 'revolut_pay';
    public const REVOLUT_PAYMENT_REQUEST_CODE = 'revolut_payment_request';

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var RevolutApi
     */
    private $revolutApi;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var PrConfig
     */
    private $prConfig;

    /**
     * @var RevConfig
     */
    private $revConfig;
    
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
        
    /**
     * @var Resolver
     */
    protected $localeResolver;

    /**
     * @param Session $checkoutSession
     * @param RevolutApi $revolutApi
     * @param Config $config
     * @param PrConfig $prConfig
     * @param RevConfig $revConfig
     * @param Http $request
     * @param Resolver $localeResolver
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $checkoutSession,
        RevolutApi $revolutApi,
        Config $config,
        PrConfig $prConfig,
        RevConfig $revConfig,
        Http $request,
        Resolver $localeResolver,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->revolutApi = $revolutApi;
        $this->config = $config;
        $this->prConfig = $prConfig;
        $this->revConfig = $revConfig;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $quote = $this->checkoutSession->getQuote();
        $amount = $quote->getGrandTotal();
        $currency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $availableCardBrands = $this->revolutApi->getAvailableCardBrands($storeId, $currency, $amount);

        return [
            'payment' => [
                self::GATEWAY_CODE => [
                    'mode' => $this->config->getModeName($storeId),
                    'originUrl' => $this->getStoreDomain(),
                    'locale' => $this->getStoreLocale(),
                    'revolutSdk' => $this->config->getSdkUrl($storeId),
'prButtonStyle' => $this->prConfig->getPaymentRequestButtonStyleConfigs($storeId),
                    'redirectUrl' => $this->urlBuilder->getUrl(
                        'revolut/process/payment',
                        ['_secure' => $this->request->isSecure()]
                    ),
                    'revButtonStyle' => $this->revConfig->getRevolutPayButtonStyleConfigs($storeId),
                    'checkoutUrl' => $this->urlBuilder->getUrl(
                        'checkout',
                        ['_secure' => $this->request->isSecure()]
                    ) . '#payment',
                    'cardholderNameField' => $this->config->isCardholderNameFieldActive($storeId),
                    'publicKey' => $this->revolutApi->getMerchantPublicKey($storeId),
                    'availableCardBrands' => $availableCardBrands,
                    'isCheckoutPageActivated' => $this->revConfig->isCheckoutPageActivated($storeId),
                ],
            ]
        ];
    }

    /**
     * Get StoreLocale
     *
     * @return string|false store locale
     */
    public function getStoreLocale()
    {
        $currentLocaleCode = $this->localeResolver->getLocale(); // en_EN
        return strstr($currentLocaleCode, '_', true);
    }
    
    /**
     * Get StoreDomain
     *
     * @return string|null store domain
     */
    public function getStoreDomain()
    {
        $mageDomain = $this->storeManager->getStore()->getBaseUrl();
        
        if (class_exists(\Laminas\Uri\Http::class)) {
            $uri = new \Laminas\Uri\Http($mageDomain);
        } elseif (class_exists(\Zend\Uri\Http::class)) {
            $uri = new \Zend\Uri\Http($mageDomain);
        } else {
            return "";
        }

        return $uri->getHost(); // @phpstan-ignore-line
    }
}
