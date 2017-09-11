<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class RollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Update\Rollback
     */
    protected $rollBack;

    /**
     * @var string
     */
    protected $backupPath;

    /**
     * @var string
     */
    protected $archivedDir;

    /**
     * @var string
     */
    protected $excludedDir;

    /**
     * @var string
     */
    protected $backupFileName;

    /**
     * @var \PharData
     */
    protected $backupFile;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Update\Backup\BackupInfo
     */
    protected $backupInfo;

    protected function setup()
    {
        parent::setUp();
        $this->backupPath = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/backup/';
        $this->archivedDir = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/archived/';
        $this->excludedDir = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/archived/excluded/';

        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath);
        }
        if (!is_dir($this->archivedDir)) {
            mkdir($this->archivedDir);
        }
        if (!is_dir($this->excludedDir)) {
            mkdir($this->excludedDir);
        }

        $this->backupFileName = $this->backupPath . '/../' . uniqid() . '_code.tar';
        $this->backupFile = new \PharData($this->backupFileName);
        $this->backupInfo = $this->getMock('Magento\Update\Backup\BackupInfo', [], [], '', false);
        $this->rollBack = new \Magento\Update\Rollback($this->backupPath, $this->archivedDir, null, $this->backupInfo);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->autoRollbackHelper(2);
        if (file_exists($this->backupFileName)) {
            unlink($this->backupFileName);
        }
        $gtzFile = str_replace('tar', 'tgz', $this->backupFileName);
        if (file_exists($gtzFile)) {
            unlink($gtzFile);
        }

        if (is_dir($this->backupPath)) {
            rmdir($this->backupPath);
        }
        if (is_dir($this->excludedDir)) {
            rmdir($this->excludedDir);
        }
        if (is_dir($this->archivedDir)) {
            rmdir($this->archivedDir);
        }
        if (file_exists(MAGENTO_BP . '/var/.update_status.txt')) {
            unlink(MAGENTO_BP . '/var/.update_status.txt');
        }
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testAutoRollbackBackupFileUnavailable()
    {
        $this->rollBack->execute('someInvalidfile');
    }

    public function testAutoRollback()
    {
        // Setup
        $this->autoRollbackHelper();

        $this->backupFile->buildFromDirectory($this->archivedDir);
        $this->backupFile->addEmptyDir("testDirectory");
        $this->backupFile->compress(\Phar::GZ, '.tgz');

        $newFile = $this->backupPath . '/' . uniqid() . '_code.tgz';
        copy($this->backupFileName, $newFile);
        if (file_exists($this->backupFileName)) {
            $this->backupFile = null;
            unlink($this->backupFileName);
        }
        $gtzFile = str_replace('tar', 'tgz', $this->backupFileName);
        if (file_exists($gtzFile)) {
            unlink($gtzFile);
        }
        $this->backupFileName = $newFile;

        // Change the contents of a.txt
        $this->autoRollbackHelper(1);
        $this->assertEquals('foo changed', file_get_contents($this->archivedDir . 'a.txt'));

        $this->backupInfo->expects($this->once())->method('getBlacklist')->willReturn(['excluded']);
        // Rollback process
        $this->rollBack->execute($this->backupFileName);

        // Assert that the contents of a.txt has been restored properly
        $this->assertEquals('foo', file_get_contents($this->archivedDir . 'a.txt'));
    }

    /**
     * Helper to create simple files
     *
     * @param int $flag
     */
    protected function autoRollbackHelper($flag = 0)
    {
        $fileA = 'a.txt';
        $fileB = 'b.txt';
        $fileC = 'c.txt';

        if ($flag === 0) {
            file_put_contents($this->archivedDir . $fileA, 'foo');
            file_put_contents($this->archivedDir . $fileB, 'bar');
            file_put_contents($this->archivedDir . $fileC, 'baz');
        } elseif ($flag === 1) {
            file_put_contents($this->archivedDir . $fileA, 'foo changed');
        } elseif ($flag === 2) {
            if (file_exists($this->archivedDir . $fileA)) {
                unlink($this->archivedDir . $fileA);
            }
            if (file_exists($this->archivedDir . $fileB)) {
                unlink($this->archivedDir . $fileB);
            }
            if (file_exists($this->archivedDir . $fileC)) {
                unlink($this->archivedDir . $fileC);
            }
        }
    }
}
