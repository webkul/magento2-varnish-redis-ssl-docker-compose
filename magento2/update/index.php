<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

if (PHP_SAPI != 'cli') {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8') === false) {
        $xssHeaderValue = '1; mode=block';
    } else {
        $xssHeaderValue = '0';
    }
    header('X-XSS-Protection: ' . $xssHeaderValue);
}

$status = new \Magento\Update\Status();

$isUpdateInProgress = $status->isUpdateInProgress();
$statusMessage = '';
$statusMessage .= $status->get();
$statusMessage = str_replace("\n", "<br />", $statusMessage);
$queue =  new \Magento\Update\Queue();
$pending = !$status->isUpdateInProgress() && !$queue->isEmpty() && !$status->isUpdateError();

if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
    if($_SERVER['PATH_INFO'] === '/rollback' && file_exists(MAGENTO_BP . '/var/.update_error.flag')) {
        try {
            $queue->clear();
            $backupInfo = new \Magento\Update\Backup\BackupInfo();

            $backupPaths = $backupInfo->getBackupFilePaths();
            if (isset($backupPaths['error'])) {
                $status->add('WARNING: There is a problem with backup files! Performing rollback from these'
                    . ' files may cause the Magento application to be unstable', \Psr\Log\LogLevel::WARNING);
                foreach ($backupPaths['error'] as $error) {
                    $status->add($error, \Psr\Log\LogLevel::WARNING);
                }
                unset($backupPaths['error']);
            }

            foreach (array_values($backupPaths) as $backupPath) {
                $queue->addJobs(
                    ['jobs' =>
                        [
                            'name' => $backupPath['type'],
                            'params'=> ['backup_file_name' => $backupPath['filename']]
                        ]
                    ]
                );
            }

            $status->setUpdateError(false);
        } catch (\Exception $e) {
            $status->setUpdateError(true);
            $status->add('Error in Rollback:' . $e->getMessage(), \Psr\Log\LogLevel::ERROR);
        }
    } elseif ($_SERVER['PATH_INFO'] === '/status') {
        $complete = !$status->isUpdateInProgress() && $queue->isEmpty() && !$status->isUpdateError();
        if ($complete) {
            $status->clear();
        }
        echo json_encode(
            [
                'statusMessage' => $statusMessage,
                'isUpdateInProgress' => $isUpdateInProgress,
                'complete' => $complete,
                'error' => $status->isUpdateError(),
                'pending' => $pending,
            ]
        );
    }
} else {
    if (!file_exists(MAGENTO_BP . '/app/etc/config.php') || !file_exists(MAGENTO_BP . '/app/etc/env.php')) {
        header('Location: ../setup');
        die();
    }
    $type = 'default';
    $titles = [];
    $defaultHeaderTitle = 'Magento Updater';
    if (file_exists(MAGENTO_BP . '/var/.type.json')) {
        $typeFlag = json_decode(file_get_contents(MAGENTO_BP . '/var/.type.json'), true);
        $headerTitle = isset($typeFlag['headerTitle']) ? $typeFlag['headerTitle'] : $defaultHeaderTitle;
        $titles = $typeFlag['titles'];
    } else {
        $headerTitle = $defaultHeaderTitle;
    }
    include __DIR__ . '/app/code/Magento/Update/view/templates/status.phtml';
}
