<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class MaintenanceModeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to the maintenance flag file
     *
     * @var string
     */
    protected $flagFile;

    /**
     * Path to the IP addresses file
     *
     * @var string
     */
    protected $ipFile;

    /**
     * @var MaintenanceMode
     */
    protected $maintenanceMode;

    protected function setUp()
    {
        $this->flagFile = TESTS_TEMP_DIR . '/.maintenance.flag';
        $this->ipFile = TESTS_TEMP_DIR . '/.maintenance.ip';

        $this->maintenanceMode = new \Magento\Update\MaintenanceMode($this->flagFile, $this->ipFile);
        if (file_exists($this->flagFile)) {
            unlink($this->flagFile);
        }
        if (file_exists($this->ipFile)) {
            unlink($this->ipFile);
        }
    }

    protected function tearDown()
    {
        if (file_exists($this->flagFile)) {
            unlink($this->flagFile);
        }
        if (file_exists($this->ipFile)) {
            unlink($this->ipFile);
        }
        if (file_exists(MAGENTO_BP . '/var/.update_status.txt')) {
            unlink(MAGENTO_BP . '/var/.update_status.txt');
        }
    }

    public function testFlow()
    {
        $this->assertFalse($this->maintenanceMode->isOn());

        /** Successfully set maintenance mode */
        $this->maintenanceMode->set(true);
        $this->assertTrue($this->maintenanceMode->isOn());

        /** Successfully disable maintenance mode */
        $this->maintenanceMode->set(false);
        $this->assertFalse($this->maintenanceMode->isOn());

        /** Test case when maintenance mode cannot be disabled from the updater application */
        $this->maintenanceMode->set(true);
        file_put_contents($this->ipFile, '');
        $this->maintenanceMode->set(false);
        $this->assertTrue($this->maintenanceMode->isOn());
    }
}
