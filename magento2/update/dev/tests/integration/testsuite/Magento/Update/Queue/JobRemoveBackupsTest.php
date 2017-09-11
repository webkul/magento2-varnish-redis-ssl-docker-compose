<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

use Magento\Update\Status;

class JobRemoveBackupsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Update\Queue\JobRemoveBackups */
    protected $jobRemoveBackup;

    /** @var string */
    protected $backupFilename;

    /** @var string */
    protected $backupFilenameA;

    /** @var string */
    protected $backupFilenameB;

    /** @var string */
    protected $backupFilenameC;

    /** @var string */
    protected $backupPath;

    /** @var string */
    protected $maintenanceFlagFilePath;

    /** @var string */
    protected $maintenanceAddressFlag;

    /** @var string */
    protected $updateErrorFlagFilePath;

    protected function setUp()
    {
        parent::setUp();
        $this->backupFilenameA = uniqid('test_backupA') . '.zip';
        $this->backupFilenameB = uniqid('test_backupB') . '.zip';
        $this->backupFilenameC = uniqid('test_backupC') . '.zip';
        $this->backupPath = TESTS_TEMP_DIR . '/backup/';
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath);
        }
        $this->maintenanceFlagFilePath = TESTS_TEMP_DIR . '/.maintenance.flag';
        $this->maintenanceAddressFlag = $this->maintenanceAddressFlag;
        $this->updateErrorFlagFilePath = TESTS_TEMP_DIR . '/.update_error.flag';
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->backupPath . $this->backupFilenameA)) {
            unlink($this->backupPath . $this->backupFilenameA);
        }
        if (file_exists($this->backupPath . $this->backupFilenameB)) {
            unlink($this->backupPath . $this->backupFilenameB);
        }
        if (file_exists($this->backupPath . $this->backupFilenameC)) {
            unlink($this->backupPath . $this->backupFilenameC);
        }
        if (is_dir($this->backupPath)) {
            rmdir($this->backupPath);
        }
        if (file_exists($this->maintenanceFlagFilePath)) {
            unlink($this->maintenanceFlagFilePath);
        }
        if (file_exists($this->updateErrorFlagFilePath)) {
            unlink($this->updateErrorFlagFilePath);
        }
    }

    /**
     * @param bool $isMaintenanceModeOn
     * @param bool $isUpdaterError
     * @dataProvider flagFileDataProvider
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot remove backup archives while setup is in progress.
     */
    public function testExecuteFlag($isMaintenanceModeOn, $isUpdaterError)
    {
        /** @var \Magento\Update\MaintenanceMode $maintenanceModeMock */
        $maintenanceModeMock = $this->getMockBuilder('Magento\Update\MaintenanceMode')
            ->disableOriginalConstructor()
            ->getMock();
        $maintenanceModeMock->expects($this->any())->method('isOn')->willReturn($isMaintenanceModeOn);
        /** @var \Magento\Update\Status $statusMock */
        $statusMock = $this->getMockBuilder('Magento\Update\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $statusMock->expects($this->any())->method('isUpdateError')->willReturn($isUpdaterError);
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            [$this->backupPath . $this->backupFilenameA],
            $statusMock,
            $maintenanceModeMock
        );
        $this->jobRemoveBackup->execute();
    }

    public function flagFileDataProvider()
    {
        return [
            "Updater error" => [false, true],
            "Maintenance mode on" => [true, true]
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not delete backup archive
     */
    public function testExecuteInvalidBackupFile()
    {
        $maintenanceMode = new \Magento\Update\MaintenanceMode(
            $this->maintenanceFlagFilePath,
            $this->maintenanceAddressFlag
        );
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            ['backups_file_names' => [$this->backupPath . 'no-such-file.zip']],
            new Status(),
            $maintenanceMode
        );
        $this->jobRemoveBackup->execute();
    }

    public function testExecuteSingle()
    {
        if (!file_exists($this->backupPath . $this->backupFilenameA)) {
            file_put_contents($this->backupPath . $this->backupFilenameA, '');
        }
        $maintenanceMode = new \Magento\Update\MaintenanceMode(
            $this->maintenanceFlagFilePath,
            $this->maintenanceAddressFlag
        );
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            ['backups_file_names' => [$this->backupPath . $this->backupFilenameA]],
            new \Magento\Update\Status(),
            $maintenanceMode
        );
        $this->jobRemoveBackup->execute();
        $this->assertFalse(file_exists($this->backupPath . $this->backupFilenameA));
    }

    public function testExecuteMultiple()
    {
        if (!file_exists($this->backupPath . $this->backupFilenameA)) {
            file_put_contents($this->backupPath . $this->backupFilenameA, '');
        }
        if (!file_exists($this->backupPath . $this->backupFilenameB)) {
            file_put_contents($this->backupPath . $this->backupFilenameB, '');
        }
        if (!file_exists($this->backupPath . $this->backupFilenameC)) {
            file_put_contents($this->backupPath . $this->backupFilenameC, '');
        }
        $maintenanceMode = new \Magento\Update\MaintenanceMode(
            $this->maintenanceFlagFilePath,
            $this->maintenanceAddressFlag
        );
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            [
                'backups_file_names' => [
                    $this->backupPath . $this->backupFilenameA,
                    $this->backupPath . $this->backupFilenameB
                ]
            ],
            new \Magento\Update\Status(),
            $maintenanceMode
        );
        $this->jobRemoveBackup->execute();
        $this->assertFalse(file_exists($this->backupPath . $this->backupFilenameA));
        $this->assertFalse(file_exists($this->backupPath . $this->backupFilenameB));
        $this->assertTrue(file_exists($this->backupPath . $this->backupFilenameC));
    }
}
