<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

use Magento\Update\Queue\Reader;
use Magento\Update\Queue\AbstractJob;
use Magento\Update\Queue\JobFactory;
use Magento\Update\Queue\Writer;

/**
 * Class for access to the queue of Magento updater application jobs.
 */
class Queue
{
    /**#@+
     * Key used in queue file.
     */
    const KEY_JOBS = 'jobs';
    const KEY_JOB_NAME = 'name';
    const KEY_JOB_PARAMS = 'params';
    /**#@-*/

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var JobFactory
     */
    protected $jobFactory;

    /**
     * Initialize dependencies.
     *
     * @param Reader|null $reader
     * @param Writer|null $writer
     * @param JobFactory|null $jobFactory
     */
    public function __construct(Reader $reader = null, Writer $writer = null, JobFactory $jobFactory = null)
    {
        $this->reader = $reader ? $reader : new Reader();
        $this->writer = $writer ? $writer : new Writer();
        $this->jobFactory = $jobFactory ? $jobFactory : new JobFactory();
    }

    /**
     * Peek at job queue
     *
     * @throws \RuntimeException
     * @return array
     */
    public function peek()
    {
        $queue = json_decode($this->reader->read(), true);
        if (!is_array($queue)) {
            return [];
        }
        if (isset($queue[self::KEY_JOBS]) && is_array($queue[self::KEY_JOBS])) {
            $this->validateJobDeclaration($queue[self::KEY_JOBS][0]);
            return $queue[self::KEY_JOBS][0];
        } else {
            throw new \RuntimeException(sprintf('"%s" field is missing or is not an array.', self::KEY_JOBS));
        }
    }

    /**
     * Pop job queue.
     *
     * @return AbstractJob
     * @throws \RuntimeException
     */
    public function popQueuedJob()
    {
        $job = null;
        $queue = json_decode($this->reader->read(), true);
        if (!is_array($queue)) {
            return $job;
        }
        if (isset($queue[self::KEY_JOBS]) && is_array($queue[self::KEY_JOBS])) {
            $this->validateJobDeclaration($queue[self::KEY_JOBS][0]);
            $job = $this->jobFactory->create(
                $queue[self::KEY_JOBS][0][self::KEY_JOB_NAME],
                $queue[self::KEY_JOBS][0][self::KEY_JOB_PARAMS]
            );
            array_shift($queue[self::KEY_JOBS]);
            if (empty($queue[self::KEY_JOBS])) {
                $this->writer->write('');
            } else {
                $this->writer->write(json_encode($queue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ));
            }
        } else {
            throw new \RuntimeException(sprintf('"%s" field is missing or is not an array.', self::KEY_JOBS));
        }
        return $job;
    }

    /**
     * Check if queue is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        $queue = json_decode($this->reader->read(), true);
        return empty($queue);
    }

    /**
     * @param array $jobs
     * @return void
     */
    public function addJobs(array $jobs)
    {
        foreach ($jobs as $job) {
            $this->validateJobDeclaration($job);
            $queue = json_decode($this->reader->read(), true);
            $queue[self::KEY_JOBS][] = $job;
            $this->writer->write(json_encode($queue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ));
        }
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->writer->write('');
    }

    /**
     * Make sure job declaration is correct.
     *
     * @param object $job
     * @throws \RuntimeException
     */
    protected function validateJobDeclaration($job)
    {
        $requiredFields = [self::KEY_JOB_NAME, self::KEY_JOB_PARAMS];
        foreach ($requiredFields as $field) {
            if (!isset($job[$field])) {
                throw new \RuntimeException(sprintf('"%s" field is missing for one or more jobs.', $field));
            }
        }
    }
}
