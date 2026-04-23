<?php


namespace Revolut\Payment\Controller\Adminhtml\ApplePay;

use Magento\Backend\App\Action;
use Magento\Framework\UrlInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as DriverInterface;
use Magento\Framework\Exception\LocalizedException;


use Revolut\Payment\Model\Helper\ConstantValue;
use Revolut\Payment\Model\Helper\Api\RevolutApi;
use Revolut\Payment\Model\Helper\Logger;
use Revolut\Payment\Gateway\Config\Config;

class Setup extends Action
{
    /**
     * @var DirectoryList
     */
    protected $directory;
    
    /**
     * @var DriverInterface
     */
    protected $fileSystem;
    
    /**
     * @var Data
     */
    protected $json;

    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var RevolutApi
     */
    protected $revolutApi;
    
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var UrlInterface
     */
    protected $urlHelper;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $json
     * @param Config $config
     * @param Logger $logger
     * @param UrlInterface $urlHelper
     * @param DirectoryList $directory
     * @param StoreManagerInterface $storeManager
     * @param RevolutApi $revolutApi
     * @param DriverInterface $fileSystem
     */
    public function __construct(
        Context $context,
        Data $json,
        Config $config,
        Logger $logger,
        UrlInterface $urlHelper,
        DirectoryList $directory,
        StoreManagerInterface $storeManager,
        RevolutApi $revolutApi,
        DriverInterface $fileSystem
    ) {
        $this->json = $json;
        $this->logger = $logger;
        $this->urlHelper = $urlHelper;
        $this->directory = $directory;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->revolutApi = $revolutApi;
        $this->fileSystem = $fileSystem;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $stores = $this->storeManager->getStores();
            $domainList = [];
            
            foreach ($stores as $store) {
                if (empty($store->getId()) || empty($store->getBaseUrl())) {
                    continue;
                }
                
                if (class_exists(\Laminas\Uri\Http::class)) {
                    $uri = new \Laminas\Uri\Http($store->getBaseUrl());
                    $storeDomain = $uri->getHost();
                } elseif (class_exists(\Zend\Uri\Http::class)) {
                    $uri = new \Zend\Uri\Http($store->getBaseUrl());
                    $storeDomain = $uri->getHost();
                } else {
                    $storeDomain = "";
                }

                if (empty($storeDomain)) {
                    continue;
                }

                $domainList[$storeDomain] = $this->applePayDomainSetup($storeDomain, $store->getId());
            }

            return $this->jsonResponse([
                'success' => true,
                'result' => array_values($domainList),
            ]);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return $this->jsonResponse([
                ['success' => false,
                'errorMsg' =>'Something went wrong. ' . $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * ApplePay DomainSetup
     *
     * @param  string $storeDomain
     * @param  int $storeId
     *
     * @return array
     */
    public function applePayDomainSetup($storeDomain, $storeId)
    {
        if (!$this->config->isApiKeyConfigured($storeId)) {
            return [
                'success' => false,
                'errorMsg' => '',
            ];
        }

        $domainSetupResult = $this->config->getApplePayDomainSetupResult();

        if (!empty($domainSetupResult)) {
            $domainSetupResult = $this->json->jsonDecode($domainSetupResult); // @phpstan-ignore-line
        }

        if (!is_array($domainSetupResult)) {
            $domainSetupResult = [];
        }

        if (in_array($storeDomain, $domainSetupResult)) {
            return [
                'success' => true,
                'domain' => $storeDomain
            ];
        }

        if (!$this->config->isLiveMode($storeId)) {
            return [
                'success' => false,
                'domain' => $storeDomain,
                'errorMsg' => 'Apple Pay is not active in Sandbox.',
            ];
        }
        
        try {
            $this->downloadOnboardingFile();
            $register_result = $this->revolutApi->registerApplePayDomain($storeDomain, $storeId);

            if (isset($register_result['status'])) {
                unset($register_result['status']);
            }

            if (!empty($register_result) && is_array($register_result)
            && !in_array('applePayRegistrationId', array_keys($register_result))) {
                $this->logger->debug($this->json->jsonEncode($register_result));
                if (is_array($register_result) && in_array('errorMessage', array_keys($register_result))) {
                    $register_result = $register_result['errorMessage'];
                } else {
                    $register_result = $this->json->jsonEncode($register_result);
                }

                throw new LocalizedException(__('Cannot onboard Apple pay merchant: ' . $register_result));
            }

            $this->logger->debug("register result: " . $storeDomain . ' ' . $this->json->jsonEncode($register_result));
            $domainSetupResult[] = $storeDomain;
            $this->config->saveApplePayDomainSetupResult($this->json->jsonEncode($domainSetupResult));

            $this->fileSystem->deleteDirectory($this->getOnboardingFileDir());
            $this->fileSystem->deleteDirectory($this->getOnboardingFileDir(true));
        
            $register_result = [
                'success' => true,
                'domain' => $storeDomain
            ];
        } catch (\Exception $e) {
            $error_msg = "Can not register ApplePay domain: {$e->getMessage()}";
            $this->logger->debug($error_msg);
            $register_result = [
                'success' => false,
                'errorMsg' => $error_msg,
                'domain' => $storeDomain
            ];
        }

        return $register_result;
    }
    
    /**
     * Get OnboardingFileDir
     *
     * @param bool $publicDir
     *
     * @return string
     */
    public function getOnboardingFileDir($publicDir = false)
    {
        $rootPath = $this->directory->getRoot();
        $domain_onboarding_file_directory = '.well-known';

        if ($publicDir) {
            $rootPath  = $this->directory->getPath('pub');
        }

        return $rootPath . '/' . $domain_onboarding_file_directory;
    }
    
    /**
     * Download OnboardingFile
     *
     * @return void
     */
    public function downloadOnboardingFile()
    {
        $domain_onboarding_file_name = 'apple-developer-merchantid-domain-association';

        $onboarding_file_dir = $this->getOnboardingFileDir();
        $onboarding_file_path = $onboarding_file_dir . '/' . $domain_onboarding_file_name;
        
        $onboarding_file_dir_pub = $this->getOnboardingFileDir(true);
        $onboarding_file_path_pub = $onboarding_file_dir_pub . '/' . $domain_onboarding_file_name;
        
        $domain_onboarding_file_remote_link = 'https://assets.revolut.com/api-docs/merchant-api'
                                            .'/files/apple-developer-merchantid-domain-association';
        
        if (!$this->fileSystem->isExists($onboarding_file_dir)
        && ! $this->fileSystem->createDirectory($onboarding_file_dir, 0755)) {
            throw new LocalizedException(__("Can not locate onboarding file: permission issue"));
        }

        if (!$this->fileSystem->isExists($onboarding_file_dir_pub)
        && ! $this->fileSystem->createDirectory($onboarding_file_dir_pub, 0755)) {
            throw new LocalizedException(__("Can not locate onboarding file: pub dir permission issue"));
        }

        if (!$this->fileSystem->filePutContents(
            $onboarding_file_path,
            $this->fileSystem->fileGetContents($domain_onboarding_file_remote_link)
        )
        ) {
            throw new LocalizedException(
                __('Cannot onboard Apple pay merchant: Can not locate on-boarding file')
            );
        }

        if (!$this->fileSystem->filePutContents(
            $onboarding_file_path_pub,
            $this->fileSystem->fileGetContents($domain_onboarding_file_remote_link)
        )
        ) {
            throw new LocalizedException(
                __('Cannot onboard Apple pay merchant: Can not locate on-boarding file in pub dir')
            );
        }
    }

    /**
     * Create json response
     *
     * @param  array $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response)
    {
        return $this->getResponse()->representJson(
            $this->json->jsonEncode($response)
        );
    }
}
