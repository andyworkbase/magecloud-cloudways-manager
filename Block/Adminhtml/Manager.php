<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Class Manager
 * @package MageCloud\CloudwaysManager\Block\Adminhtml
 */
class Manager extends Template
{
    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Json
     */
    private $serializerJson;

    /**
     * Varnish constructor.
     * @param Template\Context $context
     * @param FormKey $formKey
     * @param array $data
     * @param Json|null $serializerJson
     */
    public function __construct(
        Template\Context $context,
        FormKey $formKey,
        array $data = [],
        Json $serializerJson = null
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->serializerJson = $serializerJson ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * @return array
     */
    public function getUrlConfig()
    {
        return [
            'serviceState' => $this->getServiceStateActionUrl(),
            'varnishEnable' => $this->getVarnishEnableActionUrl(),
            'varnishDisable' => $this->getVarnishDisableActionUrl(),
            'varnishPurge' => $this->getVarnishPurgeActionUrl(),
            'configuration' => $this->getConfigurationUrl()
        ];
    }

    /**
     * @return bool|false|string
     */
    public function getUrlJsonConfig()
    {
        return $this->serializerJson->serialize($this->getUrlConfig());
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Create action url by path
     *
     * @param string $path
     * @return string
     */
    private function getActionUrl($path = '')
    {
        return $this->getUrl($path,
            [
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }

    /**
     * Get url for check Cloudways services state
     *
     * @return string
     */
    private function getServiceStateActionUrl()
    {
        return $this->getActionUrl('cloudways_manager/service/state');
    }

    /**
     * Get url for enable Cloudways varnish service
     *
     * @return string
     */
    private function getVarnishEnableActionUrl()
    {
        return $this->getActionUrl('cloudways_manager/varnish/enable');
    }

    /**
     * Get url for disable Cloudways varnish service
     *
     * @return string
     */
    private function getVarnishDisableActionUrl()
    {
        return $this->getActionUrl('cloudways_manager/varnish/disable');
    }

    /**
     * Get url for purge Cloudways varnish service
     *
     * @return string
     */
    private function getVarnishPurgeActionUrl()
    {
        return $this->getActionUrl('cloudways_manager/varnish/purge');
    }

    /**
     * Get url for configuration page
     *
     * @return string
     */
    private function getConfigurationUrl()
    {
        return $this->getActionUrl('adminhtml/system_config/edit/section/cloudways');
    }
}
