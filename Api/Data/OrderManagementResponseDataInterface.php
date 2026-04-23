<?php

namespace Revolut\Payment\Api\Data;

/**
 * Interface OrderManagementResponseDataInterface
 * @api
 */
interface OrderManagementResponseDataInterface
{

    public const KEY_SUCCESS = 'success';

    public const KEY_PROGRESS = 'progress';

    public const KEY_REDIRECT_URL = 'redirect_url';

    public const KEY_CURRENCY = 'currency';

    public const KEY_MESSAGE = 'message';

    public const KEY_CUSTOMER_ID = 'customer_id';

    public const KEY_QUOTE_ID = 'quote_id';

    public const KEY_STORE_ID = 'store_id';

    public const KEY_AMOUNT = 'amount';

    public const KEY_PUBLIC_ID = 'public_id';

    public const KEY_PUBLIC_KEY = 'public_key';

    public const KEY_AVAILABLE_CARD_BRANDS = 'available_card_brands';

    public const KEY_IS_REWARD_BANNER_ACTIVE = 'is_reward_banner_active';
    
    /**
     * Set RedirectUrl
     *
     * @return string
     */
    public function getRedirectUrl();
    
    /**
     * Set RedirectUrl
     *
     * @param String|mixed $redirectUrl
     * @return $this
     */
    public function setRedirectUrl($redirectUrl);
    
    /**
     * Set Success
     *
     * @return bool
     */
    public function getSuccess();
    
    /**
     * Set Success
     *
     * @param bool|mixed $success
     * @return $this
     */
    public function setSuccess($success);
    
    /**
     * Get InProgress
     *
     * @return bool
     */
    public function getInProgress();
    
    /**
     * Set InProgress
     *
     * @param bool|mixed $progress
     * @return $this
     */
    public function setInProgress($progress);
    
    /**
     * Get CustomerId
     *
     * @return int
     */
    public function getCustomerId();
    
    /**
     * Set CustomerId
     *
     * @param int|mixed $customerId
     * @return $this
     */
    public function setCustomerId($customerId);
    
    /**
     * Get QuoteId
     *
     * @return int
     */
    public function getQuoteId();
    
    /**
     * Set QuoteId
     *
     * @param int|mixed $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);
    
    /**
     * Get StoreId
     *
     * @return int
     */
    public function getStoreId();
    
    /**
     * Set StoreId
     *
     * @param int|mixed $storeId
     * @return $this
     */
    public function setStoreId($storeId);
    
    /**
     * Get Amount
     *
     * @return int
     */
    public function getAmount();
    
    /**
     * Set Amount
     *
     * @param int|mixed $amount
     * @return $this
     */
    public function setAmount($amount);
    
    /**
     * Get PublicId
     *
     * @return string
     */
    public function getPublicId();
    
    /**
     * Set PublicId
     *
     * @param string|mixed $publicId
     * @return $this
     */
    public function setPublicId($publicId);

    /**
     * Get PublicKey
     *
     * @return string
     */
    public function getPublicKey();
    
    /**
     * Set PublicKey
     *
     * @param string|mixed $publicKey
     * @return $this
     */
    public function setPublicKey($publicKey);
    
    /**
     * Get Currency
     *
     * @return string
     */
    public function getCurrency();
    
    /**
     * Set Currency
     *
     * @param string|mixed $currency
     * @return $this
     */
    public function setCurrency($currency);
    
    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage();
    
    /**
     * Set Message
     *
     * @param string|mixed $message
     * @return $this
     */
    public function setMessage($message);
    
    /**
     * Get AvailableCardBrands
     *
     * @return string[]
     */
    public function getAvailableCardBrands();
    
    /**
     * Set AvailableCardBrands
     *
     * @param string[]|mixed $availableCardBrands
     * @return $this
     */
    public function setAvailableCardBrands($availableCardBrands);
}
