<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

/**
 * Update cron exit codes
 */
const UPDATE_CRON_NORMAL_EXIT = 0;
const UPDATE_CRON_EXIT_WITH_ERROR = 1;

$status = new \Magento\Update\Status();
$cronReadinessChecker = new \Magento\Update\CronReadinessCheck();
$notification = 'update-cron: Please check var/log/update.log for execution summary.' . PHP_EOL;

if (!$cronReadinessChecker->runReadinessCheck()) {
    print $notification;
    exit(UPDATE_CRON_EXIT_WITH_ERROR);
}

if ($status->isUpdateInProgress()) {
    $status->add('Update is already in progress.', \Psr\Log\LogLevel::WARNING);
    print $notification;
    exit(UPDATE_CRON_EXIT_WITH_ERROR);
}

if ($status->isUpdateError()) {
    $status->add('There was an error in previous Update attempt.');
    print $notification;
    exit(UPDATE_CRON_EXIT_WITH_ERROR);
}

$backupDirectory = BACKUP_DIR;
if (!file_exists($backupDirectory)) {
    if (!mkdir($backupDirectory)) {
        $status->add(sprintf('Backup directory, "%s" cannot be created.', $backupDirectory), \Psr\Log\LogLevel::ERROR);
        print $notification;
        exit(UPDATE_CRON_EXIT_WITH_ERROR);
    }
    chmod($backupDirectory, 0770);
}

try {
    $status->setUpdateInProgress();
} catch (\RuntimeException $e) {
    $status->add($e->getMessage(), \Psr\Log\LogLevel::ERROR);
    print $notification;
    exit(UPDATE_CRON_EXIT_WITH_ERROR);
}

$jobQueue = new \Magento\Update\Queue();
$exitCode = UPDATE_CRON_NORMAL_EXIT;
try {
    while (!empty($jobQueue->peek()) &&
        strpos($jobQueue->peek()[\Magento\Update\Queue::KEY_JOB_NAME], 'setup:') === false
    ) {
        $job = $jobQueue->popQueuedJob();
        $status->add(
            sprintf('Job "%s" has been started', $job)
        );
        try {
            $job->execute();
            $status->add(sprintf('Job "%s" has successfully completed', $job), \Psr\Log\LogLevel::INFO);
        } catch (\Exception $e) {
            $status->setUpdateError();
            $status->add(
                sprintf(
                    'An error occurred while executing job "%s": %s', $job, $e->getMessage(),
                    \Psr\Log\LogLevel::ERROR
                )
            );
            $status->setUpdateInProgress(false);
            $exitCode = UPDATE_CRON_EXIT_WITH_ERROR;
        };
    }
} catch (\Exception $e) {
    $status->setUpdateError();
    $status->add($e->getMessage(), \Psr\Log\LogLevel::ERROR);
    $exitCode = UPDATE_CRON_EXIT_WITH_ERROR;
} finally {
    $status->setUpdateInProgress(false);
    if ($exitCode != UPDATE_CRON_NORMAL_EXIT) {
        print $notification;
    }
    exit($exitCode);
}
