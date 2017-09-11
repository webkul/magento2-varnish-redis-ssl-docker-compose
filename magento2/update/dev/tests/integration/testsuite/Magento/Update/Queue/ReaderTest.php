<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class ReaderTest extends \PHPUnit_Framework_TestCase
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
    protected $invalidQueueFilePath;

    /**
     * @var string
     */
    protected $tmpQueueFilePath;

    protected function setUp()
    {
        parent::setUp();
        $this->validQueueFilePath = __DIR__ . '/../_files/update_queue_valid.json';
        $this->invalidQueueFilePath = __DIR__ . '/../_files/update_queue_invalid.json';
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

    public function testRead()
    {
        $reader = new \Magento\Update\Queue\Reader($this->validQueueFilePath);
        $actualQueueFileContent = $reader->read();
        $expectedQueueFileContent = file_get_contents($this->validQueueFilePath);
        $this->assertEquals($expectedQueueFileContent, $actualQueueFileContent);
    }

    public function testReadFileDoesNotExist()
    {
        $invalidFilePath = 'invalidpath';
        $reader = new \Magento\Update\Queue\Reader($invalidFilePath);
        $actualQueueFileContent = $reader->read();
        $expectedQueueFileContent = '';
        $this->assertEquals($expectedQueueFileContent, $actualQueueFileContent);
    }

    public function testReadInvalidFileFormat()
    {
        $reader = new \Magento\Update\Queue\Reader($this->invalidQueueFilePath);
        $this->setExpectedException(
            '\RuntimeException',
            "Content of \"{$this->invalidQueueFilePath}\" must be a valid JSON."
        );
        $reader->read();
    }
}
