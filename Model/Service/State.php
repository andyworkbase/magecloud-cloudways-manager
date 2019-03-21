<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Model\Service;

use MageCloud\CloudwaysManager\Model\AbstractManager;

/**
 * API manager for Cloudways services state
 *
 * Class ApiManager
 * @package MageCloud\CloudwaysManager\Model\Service
 */
class State extends AbstractManager
{
    /**
     * Prepare action(s) before request
     *
     * @param string $action
     * @param array $params
     * @param $method
     * @param array $headers
     * @return $this
     */
    public function buildRequest(
        $action = '',
        $method = \Zend_Http_Client::POST,
        $params = [],
        $headers = []
    ) {
        $this->setResponse(new \Magento\Framework\DataObject([]));

        if (!$this->prepareAuthorizationPostParams()) {
            $this->setResponse([
                'error_message' => __('API Key, Email Address related to Cloudways account must be set up before using API calls.')
            ]);
            return $this;
        }

        // pre process action(s) like retrieve access token, server list, etc..
        $this->preProcess();

        $this->setHeaders($headers);
        $this->setParams($params);
        $this->setRequestMethod($method);

        if (!$this->prepareApiUrl($action)) {
            $this->setResponse([
                'error_message' => __('API url must be set up before using API calls.')
            ]);
            return $this;
        }

        return $this;
    }
}