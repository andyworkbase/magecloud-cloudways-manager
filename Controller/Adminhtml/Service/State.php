<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Controller\Adminhtml\Service;

use MageCloud\CloudwaysManager\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class State
 * @package MageCloud\CloudwaysManager\Controller\Adminhtml\Service
 */
class State extends Index
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

        /** @var \MageCloud\CloudwaysManager\Model\Service\State $serviceState */
        $serviceState = $this->serviceStateFactory->create();
        $response = $serviceState->buildRequest('service', 'GET')
            ->sendRequest()
            ->getFormattedResponse();

        $isError = $response->getIsError() && !empty($response->getErrorMessage());
        if ($isError) {
            $responseContent['errors'] = true;
            $responseContent['message'] = $response->getErrorMessage();
        } else if ($response->getSuccess()) {
            $services = $response->getServices();
            $list = isset($services['status']) ? $services['status'] : [];
            if (!empty($list)) {
                $responseContent['content'] = $this->getServicesStateListHtml($list);
            }
        } else {
            $responseContent['errors'] = true;
            $responseContent['message'] = 'Unexpected error. Please try again later.';
        }

        $resultJson->setData($responseContent);
        return $resultJson;
    }

    /**
     * @param $servicesList
     * @return string
     */
    private function getServicesStateListHtml($servicesList)
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $result = [];
        foreach ($servicesList as $code => $status) {
            $statusLabel = $status;
            if (is_int($statusLabel)) {
                $statusLabel = $statusLabel ? 'Enabled' : 'Disabled';
            }
            if (!$status) {
                $decoratorClass = 'grid-severity-disable';
            } else if ($status == 'stopped') {
                $decoratorClass = 'grid-severity-critical';
            } else if ($status == 'running') {
                $decoratorClass = 'grid-severity-minor';
            } else {
                $decoratorClass = 'grid-severity-notice';
            }

            $result[] = [
                'code' => $code,
                'status' => $statusLabel,
                'decorator_class' => $decoratorClass
            ];
        }

        $blockInstance = $resultPage->getLayout()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('MageCloud_CloudwaysManager::services.phtml')
            ->setData('services_list', $result);

        return $blockInstance ? $blockInstance->toHtml() : '';
    }
}