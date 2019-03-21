<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Model;

use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\CacheInvalidate\Model\SocketFactory;
use Magento\Framework\HTTP\PhpEnvironment\ServerAddress;
use Magento\Framework\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use MageCloud\CloudwaysManager\Helper\Data as HelperData;
use MageCloud\CloudwaysManager\Model\Serializer;
use Magento\Framework\App\ObjectManager;

/**
 * API manager for Cloudways
 *
 * Class AbstractManager
 * @package MageCloud\CloudwaysManager\Model
 */
abstract class AbstractManager extends \Magento\Framework\DataObject
{
    /**
     * HTTP RESPONSE CODES
     */
    const HTTP_RESPONSE_CODE_SUCCESS = '200';
    const HTTP_RESPONSE_CODE_TOKEN_EXPIRED = '401';

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var SocketFactory
     */
    protected $socketAdapterFactory;

    /**
     * @var ServerAddress
     */
    protected $serverAddress;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * API request headers
     *
     * @var array
     */
    private $headers = [
        "Content-Type: application/json"
    ];

    /**
     * API request params
     *
     * @var array
     */
    private $params = [];

    /**
     * Cloudways API endpoint
     *
     * @var null
     */
    private $url = '';

    /**
     * Cloudways API Key
     *
     * @var string
     */
    private $apiKey = '';

    /**
     * Email address associated with Cloudways account
     *
     * @var string
     */
    private $email = '';

    /**
     * API request method
     *
     * @var string
     */
    private $requestMethod = '';

    /**
     * API response access token
     *
     * @var string
     */
    private $accessToken = '';

    /**
     * API response server list related to Cloudways account
     *
     * @var array
     */
    private $serverList = [];

    /**
     * API response needed server id related to Cloudways account
     *
     * @var string
     */
    private $serverId = '';

    /**
     * Response from API request
     *
     * @var null
     */
    private $response = null;

    /**
     * Errors recollected after each API call
     *
     * @var array
     */
    protected $callErrors = [];

