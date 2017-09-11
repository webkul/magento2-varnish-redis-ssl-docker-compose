<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Update\Queue\Reader
     */
    protected $readerReader;

    /**
     * @var string
     */
    protected $validQueueFilePath;

    /**
     * @var string
     */
    protected $tmpQueueFilePath;

    protected function setUp()
    {
        parent::setUp();
        $this->validQueueFilePath = __DIR__ . '/../_files/update_queue_valid.json';
        $this->tmpQueueFilePath = TESTS_TEMP_DIR . '/update_queue_valid.json';

        /** Prepare temporary queue file which can be modified */
        $queueFileContent = file_get_contents($this->validQueueFilePath);
        file_put_contents($this->tmpQueueFilePath, $queueFileContent);
        /** Make sure it was created */
        $this->assertEquals($queueFileContent, file_get_contents($this->tmpQueueFilePath), "Precondition failed.");
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->tmpQueueFilePath)) {
            unlink($this->tmpQueueFilePath);
        }
    }

    public function testWrite()
    {
        $writer = new Writer($this->tmpQueueFilePath);
        $writer->write('{"jobs": []}');
        $expectedQueueFileContent = file_get_contents($this->tmpQueueFilePath);
        $this->assertEquals($expectedQueueFileContent, $writer->read());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Content to write must be a valid JSON.
     */
    public function testWriteInvalidJson()
    {
        $writer = new Writer($this->tmpQueueFilePath);
        $writer->write('invalid json string');
    }

    public function testWriteFileDoesNotExist()
    {
        $invalidFilePath = 'invalidpath';
        $writer = new Writer($invalidFilePath);
        $this->assertFalse($writer->write('{jobs: []}'));
    }
}
