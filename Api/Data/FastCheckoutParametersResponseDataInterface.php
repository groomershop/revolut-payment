<?php

namespace Revolut\Payment\Api\Data;

/**
 * Interface FastCheckoutParametersDataInterface
 * @api
 */
interface FastCheckoutParametersResponseDataInterface
{
    public const KEY_IS_REVOLUT_PAYMENT_REQUEST_ACTIVE_LOCATION = 'is_revolut_payment_request_active_location';

    public const KEY_IS_REVOLUT_PAY_ACTIVE_LOCATION = 'is_revolut_pay_active_location';

    public const KEY_STORE_CODE = 'store_code';

    public const KEY_REVOLUT_PAYMENT_REQUEST_THEME_CONFIGS = 'revolut_payment_request_theme_configs';

    public const KEY_REVOLUT_PAY_THEME_CONFIGS = 'revolut_pay_theme_configs';

    public const KEY_METHOD_CODE = 'method_code';

    public const KEY_REVOLUT_SDK = 'revolut_sdk';

    public const KEY_PRODUCT_ID = 'product_id';
    

    /**
     * Get IsRevolutPaymentRequestActiveLocation
     *
     * @return bool
     */
    public function getIsRevolutPaymentRequestActiveLocation();
    
    /**
     * Set IsRevolutPaymentRequestActiveLocation
     *
     * @param bool|mixed $isActiveLocation
     * @return $this
     */
    public function setIsRevolutPaymentRequestActiveLocation($isActiveLocation);
    
    /**
     * Get IsRevolutPayActiveLocation
     *
     * @return bool
     */
    public function getIsRevolutPayActiveLocation();
    
    /**
     * Set IsRevolutPayActiveLocation
     *
     * @param bool|mixed $isActiveLocation
     * @return $this
     */
    public function setIsRevolutPayActiveLocation($isActiveLocation);
    
    /**
     * Get StoreCode
     *
     * @return string
     */
    public function getStoreCode();
    
    /**
     * Set StoreCode
     *
     * @param string|mixed $code
     * @return $this
     */
    public function setStoreCode($code);
    
    /**
     * Get RevolutPaymentRequestThemeConfigs
     *
     * @return string
     */
    public function getRevolutPaymentRequestThemeConfigs();
    
    /**
     * Set RevolutPaymentRequestThemeConfigs
     *
     * @param string|mixed $themeConfigs
     * @return $this
     */
    public function setRevolutPaymentRequestThemeConfigs($themeConfigs);
    
    /**
     * Get RevolutPayThemeConfigs
     *
     * @return string
     */
    public function getRevolutPayThemeConfigs();
    
    /**
     * Set RevolutPayThemeConfigs
     *
     * @param string|mixed $themeConfigs
     * @return $this
     */
    public function setRevolutPayThemeConfigs($themeConfigs);

    /**
     * Get MethodCode
     *
     * @return string
     */
    public function getMethodCode();
    
    /**
     * Set MethodCode
     *
     * @param string|mixed $methodCode
     * @return $this
     */
    public function setMethodCode($methodCode);
    
    /**
     * Get RevolutSdk
     *
     * @return string
     */
    public function getRevolutSdk();
    
    /**
     * Set RevolutSdk
     *
     * @param string|mixed $revolutSdk
     * @return $this
     */
    public function setRevolutSdk($revolutSdk);
    
    /**
     * Get ProductId
     *
     * @return int
     */
    public function getProductId();
    
    /**
     * Set ProductId
     *
     * @param int|mixed $productId
     * @return $this
     */
    public function setProductId($productId);
    
}
