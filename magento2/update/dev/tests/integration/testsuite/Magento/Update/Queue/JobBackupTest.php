<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

use Magento\Update\MaintenanceMode;

class JobBackupTest extends \PHPUnit_Framework_TestCase
{
    /** @var  string */
    protected $backupFilename;

    /** @var  string */
    protected $backupPath;

    /** @var array */
    protected $dirList = [];

    protected function setUp()
    {
        parent::setUp();
        $this->backupFilename = uniqid('test_backup') . '.zip';
        $this->backupPath = TESTS_TEMP_DIR . '/backup/';
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath);
        }
    }

    protected function tearDown()
    {
        if (is_dir($this->backupPath)) {
            rmdir($this->backupPath);
        }
        if (file_exists(TESTS_TEMP_DIR . '/maintenanceMode.flag')) {
            unlink(TESTS_TEMP_DIR . '/maintenanceMode.flag');
        }
        if (file_exists(TESTS_TEMP_DIR . '/maintenanceAddress.flag')) {
            unlink(TESTS_TEMP_DIR . '/maintenanceAddress.flag');
        }
        parent::tearDown();
    }

    public function testArchive()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $this->markTestSkipped();
        }
        $jobName = 'Backup';
        $jobStatus = new \Magento\Update\Status();
        $jobStatus->clear();

        $backupInfo = $this->getMockBuilder('Magento\Update\Backup\BackupInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $backupInfo->expects($this->any())
            ->method('generateBackupFilename')
            ->willReturn($this->backupFilename);
        $backupInfo->expects($this->any())
            ->method('getArchivedDirectory')
            ->willReturn(UPDATER_BP);
        $backupInfo->expects($this->any())
            ->method('getBlacklist')
            ->willReturn(['var/backup', 'vendor', 'app/code']);
        $backupInfo->expects($this->any())
            ->method('getBackupPath')
            ->willReturn($this->backupPath);

        $maintenanceMode = new MaintenanceMode(
            TESTS_TEMP_DIR . '/maintenanceMode.flag',
            TESTS_TEMP_DIR . '/maintenanceAddress.flag'
        );
        $jobBackup = new \Magento\Update\Queue\JobBackup($jobName, [], $jobStatus, $maintenanceMode, $backupInfo);
        $this->dirList = scandir($this->backupPath);

        $jobBackup->execute();

        $tmpFiles = array_diff(scandir($this->backupPath), $this->dirList);
        $actualBackupFile = array_pop($tmpFiles);
        $this->assertEquals($this->backupFilename, $actualBackupFile);

        $actualJobStatus = $jobStatus->get();
        $fullBackupFileName = $this->backupPath . $this->backupFilename;
        $this->assertContains(sprintf('Creating backup archive "%s" ...', $fullBackupFileName), $actualJobStatus);
        $this->assertContains(sprintf('Backup archive "%s" has been created.', $fullBackupFileName), $actualJobStatus);

        if (file_exists($this->backupPath . $actualBackupFile)) {
            unlink($this->backupPath . $actualBackupFile);
        }
    }
}
