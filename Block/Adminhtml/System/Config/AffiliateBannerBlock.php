<?php

namespace Revolut\Payment\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class AffiliateBannerBlock extends Field
{
    protected $_template = 'Revolut_Payment::system/banners/affiliate_banner.phtml';
    protected function _getElementHtml(AbstractElement $element)
    {
       return $this->_toHtml();
    }
}