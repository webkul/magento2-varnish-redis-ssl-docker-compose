<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class CronReadinessCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var bool
     */
    static public $writable = true;

    /**
     * @var string
     */
    private $setupCronJobStatusFilePath;

    /**
     * @var string
     */
    private $cronJobStatusFilePath;

    protected function setUp()
    {
        $this->setupCronJobStatusFilePath = MAGENTO_BP . '/var/' . CronReadinessCheck::SETUP_CRON_JOB_STATUS_FILE;
        file_put_contents(
            $this->setupCronJobStatusFilePath,
            json_encode([CronReadinessCheck::KEY_FILE_PATHS => [CronReadinessCheck::KEY_LIST => [__FILE__]]])
        );

        $this->cronJobStatusFilePath = MAGENTO_BP . '/var/' . CronReadinessCheck::CRON_JOB_STATUS_FILE;
        file_put_contents(
            $this->cronJobStatusFilePath,
            json_encode([CronReadinessCheck::KEY_CURRENT_TIMESTAMP => 150])
        );
    }

    public function tearDown()
    {
        if (file_exists($this->setupCronJobStatusFilePath)) {
            unlink($this->setupCronJobStatusFilePath);
        }
        if (file_exists($this->cronJobStatusFilePath)) {
            unlink($this->cronJobStatusFilePath);
        }
    }

    public function testRunReadinessCheckNotWritable()
    {
        $cronReadinessCheck = new CronReadinessCheck();
        self::$writable = false;
        $this->assertFalse($cronReadinessCheck->runReadinessCheck());

        $file = fopen($this->cronJobStatusFilePath, 'r');
        $data = fread($file, filesize($this->cronJobStatusFilePath));
        $json = json_decode($data, true);
        $expected = [
            CronReadinessCheck::KEY_READINESS_CHECKS => [
                CronReadinessCheck::KEY_FILE_PERMISSIONS_VERIFIED => false,
            ],
            CronReadinessCheck::KEY_LAST_TIMESTAMP => 150,
            CronReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
        ];
        $errorMessage = $json[CronReadinessCheck::KEY_READINESS_CHECKS]['error'];
        unset($json[CronReadinessCheck::KEY_READINESS_CHECKS]['error']);
        $this->assertEquals($expected, $json);
        $this->assertContains('Found non-writable path(s):<br/>', $errorMessage);
    }

    public function testRunReadinessCheck()
    {
        $cronReadinessCheck = new CronReadinessCheck();
        self::$writable = true;
        $this->assertTrue($cronReadinessCheck->runReadinessCheck());
        $file = fopen($this->cronJobStatusFilePath, 'r');
        $data = fread($file, filesize($this->cronJobStatusFilePath));
        $json = json_decode($data, true);
        $expected = [
            CronReadinessCheck::KEY_READINESS_CHECKS => [CronReadinessCheck::KEY_FILE_PERMISSIONS_VERIFIED => true],
            CronReadinessCheck::KEY_LAST_TIMESTAMP => 150,
            CronReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
        ];
        sort($expected);
        sort($json);
        $this->assertEquals($expected, $json);
    }
}

function time()
{
    return 200;
}

function is_writable()
{
    return CronReadinessCheckTest::$writable;
}
