<?php
/**
 * @author andy
 * @email andyworkbase@gmail.com
 * @team MageCloud
 */
namespace MageCloud\CloudwaysManager\Console\Command\Service;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;
use MageCloud\CloudwaysManager\Helper\Data as HelperData;
use MageCloud\CloudwaysManager\Model\Service\StateFactory as ServiceStateFactory;

/**
 * Class State
 * @package MageCloud\CloudwaysManager\Console\Command\Service
 */
class State extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ServiceStateFactory
     */
    protected $serviceStateFactory;

    /**
     * State constructor.
     * @param AppState $state
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param HelperData $helperData
     * @param ServiceStateFactory $serviceStateFactory
     */
    public function __construct(
        AppState $state,
        \Magento\Framework\Event\Manager $eventManager,
        HelperData $helperData,
        ServiceStateFactory $serviceStateFactory
    ) {
        parent::__construct();
        $this->appState = $state;
        $this->serviceStateFactory = $serviceStateFactory;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('magecloud:cloudways-manager:service-state')
            ->setDescription('Cloudways Varnish Service State');
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
        if (!count($isEnabled)) {
            $output->writeln("<info>Enabled module to perform this operation.</info>");
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        }

        /** @var \MageCloud\CloudwaysManager\Model\Service\State $serviceState */
        $serviceState = $this->serviceStateFactory->create();
        $response = $serviceState->buildRequest('service', 'GET')
            ->sendRequest()
            ->getFormattedResponse();

        $list = [];
        $isError = $response->getIsError() && !empty($response->getErrorMessage());
        if ($isError) {
            $message = $response->getErrorMessage();
        } else if ($response->getSuccess()) {
            $services = $response->getServices();
            $list = isset($services['status']) ? $services['status'] : $list;
        } else {
            $message = 'Unexpected error. Please try again later.';
        }

        if ($isError) {
            $output->writeln("<error>{$message}</error>");
        } else if (!empty($list)) {
            $this->buildDecoratedResponse($output, $list);
        }

        return false;
    }

    /**
     * @param OutputInterface $output
     * @param $list
     */
    private function buildDecoratedResponse(OutputInterface $output, $list)
    {
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Service', 'Status']);
        $rows = [];
        foreach ($list as $service => $status) {
            if (is_int($status)) {
                $statusLabel = $status ? 'Enabled' : 'Disabled';
            } else {
                $statusLabel = ucfirst($status);
            }
            $rowData = [
                'Service' => $service,
                'Status' => $statusLabel,
            ];
            $rows[] = $rowData;
        }

        $table->addRows($rows);
        $table->render($output);
    }
}