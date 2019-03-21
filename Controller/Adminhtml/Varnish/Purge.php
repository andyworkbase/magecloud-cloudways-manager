<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Controller\Adminhtml\Varnish;

use MageCloud\CloudwaysManager\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Purge
 *
 * Supported events:
 *  magecloud_cloudways_manager_varnish_purge_before
 *  magecloud_cloudways_manager_varnish_purge_after
 *
 * @package MageCloud\CloudwaysManager\Controller\Adminhtml\Varnish
 */
class Purge extends Index
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $responseContent = [];
        if (!$this->getRequest()->isAjax()) {
            $responseContent = [
                'errors' => true,
                'message' => __('Invalid request.')
            ];
            $resultJson->setData($responseContent);
            return $resultJson;
        }

        $params = [
            'action' => 'purge'
        ];

        $this->_eventManager->dispatch('magecloud_cloudways_manager_varnish_purge_before',
            ['params' => $params, 'request' => $this->getRequest()]
        );

        /** @var \MageCloud\CloudwaysManager\Model\Varnish\Manager $manager */
        $manager = $this->varnishManagerFactory->create();
        $response = $manager->buildRequest('service/varnish', 'POST', $params)
            ->sendRequest()
            ->getFormattedResponse();

        $isError = $response->getIsError() && !empty($response->getErrorMessage());
        if ($isError) {
            $responseContent['errors'] = true;
            $responseContent['message'] = $response->getErrorMessage();
        } else if ($response->getSuccess()) {
            // ajax response content should only be shown in error case,
            // if result successful page will be refreshed with success message
            $this->messageManager->addSuccessMessage(__('Varnish cache was purged successfully.'));
        } else {
            $responseContent['errors'] = true;
            $responseContent['message'] = 'Unexpected error. Please try again later.';
        }

        $this->_eventManager->dispatch('magecloud_cloudways_manager_varnish_purge_after',
            ['params' => $params, 'response' => $response]
        );

        $resultJson->setData($responseContent);
        return $resultJson;
    }
}