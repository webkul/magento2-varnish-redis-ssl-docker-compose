<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

use Magento\Update\Backup\BackupInfo;

class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $cronScript;

    /**
     * @var string
     */
    protected $backupToRollback;

    /**
     * @var string
     */
    protected $backupToRemoveA;

    /**
     * @var string
     */
    protected $backupToRemoveB;

    /**
     * @var \Magento\Update\Status
     */
    protected $status;

    /** @var string */
    protected $backupPath;

    protected function setUp()
    {
        $this->backupPath = TESTS_TEMP_DIR . '/var/backup';
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0777, true);
        }

        $this->cronScript = UPDATER_BP . '/dev/tests/integration/framework/cron.php';
        $this->backupToRollback = $this->backupPath . '/BackupToRollback.zip';
        $this->backupToRemoveA = $this->backupPath . '/BackupToRemoveA.zip';
        $this->backupToRemoveB = $this->backupPath . '/BackupToRemoveB.zip';
        $this->status = new \Magento\Update\Status();

        file_put_contents($this->backupToRollback, 'w');
        file_put_contents($this->backupToRemoveA, 'w');
        file_put_contents($this->backupToRemoveB, 'w');
    }


    protected function tearDown()
    {
        parent::tearDown();
        $this->status->setUpdateInProgress(false);
        $this->status->setUpdateError(false);
        if (file_exists($this->backupToRollback)) {
            unlink($this->backupToRollback);
        }
        if (file_exists($this->backupToRemoveA)) {
            unlink($this->backupToRemoveA);
        }
        if (file_exists($this->backupToRemoveB)) {
            unlink($this->backupToRemoveB);
        }
        array_map('unlink', glob($this->backupPath . '/*.zip'));
        if (is_dir($this->backupPath)) {
            rmdir($this->backupPath);
            rmdir(TESTS_TEMP_DIR . '/var');
        }
        if (file_exists(MAGENTO_BP . '/var/.update_queue.json')) {
            unlink(MAGENTO_BP . '/var/.update_queue.json');
        }
        if (file_exists(MAGENTO_BP . '/var/.update_status.txt')) {
            unlink(MAGENTO_BP . '/var/.update_status.txt');
        }
        if (file_exists(MAGENTO_BP . '/var/.update_cronjob_status')) {
            unlink(MAGENTO_BP . '/var/.update_cronjob_status');
        }
    }

    public function testUpdateInProgress()
    {
        $this->status->setUpdateInProgress();
        shell_exec('php -f ' . $this->cronScript);
        $jobStatus = $this->status->get();
        $this->assertContains('Update is already in progress.', $jobStatus);
    }

    public function testValidQueue()
    {
        $this->assertTrue(file_exists($this->backupToRollback));
        $this->assertTrue(file_exists($this->backupToRemoveA));
        $this->assertTrue(file_exists($this->backupToRemoveB));

        file_put_contents(MAGENTO_BP . '/var/.update_queue.json',
            '{
              "jobs": [
                {
                  "name": "remove_backups",
                  "params": {
                    "backups_file_names": [
                      "' . $this->backupToRemoveA . '",
                      "' . $this->backupToRemoveB . '"
                    ]
                  }
                }
              ],
              "ignored_field": ""
            }');
        shell_exec('php -f ' . $this->cronScript);

        $jobStatus = $this->status->get();
        // verify removals
        $this->assertNotContains('An error occurred while executing job "<remove_backups>"', $jobStatus);
        $this->assertContains('Job "remove_backups {"backups_file_names":["' .
            $this->backupToRemoveA . '","' . $this->backupToRemoveB .
            '"]}" has successfully completed', $jobStatus);
        $this->assertFalse(file_exists($this->backupToRemoveA));
        $this->assertFalse(file_exists($this->backupToRemoveB));
    }

    /**
     * Test invalid queue file
     *
     * @expectedException /RuntimeException
     * @expectedExceptionMessage RuntimeException: Missing job params "params" field is missing for one or more jobs
     */
    public function testInvalidQueue()
    {
        file_put_contents(MAGENTO_BP . '/var/.update_queue.json',
            '{
              "jobs": [
                {
                  "name": "backup"
                }
              ],
              "ignored_field": ""
            }');
        shell_exec('php -f ' . $this->cronScript);
        $jobStatus = $this->status->get();
        $this->assertContains('"params" field is missing for one or more jobs', $jobStatus);
    }
}
