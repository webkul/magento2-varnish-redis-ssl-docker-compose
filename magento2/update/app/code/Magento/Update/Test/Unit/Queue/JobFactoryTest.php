<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

use Magento\Update\Queue\JobFactory;

class JobFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobFactory
     */
    protected $jobFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->jobFactory = new JobFactory();
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreate($jobName, $expectedJobClass)
    {
        $job = $this->jobFactory->create($jobName, []);
        $this->assertInstanceOf($expectedJobClass, $job);
    }

    public function createProvider()
    {
        return [
            'Update Job' => [JobFactory::NAME_UPDATE, '\Magento\Update\Queue\JobUpdate'],
            'Backup Job' => [JobFactory::NAME_BACKUP, '\Magento\Update\Queue\JobBackup'],
            'Rollback Job' => [JobFactory::NAME_ROLLBACK, '\Magento\Update\Queue\JobRollback'],
            'Remove backups Job' => [JobFactory::NAME_REMOVE_BACKUPS, '\Magento\Update\Queue\JobRemoveBackups'],
            'Uninstall Job' => [JobFactory::NAME_UNINSTALL, '\Magento\Update\Queue\JobComponentUninstall'],
        ];
    }

    public function testCreateInvalidJob()
    {
        $this->setExpectedException(
            '\RuntimeException',
            '"invalid" job is not supported.'
        );
        $this->jobFactory->create('invalid', []);
    }
}
