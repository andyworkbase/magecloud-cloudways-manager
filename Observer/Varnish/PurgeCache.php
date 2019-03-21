<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Observer\Varnish;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use MageCloud\CloudwaysManager\Helper\Data as HelperData;
use MageCloud\CloudwaysManager\Model\Varnish\ManagerFactory as VarnishManagerFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class PurgeCache
 *
 * Supported events:
 * - magecloud_cloudways_manager_varnish_purge_before
 * - magecloud_cloudways_manager_varnish_purge_after
 *
 * @package MageCloud\CloudwaysManager\Observer\Varnish
 */
class PurgeCache implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var VarnishManagerFactory
     */
    protected $varnishManagerFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * PurgeCache constructor.
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param VarnishManagerFactory $varnishManagerFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        RequestInterface $request,
        HelperData $helperData,
        VarnishManagerFactory $varnishManagerFactory,
        ManagerInterface $messageManager
    ) {
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->varnishManagerFactory = $varnishManagerFactory;
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $isEnabled = $this->helperData->isEnabled();
        $isPurgeAuto = $this->helperData->isPurgeAuto();
        if ($isEnabled && $isPurgeAuto) {
            $params = [
                'action' => 'purge'
            ];

            $this->eventManager->dispatch('magecloud_cloudways_manager_varnish_purge_before',
                ['params' => $params, 'request' => $this->request]
            );

            /** @var \MageCloud\CloudwaysManager\Model\Varnish\Manager $manager */
            $manager = $this->varnishManagerFactory->create();
            $response = $manager->buildRequest('service/varnish', 'POST', $params)
                ->sendRequest()
                ->getFormattedResponse();

            $isError = $response->getIsError() && !empty($response->getErrorMessage());
            if ($isError) {
                $message = $response->getErrorMessage();
            } else if ($response->getSuccess()) {
                $message = 'Varnish cache was purged successfully.';
            } else {
                $message = 'Unexpected error. Please try again later.';
            }

            $this->eventManager->dispatch('magecloud_cloudways_manager_varnish_purge_after',
                ['params' => $params, 'response' => $response]
            );

            if ($isError) {
                $this->messageManager->addErrorMessage(__($message));
            } else {
                $this->messageManager->addSuccessMessage(__($message));
            }
        }

        return $this;
    }
}