<?php

namespace Revolut\Payment\Model\Helper\Api;

use Revolut\Payment\Gateway\Helper\AmountProvider;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\App\Helper\Context;
use Revolut\Payment\Model\Helper\ConstantValue;
use Revolut\Payment\Gateway\Config\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Revolut\Payment\Model\Helper\Logger;
use Magento\Framework\Exception\LocalizedException;

class RevolutApi extends AbstractHelper
{
    /**
     * Revolut Api Version
     *
     * @var string
     */
    public $apiVersion = '2024-09-01';

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;
    
    /**
     * @var AmountProvider
     */
    protected $amountProvider;

    /**
     * RevolutApi constructor.
     *
     * @param Config $config
     * @param Data $jsonHelper
     * @param Context $context
     * @param Logger $logger
     * @param AmountProvider $amountProvider
     * @param ModuleListInterface $moduleList
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Config $config,
        Data $jsonHelper,
        Context $context,
        Logger $logger,
        AmountProvider $amountProvider,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->moduleList = $moduleList;
        $this->amountProvider = $amountProvider;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Http request
     *
     * @param string $url
     * @param int $storeId
     * @param string $method
     * @param array|null $content
     * @return array
     * @throws \Exception
     */
    public function request($url, $storeId, $method, $content = null)
    {
        $httpHeaders = class_exists(\Laminas\Http\Headers::class) ?
        new \Laminas\Http\Headers() : new \Zend\Http\Headers(); // @phpstan-ignore-line

        $setup_version =  $this->moduleList->getOne( // @phpstan-ignore-line
            ConstantValue::MODULE_NAME
        )['setup_version'];
        $apiKey = strpos($url, 'public/') ? $this->getMerchantPublicKey($storeId) : $this->config->getApiKey($storeId);
        $httpHeaders->addHeaders( // @phpstan-ignore-line
            array_merge(
                [
                "Authorization" => "Bearer " . $apiKey,
                'Revolut-Api-Version' => $this->apiVersion,
                "User-Agent" => 'Revolut Payment Gateway/' . $setup_version
                . ' Magento/' . $this->productMetadata->getVersion()
                . ' PHP/' . PHP_VERSION,
                "Content-Type" => "application/json; charset=utf-8"
                ]
            )
        );

        $content = $content ? $this->jsonHelper->jsonEncode($content) : $content;

        $request = class_exists(\Laminas\Http\Request::class) ?
        new \Laminas\Http\Request() : new \Zend\Http\Request(); // @phpstan-ignore-line

        $request->setHeaders($httpHeaders); // @phpstan-ignore-line
        $request->setContent($content); // @phpstan-ignore-line
        $request->setUri($url); // @phpstan-ignore-line
        $request->setMethod($method); // @phpstan-ignore-line

        $client = class_exists(\Laminas\Http\Client::class) ?
        new \Laminas\Http\Client() : new \Zend\Http\Client(); // @phpstan-ignore-line

        $options = [
            'adapter' => class_exists(\Laminas\Http\Client\Adapter\Curl::class) ?
            \Laminas\Http\Client\Adapter\Curl::class :
            \Zend\Http\Client\Adapter\Curl::class, // @phpstan-ignore-line
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 60
        ];

        $client->setOptions($options); // @phpstan-ignore-line

        try {
            $response = $client->send($request); // @phpstan-ignore-line
            if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500 && 'GET' !== $method) {
                throw new LocalizedException(__("Something went wrong: $method $url\n" . $response->getBody()));
            }
            return $this->json($response->getBody());
        } catch (\Exception $e) {
            $this->logger->debug("API error: " . $e->getMessage());
            throw new LocalizedException(__("API error: " . $e->getMessage()));
        }
    }

    /**
     * Post request
     *
     * @param  string $url
     * @param  int $storeId
     * @param  array|null $content
     * @return array
     */
    public function post($url, $storeId, $content = null)
    {
        return $this->request($url, $storeId, 'POST', $content);
    }
    
    /**
     * Get request
     *
     * @param  string $url
     * @param  int $storeId
     * @return array
     */
    public function get($url, $storeId)
    {
        return $this->request($url, $storeId, 'GET');
    }
    
    /**
     * Patch request
     *
     * @param  string $url
     * @param  int $storeId
     * @param  array|null $content
     * @return array
     */
    public function patch($url, $storeId, $content = null)
    {
        return $this->request($url, $storeId, 'PATCH', $content);
    }
    
    /**
     * Put request
     *
     * @param  string     $url
     * @param  int        $storeId
     * @param  array|null $content
     * @return array
     */
    public function put($url, $storeId, $content = null)
    {
        return $this->request($url, $storeId, 'PUT', $content);
    }
    
    /**
     * Delete request
     *
     * @param  string $url
     * @param  int $storeId
     * @return array
     */
    public function delete($url, $storeId)
    {
        return $this->request($url, $storeId, 'DELETE');
    }

    /**
     * Json decode
     *
     * @param  mixed $response
     * @return array
     */
    public function json($response)
    {
        return (array)$this->jsonHelper->jsonDecode($response); // @phpstan-ignore-line
    }
    
    /**
     * Get MerchantPublicKey
     *
     * @param  int $storeId
     * @return string|mixed
     */
    public function getMerchantPublicKey($storeId)
    {
        $publicKeyUrl = $this->config->getApiUrl($storeId) .
        ConstantValue::API . '/' .
        ConstantValue::ENDPOINT_PUBLIC_KEY;

        $response = $this->get($publicKeyUrl, $storeId);
        $publicKey = isset($response['public_key']) ?  (string)$response['public_key'] : '';
        return $publicKey;
    }
    
    /**
     * Get AvailableCardBrands
     *
     * @param int $storeId
     * @param string $currency
     * @param int|mixed $amount
     * @return array
     */
    public function getAvailableCardBrands($storeId, $currency, $amount)
    {
        $availableCardBrands = $this->config->getAvailableCardBrands($storeId);

        if ($availableCardBrands) {
            $availableCardBrands = $this->json($availableCardBrands);
        }
        
        if ($availableCardBrands) {
            return $availableCardBrands;
        }

        $response = $this->get(
            $this->config->getApiUrl($storeId) .
            ConstantValue::API . '/' .
            ConstantValue::ENDPOINT_AVAILABLE_PAYMENT_METHODS .
            '?amount=' . $amount .'&currency=' . $currency,
            $storeId
        );

        $availableCardBrands = isset($response['available_card_brands']) ? $response['available_card_brands'] : [];
        
        if (!$availableCardBrands) {
            return [];
        }

        $this->config->saveAvailableCardBrands($this->jsonHelper->jsonEncode($availableCardBrands), $storeId);
        return $availableCardBrands;
    }

    /**
     * Register ApplePayDomain
     *
     * @param string $domain
     * @param int $storeId
     * @return array
     */
    public function registerApplePayDomain($domain, $storeId)
    {
        $url = $this->config->getApiUrl($storeId) . '/api/apple-pay/domains/register';
        return $this->post($url, $storeId, ["domain" => $domain]);
    }
    
    /**
     * Get Locations
     *
     * @param int $storeId
     * @return array
     */
    public function getLocations($storeId)
    {
        $url = $this->config->getApiUrl($storeId) . ConstantValue::API . '/locations';
        return $this->get($url, $storeId);
    }
    
    /**
     * Create Location
     *
     * @param int $storeId
     * @param array $location
     * @return array
     */
    public function createLocation($storeId, $location)
    {
        $url = $this->config->getApiUrl($storeId) . ConstantValue::API . '/locations';
        return $this->post($url, $storeId, $location);
    }
    
    /**
     * Register AddressValidationWebhook
     *
     * @param int $storeId
     * @param array $webhook
     * @return array
     */
    public function registerAddressValidationWebhook($storeId, $webhook)
    {
        $url = $this->config->getApiUrl($storeId) . ConstantValue::API . '/synchronous-webhooks';
        return $this->post($url, $storeId, $webhook);
    }
}
