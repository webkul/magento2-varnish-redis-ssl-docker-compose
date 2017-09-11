<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Composer\MagentoComposerApplication;
use Magento\Update\Queue;
use Magento\Update\Backup;
use Magento\Update\Status;

/**
 * Magento updater application 'uninstall component' job.
 */
class JobComponentUninstall extends AbstractJob
{
    /**
     * @var MagentoComposerApplication
     */
    protected $composerApp;

    /**
     * @var \Magento\Update\Queue
     */
    protected $queue;

    /**
     * Constructor
     *
     * @param string $name
     * @param array $params
     * @param MagentoComposerApplication $composerApp
     * @param Status $status
     */
    public function __construct(
        $name,
        $params,
        MagentoComposerApplication $composerApp = null,
        Status $status = null,
        Queue $queue = null
    ) {
        parent::__construct($name, $params, $status);
        $this->queue = $queue ? $queue : new Queue();
        $this->composerApp = $composerApp;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $this->composerApp = $this->composerApp ? : $this->getComposerApp();
            $this->status->add('Starting composer remove...', \Psr\Log\LogLevel::INFO);
            if (isset($this->params['components'])) {
                $packages = [];
                foreach ($this->params['components'] as $compObj) {
                    $packages[] = $compObj['name'];
                }
                $this->status->add(
                    $this->composerApp->runComposerCommand(
                        ['command' => 'remove', 'packages' => $packages, '--no-update' => true]
                    ),
                    \Psr\Log\LogLevel::INFO
                );
            } else {
                throw new \RuntimeException('Cannot find component to uninstall');
            }
            $this->status->add(
                $this->composerApp->runComposerCommand(['command' => 'update']),
                \Psr\Log\LogLevel::INFO
            );
            $this->status->add('Composer remove completed successfully', \Psr\Log\LogLevel::INFO);
            $this->queue->addJobs(
                [['name' => \Magento\Update\Queue\JobFactory::NAME_MAINTENANCE_MODE, 'params' => ['enable' => false]]]
            );
        } catch (\Exception $e) {
            $this->status->setUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
        return $this;
    }

    /**
     * Get composer application
     *
     * @return MagentoComposerApplication
     */
    private function getComposerApp()
    {
        $vendorPath = MAGENTO_BP . '/' . (include (MAGENTO_BP . '/app/etc/vendor_path.php'));
        $composerPath = $vendorPath . '/../composer.json';
        $composerPath = realpath($composerPath);

        return new MagentoComposerApplication(MAGENTO_BP . '/var/composer_home', $composerPath);
    }
}
