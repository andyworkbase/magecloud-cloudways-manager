<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Block\Backend;

/**
 * Class Cloudways
 * @package MageCloud\CloudwaysManager\Block\Backend
 */
class Cloudways extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _prepareLayout()
    {
        $this->addCloudwaysManagerButton();
        return parent::_prepareLayout();
    }

    /**
     * Add button to manage Cloudways service
     *
     * @param $label
     */
    private function addCloudwaysManagerButton()
    {
        if ($this->_authorization->isAllowed('MageCloud_CloudwaysManager::cloudways')) {
            $this->buttonList->add(
                'cloudways_manager',
                [
                    'label' => __('Cloudways Manager'),
                    'class' => 'cloudways-manager primary',
                    'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
                    'options' => $this->getButtonOptions(),
                ]
            );
        }
    }

    /**
     * @return array
     */
    private function getButtonOptions()
    {
        $splitButtonOptions = [
            'service_state' => [
                'id' => 'service-state',
                'label' => __('Check Services State'),
                'default' => false,
            ],
            'varnish_enable' => [
                'id' => 'varnish-enable',
                'label' => __('Enable Varnish'),
                'default' => false,
            ],
            'varnish_disable' => [
                'id' => 'varnish-disable',
                'label' => __('Disable Varnish'),
                'default' => false,
            ],
            'varnish-purge' => [
                'id' => 'varnish-purge',
                'label' => __('Purge Varnish Cache'),
                'default' => false,
            ],
        ];

        return $splitButtonOptions;
    }
}