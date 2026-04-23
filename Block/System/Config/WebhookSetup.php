<?php

namespace Revolut\Payment\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Exception\LocalizedException;
use Revolut\Payment\Model\Helper\Logger;
use Revolut\Payment\Gateway\Config\Config;
use Revolut\Payment\Model\RevolutOrder;
use Magento\Framework\UrlInterface;
use Revolut\Payment\Model\Helper\ConstantValue;
use Revolut\Payment\Model\Helper\Api\RevolutWebhookApi;
use Magento\Framework\Session\SessionManagerInterface;

class WebhookSetup extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Revolut_Payment::system/config/webhook_setup.phtml';

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var RevolutWebhookApi
     */
    protected $webhookApi;
    
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var RevolutOrder
     */
    protected $revolutOrder;
    
    /**
     * @var UrlInterface
     */
    protected $urlHelper;
    
    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * Webhook constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param UrlInterface $urlHelper
     * @param SessionManagerInterface $session
     * @param Logger $logger
     * @param RevolutWebhookApi $webhookApi
     * @param RevolutOrder $revolutOrder
     * @param ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        UrlInterface $urlHelper,
        SessionManagerInterface $session,
        Logger $logger,
        RevolutWebhookApi $webhookApi,
        RevolutOrder $revolutOrder,
        ModuleListInterface $moduleList,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->urlHelper = $urlHelper;
        $this->session = $session;
        $this->webhookApi = $webhookApi;
        $this->revolutOrder = $revolutOrder;
        
        $this->moduleList = $moduleList;
        parent::__construct($context, $data);
    }

    /**
     * Get CacheLifetime
     */
    public function getCacheLifetime()
    {
        return null;
    }
    
    /**
     * Get WebhookSetupAjaxUrl
     *
     * @return string
     */
    public function getWebhookSetupAjaxUrl()
    {
        return $this->getUrl('revolut/webhook/setup', ['store' => $this->getRequest()->getParam('store', 0)]);
    }

    /**
     * Render
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get ElementHtml
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