    /**
     * AbstractManager constructor.
     * @param CurlFactory $curlFactory
     * @param SocketFactory $socketAdapterFactory
     * @param ServerAddress $serverAddress
     * @param UrlInterface $urlBuilder
     * @param EncryptorInterface $encryptor
     * @param HelperData $helperData
     * @param \MageCloud\CloudwaysManager\Model\Serializer|null $serializer
     * @param array $data
     */
    public function __construct(
        CurlFactory $curlFactory,
        SocketFactory $socketAdapterFactory,
        ServerAddress $serverAddress,
        UrlInterface $urlBuilder,
        EncryptorInterface $encryptor,
        HelperData $helperData,
        Serializer $serializer = null,
        array $data = []
    ) {
        parent::__construct($data);
        $this->curlFactory = $curlFactory;
        $this->socketAdapterFactory = $socketAdapterFactory;
        $this->serverAddress = $serverAddress;
        $this->urlBuilder = $urlBuilder;
        $this->encryptor = $encryptor;
        $this->helperData = $helperData;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Serializer::class);
    }

    /**
     * As for API Key field in configuration we use type 'obscure' we need to decrypt value before use
     *
     * @return string
     */
    private function getDecryptedApiKey()
    {
        return $this->encryptor->decrypt(trim($this->helperData->getApiKey()));
    }

    /**
     * Prepare API url for request
     *
     * @param $action
     * @return bool
     */
    public function prepareApiUrl($action)
    {
        $apiEndpoint = $this->helperData->getApiEndpoint();
        if (!$action) {
            return false;
        }

        $url = sprintf($apiEndpoint . '%s', $action);
        $method = $this->getRequestMethod();
        $params = $this->getParams();
        // for 'GET" method parameter(s) should be used only as a query string in url
        if (($method == \Zend_Http_Client::GET) && (!empty($params))) {
            $this->resetParams();
            $url = sprintf($apiEndpoint . '%s?%s', $action, http_build_query($params));
        }
        $this->setApiUrl($url);

        return true;
    }

    /**
     * Prepare API authorization post params for request
     *
     * @return bool
     */
    public function prepareAuthorizationPostParams()
    {
        $apiKey = $this->getDecryptedApiKey();
        $email = trim($this->helperData->getEmailAddress());
        if (!$apiKey || !$email) {
            return false;
        }

        if (!$this->apiKey) {
            $this->apiKey = $apiKey;
        }
        if (!$this->email) {
            $this->email = $email;
        }

        $this->setParams([
            'email' => $this->email,
            'api_key' => $this->apiKey
        ]);

        return true;
    }

    /**
     * Prepare API authorization headers for request
     *
     * @return bool
     */
    public function prepareAuthorizationHeaders()
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return false;
        }

        $this->setHeaders([
            "Authorization: Bearer {$token}"
        ]);

        return true;
    }

    /**
     * Prepare API server params for request
     *
     * @return bool
     */
    public function prepareServerRequestParams()
    {
        $token = $this->getAccessToken();
        $list = $this->getServerList();
        if (!$token || empty($list)) {
            return false;
        }

        $serverId = $this->retrieveNeededServerId($list);
        if (!$serverId) {
            return false;
        }

        $this->setParams([
            'server_id' => $serverId
        ]);

        return true;
    }

    /**
     * @param $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function resetHeaders()
    {
        $this->setHeaders([]);

        return true;
    }

    /**
     * @param $params
     */
    public function setParams($params)
    {
        $this->params = array_merge($this->params, $params);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return bool
     */
    public function resetParams()
    {
        $this->setParams([]);

        return true;
    }

    /**
     * @param string $method
     */
    public function setRequestMethod($method = \Zend_Http_Client::POST)
    {
        $this->requestMethod = $method;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * @param $url
     */
    public function setApiUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->url;
    }

    /**
     * @param $token
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param array $list
     */
    public function setServerList($list)
    {
        $this->serverList = $list;
    }

    /**
     * @return array
     */
    public function getServerList()
    {
        return $this->serverList;
    }

    /**
     * @param int $id
     */
    public function setServerId($id)
    {
        $this->serverId = $id;
    }

    /**
     * @return int
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * @param $response
     */
    public function setResponse($response)
    {
        if (!$this->response || (!$this->response instanceof \Magento\Framework\DataObject)) {
            $this->response = $response;
        } else {
            $this->response->setData($response);
        }
    }

    /**
     * @return null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Format response to needed format
     *
     * @return null
     */
    public function getFormattedResponse()
    {
        if ($response = $this->getResponse()) {
            $response->setIsError(true);
            if ($internalError = $response->getErrorMessage()) {
                // authorization, connection, internal, etc... error(s)
                return $response;
            } else if ($responseError = $response->getMessage()) {
                // API response error(s)
                $response->setErrorMessage($responseError);
            } else if ($response->getSuccess()) {
                $response->setIsError(false);
            } else {
                $response->setErrorMessage(__('Unexpected error. Please try again later.'));
            }
        }

        return $response;
    }

    /**
     * Build data for API request
     *
     * @return mixed
     */
    abstract public function buildRequest();

    /**
     * @return $this|string
     */
    private function prepareAccessToken()
    {
        if ($this->getAccessToken()) {
            return true;
        }

        $this->prepareApiUrl('oauth/access_token');
        $this->setRequestMethod('POST');
        $this->sendRequest();
        $response = $this->getResponse();
        if (!$response->getAccessToken()) {
            return false;
        }

        $this->setAccessToken($response->getAccessToken());

        return $this;
    }

    /**
     * @return $this|bool
     */
    private function prepareServerList()
    {
        if (!$this->getAccessToken()) {
            return false;
        }

        $this->prepareApiUrl('server');
        $this->setRequestMethod('GET');
        $this->sendRequest();
        $response = $this->getResponse();
        if (!$response->getStatus() && empty($response->getServers())) {
            return false;
        }

        $this->setServerList($response->getServers());

        return $this;
    }

    /**
     * Required pre request action(s) (retrieve access token, get server list, etc...)
     *
     * @return $this
     */
    public function preProcess()
    {
        if (!$this->getAccessToken()) {
            $this->prepareAccessToken();
            if (!$this->prepareAuthorizationHeaders()) {
                $this->setResponse([
                    'error_message' => __('Access token is invalid.')
                ]);
                return $this;
            }
            $this->prepareServerList();
            if (!$this->prepareServerRequestParams()) {
                $this->setResponse([
                    'error_message' => __('Server(s) are not available.')
                ]);
                return $this;
            };
        }

        return $this;
    }

    /**
     * Send request to Cloudways service by API
     *
     * @return $this
     */
    public function sendRequest()
    {
        $response = $this->getResponse();
        if ($response->getErrorMessage()) {
            // return if internal error
            return $this;
        }

        try {
            /** @var \Magento\Framework\HTTP\Adapter\Curl $curl */
            $curl = $this->curlFactory->create();
            $curl->write(
                $this->getRequestMethod(),
                $this->getApiUrl(),
                '1.1',
                $this->getHeaders(),
                $this->serializer->serialize($this->getParams())
            );
            $result = $curl->read();
            if ($curl->getErrno()) {
                $this->setResponse([
                    'error_message' => sprintf(
                        'Cloudways API service connection error #%s: %s',
                        $curl->getErrno(),
                        $curl->getError()
                    )
                ]);
                return $this;
            }
            $result = \Zend_Http_Response::fromString($result);
            $responseBody = $result->getBody();
            $result = $this->serializer->unserialize($responseBody);
            if (!array_key_exists('success', $result)) {
                $result['success'] = true;
            }
            $this->setResponse($result);
            $curl->close();
        } catch (\Exception $e) {
            $this->setResponse([
                'error_message' => $e->getMessage()
            ]);
        }

        return $this;
    }

    /**
     * Get current store base url
     *
     * @return string
     */
    public function getStoreUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_nosid' => true]);
    }

    /**
     * Format store url (need to compare with url from server list from related API response)
     *
     * @return string|string[]|null
     */
    private function getFormattedStoreUrl()
    {
        $url = preg_replace('#^https?://#', '', $this->getStoreUrl());
        return (substr($url, -1) == '/') ? substr($url, 0, -1) : $url;
    }

    /**
     * Retrieve Server IP address
     * 
     * @return string
     */
    private function getServerIp()
    {
        return $this->serverAddress->getServerAddress();
    }

    /**
     * Retrieve needed server ID for API response
     *
     * @param $data
     * @return int|null
     */
    private function retrieveNeededServerId($data)
    {
        if ($this->getServerId()) {
            return $this->getServerId();
        }

        if (empty($data)) {
            return null;
        }

        // bind server ID if only one server present to prevent parse all server list
        $serverId = isset($data[0]['id']) ? $data[0]['id'] : null;
        if (count($data) > 1) {
            $storeUrlForCompare = $this->getFormattedStoreUrl();
            foreach ($data as $item) {
                $serverId = isset($server['id']) ? $item['id'] : $serverId;
                $ip = isset($item['public_ip']) ? $item['public_ip'] : false;
                if ($ip && ($ip == $this->getServerIp())) {
                    $this->setServerId($serverId);
                    break;
                }
                // as site may working through additional service(s) and IP address may be different
                // parse apps from response server list to retrieve site url to compare with current to find needed one
                $applications = isset($item['apps']) ? $item['apps'] : [];
                if (!empty($applications)) {
                    foreach ($applications as $app) {
                        $cname = isset($app['cname'])
                            ? str_replace('www.', '', $app['cname'])
                            : null;

                        if ($cname && ($cname == $storeUrlForCompare)) {
                            $this->setServerId($serverId);
                            break;
                        }
                    }
                }
            }
        }

        return $serverId;
    }
}