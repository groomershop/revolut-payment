<?php

namespace Revolut\Payment\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Revolut\Payment\Api\Data\FastCheckoutParametersResponseDataInterface;

class FastCheckoutParametersResponseData extends AbstractExtensibleObject implements
    FastCheckoutParametersResponseDataInterface
{
    /**
     * Get IsRevolutPaymentRequestActiveLocation
     *
     * @return bool|mixed
     */
    public function getIsRevolutPaymentRequestActiveLocation()
    {
        return $this->_get(self::KEY_IS_REVOLUT_PAYMENT_REQUEST_ACTIVE_LOCATION);
    }
    
    /**
     * Set IsRevolutPaymentRequestActiveLocation
     *
     * @param bool $isActiveLocation
     * @return $this
     */
    public function setIsRevolutPaymentRequestActiveLocation($isActiveLocation)
    {
        $this->setData(self::KEY_IS_REVOLUT_PAYMENT_REQUEST_ACTIVE_LOCATION, $isActiveLocation);
        return $this;
    }
    
    /**
     * Get IsRevolutPayActiveLocation
     *
     * @return bool|mixed
     */
    public function getIsRevolutPayActiveLocation()
    {
        return $this->_get(self::KEY_IS_REVOLUT_PAY_ACTIVE_LOCATION);
    }
    
    /**
     * Set IsRevolutPayActiveLocation
     *
     * @param bool $isActiveLocation
     * @return $this
     */
    public function setIsRevolutPayActiveLocation($isActiveLocation)
    {
        $this->setData(self::KEY_IS_REVOLUT_PAY_ACTIVE_LOCATION, $isActiveLocation);
        return $this;
    }
    
    /**
     * Get StoreCode
     *
     * @return string|mixed
     */
    public function getStoreCode()
    {
        return $this->_get(self::KEY_STORE_CODE);
    }
    
    /**
     * Set StoreCode
     *
     * @param string $code
     * @return $this
     */
    public function setStoreCode($code)
    {
        $this->setData(self::KEY_STORE_CODE, $code);
        return $this;
    }
    
    /**
     * Get RevolutPaymentRequestThemeConfigs
     *
     * @return string|mixed
     */
    public function getRevolutPaymentRequestThemeConfigs()
    {
        return $this->_get(self::KEY_REVOLUT_PAYMENT_REQUEST_THEME_CONFIGS);
    }
    
    /**
     * Set RevolutPaymentRequestThemeConfigs
     *
     * @param string $themeConfigs
     * @return $this
     */
    public function setRevolutPaymentRequestThemeConfigs($themeConfigs)
    {
        $this->setData(self::KEY_REVOLUT_PAYMENT_REQUEST_THEME_CONFIGS, $themeConfigs);
        return $this;
    }
    
    /**
     * Get RevolutPayThemeConfigs
     *
     * @return string|mixed
     */
    public function getRevolutPayThemeConfigs()
    {
        return $this->_get(self::KEY_REVOLUT_PAY_THEME_CONFIGS);
    }
    
    /**
     * Set RevolutPayThemeConfigs
     *
     * @param string $themeConfigs
     * @return $this
     */
    public function setRevolutPayThemeConfigs($themeConfigs)
    {
        $this->setData(self::KEY_REVOLUT_PAY_THEME_CONFIGS, $themeConfigs);
        return $this;
    }

    /**
     * Get MethodCode
     *
     * @return string|mixed
     */
    public function getMethodCode()
    {
        return $this->_get(self::KEY_METHOD_CODE);
    }
    
    /**
     * Set MethodCode
     *
     * @param string $methodCode
     * @return $this
     */
    public function setMethodCode($methodCode)
    {
        $this->setData(self::KEY_METHOD_CODE, $methodCode);
        return $this;
    }
    
    /**
     * Get RevolutSdk
     *
     * @return string|mixed
     */
    public function getRevolutSdk()
    {
        return $this->_get(self::KEY_REVOLUT_SDK);
    }
    
    /**
     * Set RevolutSdk
     *
     * @param string $revolutSdk
     * @return $this
     */
    public function setRevolutSdk($revolutSdk)
    {
        $this->setData(self::KEY_REVOLUT_SDK, $revolutSdk);
        return $this;
    }
    
    /**
     * Get ProductId
     *
     * @return int|mixed
     */
    public function getProductId()
    {
        return $this->_get(self::KEY_PRODUCT_ID);
    }
    
    /**
     * Set ProductId
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->setData(self::KEY_PRODUCT_ID, $productId);
        return $this;
    }

}
