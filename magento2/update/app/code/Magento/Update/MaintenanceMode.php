<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

use Magento\Update\Status;

/**
 * Class for handling Magento maintenance mode.
 */
class MaintenanceMode
{
    /**
     * Path to the maintenance flag file
     *
     * @var string
     */
    protected $flagFile;

    /**
     * Path to the file with white-listed IP addresses
     *
     * @var string
     */
    protected $ipFile;

    /**
     * @var Status
     */
    protected $status;

    /**
     * Initialize.
     *
     * @param string|null $flagFile
     * @param string|null $ipFile
     * @param Status|null $status
     */
    public function __construct($flagFile = null, $ipFile = null, Status $status = null)
    {
        $this->flagFile = $flagFile ? $flagFile : MAGENTO_BP . '/var/.maintenance.flag';
        $this->ipFile = $ipFile ? $ipFile : MAGENTO_BP . '/var/.maintenance.ip';
        $this->status = $status ? $status : new Status();
    }

    /**
     * Check whether Magento maintenance mode is on.
     *
     * @return bool
     */
    public function isOn()
    {
        return file_exists($this->flagFile);
    }

    /**
     * Set maintenance mode.
     *
     * @param bool $isOn
     * @return $this
     * @throws \RuntimeException
     */
    public function set($isOn)
    {
        if ($isOn) {
            if (touch($this->flagFile)) {
                $this->status->add("Magento maintenance mode is enabled.", \Psr\Log\LogLevel::INFO);
            } else {
                throw new \RuntimeException("Magento maintenance mode cannot be enabled.");
            }
        } else if (file_exists($this->flagFile)) {
            if (file_exists($this->ipFile)) {
                /** Maintenance mode should not be unset from updater application if it was set manually by the admin */
                $this->status->add(
                    "Magento maintenance mode was not disabled. It can be disabled from the Magento Backend.",
                    \Psr\Log\LogLevel::INFO
                );
            } else if (unlink($this->flagFile)) {
                $this->status->add("Magento maintenance mode is disabled.", \Psr\Log\LogLevel::INFO);
            } else {
                throw new \RuntimeException("Magento maintenance mode cannot be disabled.");
            }
        }
        return $this;
    }
}
