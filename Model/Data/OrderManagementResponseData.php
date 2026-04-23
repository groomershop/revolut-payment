<?php

namespace Revolut\Payment\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Revolut\Payment\Api\Data\OrderManagementResponseDataInterface;

class OrderManagementResponseData extends AbstractExtensibleObject implements OrderManagementResponseDataInterface
{
    /**
     * Get RedirectUrl
     *
     * @return string|mixed
     */
    public function getRedirectUrl()
    {
        return $this->_get(self::KEY_REDIRECT_URL);
    }
    
    /**
     * Set RedirectUrl
     *
     * @param string $redirectUrl
     * @return $this
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->setData(self::KEY_REDIRECT_URL, $redirectUrl);
        return $this;
    }

    /**
     * Get Success
     *
     * @return bool|mixed
     */
    public function getSuccess()
    {
        return $this->_get(self::KEY_SUCCESS);
    }
    
    /**
     * Set Success
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->setData(self::KEY_SUCCESS, $success);
        return $this;
    }
    
    /**
     * Get InProgress
     *
     * @return bool|mixed
     */
    public function getInProgress()
    {
        return $this->_get(self::KEY_PROGRESS);
    }
    
    /**
     * Set InProgress
     *
     * @param bool $progress
     * @return $this
     */
    public function setInProgress($progress)
    {
        $this->setData(self::KEY_PROGRESS, $progress);
        return $this;
    }

    /**
     * Get Currency
     *
     * @return string|mixed
     */
    public function getCurrency()
    {
        return $this->_get(self::KEY_CURRENCY);
    }
    
    /**
     * Set Currency
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        return $this->setData(self::KEY_CURRENCY, $currency);
    }

    /**
     * Get Message
     *
     * @return string|mixed
     */
    public function getMessage()
    {
        return $this->_get(self::KEY_MESSAGE);
    }
    
    /**
     * Set Message
     *
     * @param string $message
     * @@return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::KEY_MESSAGE, $message);
        return $this;
    }
    
    /**
     * Get CustomerId
     *
     * @return int|mixed
     */
    public function getCustomerId()
    {
        return $this->_get(self::KEY_CUSTOMER_ID);
    }
    
    /**
     * Set CustomerId
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }
    
    /**
     * Get QuoteId
     *
     * @return int|mixed
     */
    public function getQuoteId()
    {
        return $this->_get(self::KEY_QUOTE_ID);
    }
    
    /**
     * Set QuoteId
     *
     * @param int|mixed $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::KEY_QUOTE_ID, $quoteId);
    }
    
    /**
     * Get StoreId
     *
     * @return int|mixed
     */
    public function getStoreId()
    {
        return $this->_get(self::KEY_STORE_ID);
    }
    
    /**
     * Set StoreId
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::KEY_STORE_ID, $storeId);
    }
    
    /**
     * Get Amount
     *
     * @return int|mixed
     */
    public function getAmount()
    {
        return $this->_get(self::KEY_AMOUNT);
    }
    
    /**
     * Set Amount
     *
     * @param int|mixed  $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->setData(self::KEY_AMOUNT, $amount);
    }
    
    /**
     * Get PublicId
     *
     * @return string|mixed
     */
    public function getPublicId()
    {
        return $this->_get(self::KEY_PUBLIC_ID);
    }
    
    /**
     * Set PublicId
     *
     * @param string $publicId
     * @return $this
     */
    public function setPublicId($publicId)
    {
        return $this->setData(self::KEY_PUBLIC_ID, $publicId);
    }

    /**
     * Get PublicKey
     *
     * @return string|mixed
     */
    public function getPublicKey()
    {
        return $this->_get(self::KEY_PUBLIC_KEY);
    }
    
    /**
     * Set PublicKey
     *
     * @param string $publicKey
     * @return $this
     */
    public function setPublicKey($publicKey)
    {
        return $this->setData(self::KEY_PUBLIC_KEY, $publicKey);
    }
    
    /**
     * Get AvailableCardBrands
     *
     * @return string[]|mixed
     */
    public function getAvailableCardBrands()
    {
        return $this->_get(self::KEY_AVAILABLE_CARD_BRANDS);
    }
    
    /**
     * Set AvailableCardBrands
     *
     * @param string[] $availableCardBrands
     * @return $this
     */
    public function setAvailableCardBrands($availableCardBrands)
    {
        return $this->setData(self::KEY_AVAILABLE_CARD_BRANDS, $availableCardBrands);
    }
}
