<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class AbstractJobTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        /** Any implementation of abstract job can be used for __toString testing */
        $job = new \Magento\Update\Queue\JobBackup(
            'backup',
            ['targetArchivePath' => '/Users/john/archive.zip', 'sourceDirectory' => '/Users/john/Magento']
        );
        $this->assertEquals(
            'backup {"targetArchivePath":"/Users/john/archive.zip","sourceDirectory":"/Users/john/Magento"}',
            (string)$job
        );
    }
}
