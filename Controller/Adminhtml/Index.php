<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Controller\Adminhtml;

use Magento\Backend\App\Action;
use MageCloud\CloudwaysManager\Helper\Data as HelperData;
use MageCloud\CloudwaysManager\Model\Service\StateFactory as ServiceStateFactory;
use MageCloud\CloudwaysManager\Model\Varnish\ManagerFactory as VarnishManagerFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Index
 * @package MageCloud\CloudwaysManager\Controller\Adminhtml
 */
abstract class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageCloud_CloudwaysManager::cloudways';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ServiceStateFactory
     */
    protected $serviceStateFactory;

    /**
     * @var VarnishManagerFactory
     */
    protected $varnishManagerFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Index constructor.
     * @param Action\Context $context
     * @param HelperData $helperData
     * @param ServiceStateFactory $serviceStateFactory
     * @param VarnishManagerFactory $varnishManagerFactory
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        HelperData $helperData,
        ServiceStateFactory $serviceStateFactory,
        VarnishManagerFactory $varnishManagerFactory,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->serviceStateFactory = $serviceStateFactory;
        $this->varnishManagerFactory = $varnishManagerFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
    }
}