<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Composer\MagentoComposerApplication;
use Magento\Update\Queue;
use Magento\Update\Backup;
use Magento\Update\Rollback;

/**
 * Magento updater application 'update' job.
 */
class JobUpdate extends AbstractJob
{
    /**
     * @var \Magento\Update\Backup
     */
    protected $backup;

    /**
     * @var \Magento\Update\Queue\JobRollback
     */
    protected $jobRollback;

    /**
     * @var Rollback
     */
    protected $rollback;

    /**
     * @var MagentoComposerApplication
     */
    protected $composerApp;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * Constructor
     *
     * @param string $name
     * @param array $params
     * @param Queue $queue
     * @param MagentoComposerApplication $composerApp
     * @param \Magento\Update\Status $status
     * @param Backup $backup
     * @param Rollback $rollback
     */
    public function __construct(
        $name,
        $params,
        Queue $queue = null,
        MagentoComposerApplication $composerApp = null,
        \Magento\Update\Status $status = null,
        Backup $backup = null,
        Rollback $rollback = null
    ) {
        parent::__construct($name, $params, $status);
        $this->queue = $queue ? $queue : new Queue();
        $this->backup = $backup ? $backup : new Backup();
        $this->rollback = $rollback ? $rollback : new Rollback();
        $this->composerApp = $composerApp;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $this->composerApp = $this->composerApp ? : $this->getComposerApp();
            $this->status->add('Starting composer update...', \Psr\Log\LogLevel::INFO);
            if (isset($this->params['components'])) {
                $packages = [];
                foreach ($this->params['components'] as $compObj) {
                    $packages[] = implode(' ', $compObj);
                }
                foreach ($packages as $package) {
                    if (strpos($package, 'magento/product-enterprise-edition') !== false) {
                        $this->composerApp->runComposerCommand(
                            [
                                'command' => 'remove',
                                'packages' => ['magento/product-community-edition'],
                                '--no-update' => true
                            ]
                        );
                    }
                }
                $this->status->add(
                    $this->composerApp->runComposerCommand(
                        ['command' => 'require', 'packages' => $packages, '--no-update' => true]
                    ),
                    \Psr\Log\LogLevel::INFO
                );
            } else {
                throw new \RuntimeException('Cannot find component to update');
            }
            $this->status->add(
                $this->composerApp->runComposerCommand(['command' => 'update']),
                \Psr\Log\LogLevel::INFO
            );
            $this->status->add('Composer update completed successfully', \Psr\Log\LogLevel::INFO);
            $this->createSetupUpgradeTasks();
        } catch (\Exception $e) {
            $this->status->setUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
        return $this;
    }

    /**
     * Create setup:upgrade task for setup application cron
     *
     * @return void
     */
    private function createSetupUpgradeTasks()
    {
        $jobs = [['name' => 'setup:upgrade', 'params' => []]];
        $this->queue->addJobs($jobs);
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
