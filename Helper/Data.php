<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package MageCloud\CloudwaysManager\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Cloudflare API endpoint
     */
    const CLOUDWAYS_DEFAULT_API_ENDPOINT = 'https://api.cloudways.com/api/v1/';

    /**
     * XML paths
     */
    const XML_PATH_ENABLED = 'cloudways/general/enabled';
    const XML_PATH_EMAIL_ADDRESS = 'cloudways/general/email_address';
    const XML_PATH_API_KEY = 'cloudways/general/api_key';
    const XML_PATH_API_ENDPOINT = 'cloudways/general/api_endpoint';
    const XML_PATH_PURGE_AUTO = 'cloudways/general/purge_auto';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
    }

    /**
     * @param null $store
     * @return bool|mixed
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool|mixed
     */
    public function getApiEndpoint($store = null)
    {
        if ($this->isEnabled($store)) {
            $value = $this->scopeConfig->getValue(
                self::XML_PATH_API_ENDPOINT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            return $value ?: self::CLOUDWAYS_DEFAULT_API_ENDPOINT;
        }

        return null;
    }

    /**
     * @param null $store
     * @return bool|mixed
     */
    public function getEmailAddress($store = null)
    {
        if ($this->isEnabled($store)) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_ADDRESS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        return null;
    }

    /**
     * @param null $store
     * @return bool|mixed
     */
    public function getApiKey($store = null)
    {
        if ($this->isEnabled($store)) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_API_KEY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        return null;
    }

    /**
     * @param null $store
     * @return bool|mixed
     */
    public function isPurgeAuto($store = null)
    {
        if ($this->isEnabled($store)) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_PURGE_AUTO,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        return null;
    }
}