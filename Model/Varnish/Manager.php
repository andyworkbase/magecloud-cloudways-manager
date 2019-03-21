<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Model\Varnish;

use MageCloud\CloudwaysManager\Model\AbstractManager;

/**
 * API manager for Cloudways varnish service
 *
 * Class ApiManager
 * @package MageCloud\CloudwaysManager\Model\Varnish
 */
class Manager extends AbstractManager
{
    /**
     * Available action(s) for API Varnish service
     *
     * @var array
     */
    private $actions = ['enable', 'disable', 'purge'];

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

        // pre process required action(s): retrieve access token, server list, etc..
        $this->preProcess();

        $this->checkActionAvailability($params);

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

    /**
     * @param array $params
     * @return $this|bool
     */
    private function checkActionAvailability($params = [])
    {
        $serviceAction = array_key_exists('action', $params) ? $params['action'] : false;
        if ($serviceAction && !in_array($serviceAction, $this->actions)) {
            $message = sprintf(
                'Action [%s] is not allowed. Expected actions for this service: %s',
                $serviceAction,
                implode(', ', $this->actions)
            );
            $this->setResponse([
                'error_message' => __($message)
            ]);
            return $this;
        }

        return true;
    }
}