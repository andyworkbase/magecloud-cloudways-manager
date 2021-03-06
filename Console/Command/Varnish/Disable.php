<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Console\Command\Varnish;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;
use MageCloud\CloudwaysManager\Helper\Data as HelperData;
use MageCloud\CloudwaysManager\Model\Varnish\ManagerFactory as VarnishManagerFactory;

/**
 * Class Disabled
 * @package MageCloud\CloudwaysManager\Console\Command\Varnish
 */
class Disable extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var VarnishManagerFactory
     */
    protected $varnishManagerFactory;

    /**
     * Disabled constructor.
     * @param AppState $state
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param HelperData $helperData
     * @param VarnishManagerFactory $varnishManagerFactory
     */
    public function __construct(
        AppState $state,
        \Magento\Framework\Event\Manager $eventManager,
        HelperData $helperData,
        VarnishManagerFactory $varnishManagerFactory
    ) {
        parent::__construct();
        $this->appState = $state;
        $this->eventManager = $eventManager;
        $this->varnishManagerFactory = $varnishManagerFactory;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('magecloud:cloudways-manager:varnish-disabled')
            ->setDescription('Disable Cloudways Varnish Service');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        $isEnabled = $this->helperData->isEnabled();
        if (!$isEnabled) {
            $output->writeln("<info>Enabled module to perform this operation.</info>");
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        }

        $params = [
            'action' => 'disable'
        ];

        $this->eventManager->dispatch('magecloud_cloudways_varnish_disable_before',
            ['params' => $params, 'request' => null]
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
            $message = 'Varnish service was disabled.';
        } else {
            $message = 'Unexpected error. Please try again later.';
        }

        $this->eventManager->dispatch('magecloud_cloudways_varnish_disable_after',
            ['params' => $params, 'response' => $response]
        );

        if ($isError) {
            $output->writeln("<error>{$message}</error>");
        } else {
            $output->writeln("<info>{$message}</info>");
        }

        return false;
    }
}