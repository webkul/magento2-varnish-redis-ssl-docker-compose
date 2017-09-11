<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class JobRollbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $maintenanceFlagFilePath;

    /** @var string */
    protected $updateErrorFlagFilePath;

    protected function setUp()
    {
        parent::setUp();
        $this->maintenanceFlagFilePath = TESTS_TEMP_DIR . '/.maintenance.flag';
        $this->updateErrorFlagFilePath = TESTS_TEMP_DIR . '/.update_error.flag';
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->maintenanceFlagFilePath)) {
            unlink($this->maintenanceFlagFilePath);
        }
        if (file_exists($this->updateErrorFlagFilePath)) {
            unlink($this->updateErrorFlagFilePath);
        }
    }

    public function testManualRollbackBackupFileUnavailable()
    {
        $backupFileName = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/backup/' . 'fake.zip';
        $maintenanceMode = new \Magento\Update\MaintenanceMode(
            $this->maintenanceFlagFilePath,
            $this->updateErrorFlagFilePath
        );
        $jobRollback = new \Magento\Update\Queue\JobRollback(
            'rollback',
            ['backup_file_name' => $backupFileName],
            new \Magento\Update\Status(),
            $maintenanceMode
        );
        $this->setExpectedException(
            'RuntimeException',
            sprintf(
                'Cannot create phar \'%s\', file extension (or combination) not recognised'.
                ' or the directory does not exist',
                $backupFileName
            )
        );
        $jobRollback->execute();
    }
}
