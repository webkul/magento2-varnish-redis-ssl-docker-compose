<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class StatusCheckTest extends \PHPUnit_Framework_TestCase
{
    const REQUEST_TYPE_AJAX = 'ajax';

    /**
     * @var string
     */
    protected $indexScript;

    /**
     * @var \Magento\Update\Status
     */
    protected $status;

    /**
     * @var string
     */
    protected $uniqueMessage;

    protected function setUp()
    {
        $this->indexScript = UPDATER_BP . '/index.php';
        $this->status = new \Magento\Update\Status();
        $this->status->clear();
        $this->uniqueMessage = 'Test Message' . uniqid();
    }

    protected function tearDown()
    {
        $this->status->clear();
        unset($this->uniqueMessage);
    }

    /**
     * @param bool $isInProgress
     * @param string $statusMessage
     * @dataProvider progressStatusDataProvider
     */
    public function testStatusCheck($isInProgress, $statusMessage)
    {
        $this->status->add($this->uniqueMessage);
        $this->status->setUpdateInProgress($isInProgress);
        $actualResponse = $this->getResponse();

        $this->assertContains($this->uniqueMessage, $actualResponse);
        $this->assertContains($statusMessage, $actualResponse);
    }

    /**
     * @param bool $isInProgress
     * @dataProvider progressStatusDataProvider
     */
    public function testStatusCheckAjax($isInProgress)
    {
        $this->status->add($this->uniqueMessage);
        $this->status->setUpdateInProgress($isInProgress);
        $actualResponse = json_decode($this->getResponse(self::REQUEST_TYPE_AJAX), true);

        $this->assertInternalType('array', $actualResponse);
        $this->assertArrayHasKey('statusMessage', $actualResponse);
        $this->assertArrayHasKey('isUpdateInProgress', $actualResponse);
        $this->assertContains($this->uniqueMessage, $actualResponse['statusMessage']);
        $this->assertEquals($isInProgress, $actualResponse['isUpdateInProgress']);
    }

    /**
     * @return array
     */
    public function progressStatusDataProvider()
    {
        return [
            'isRunning' => [
                'isInProgress' => true,
                'statusMessage' => 'Update application is running'
            ],
            'isNotRunning' => [
                'isInProgress' => false,
                'statusMessage' => 'Update application is not running'
            ],
        ];
    }

    /**
     * Return response of index.php, according to the request type
     *
     * @param string|null $requestType
     * @return string
     */
    protected function getResponse($requestType = null)
    {
        if ($requestType === self::REQUEST_TYPE_AJAX) {
            $_SERVER['PATH_INFO'] = '/status';
        }
        ob_start();
        include $this->indexScript;
        $response = ob_get_contents();
        ob_end_clean();
        unset($_SERVER['PATH_INFO']);
        return $response;
    }
}
