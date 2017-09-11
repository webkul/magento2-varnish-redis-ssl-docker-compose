<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

/**
 * This class is used by Updater cron script to check if it can be run properly
 */
class CronReadinessCheck
{
    /**
     * Basename to Updater status file
     */
    const CRON_JOB_STATUS_FILE = '.update_cronjob_status';

    const UPDATE_CRON_LOG_FILE = 'var/log/update.log';

    /**#@+
     * Keys used in status file
     */
    const KEY_READINESS_CHECKS = 'readiness_checks';
    const KEY_FILE_PERMISSIONS_VERIFIED = 'file_permissions_verified';
    const KEY_ERROR = 'error';
    const KEY_CURRENT_TIMESTAMP = 'current_timestamp';
    const KEY_LAST_TIMESTAMP = 'last_timestamp';
    /**#@-*/

    /**
     *  Setup cron job status file name
     */
    const SETUP_CRON_JOB_STATUS_FILE = '.setup_cronjob_status';

    /**#@+
     *  Keys from .setup_cronjob_status file
     */
    const KEY_FILE_PATHS = 'file_paths';
    const KEY_LIST = 'list';
    /**#@-*/

    /**
     * Run Cron job readiness check
     *
     * @return bool
     */
    public function runReadinessCheck()
    {
        $resultJsonRawData = ['readiness_checks' => []];
        $success = true;

        $permissionInfo = $this->checkPermissionsRecursively();
        
        if ($permissionInfo->containsPaths())
        {
            $error = '';
            $outputString = '';
            if (!empty($permissionInfo->getNonWritablePaths())) {
                $error .= '<br/>Found non-writable path(s):<br/>' .
                    implode('<br/>', $permissionInfo->getNonWritablePaths());
                $outputString = 'Cron readiness check failure! Found non-writable paths:'
                    . PHP_EOL
                    . "\t"
                    . implode(PHP_EOL . "\t", $permissionInfo->getNonWritablePaths());
            }
            if (!empty($permissionInfo->getNonReadablePaths())) {
                $error .= '<br/>Found non-readable path(s):<br/>' .
                    implode('<br/>', $permissionInfo->getNonReadablePaths());
                $outputString .= PHP_EOL
                    . 'Cron readiness check failure! Found non-readable paths:'
                    . PHP_EOL
                    . "\t"
                    . implode(PHP_EOL . "\t", $permissionInfo->getNonReadablePaths());
            }
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_ERROR] = $error;
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_FILE_PERMISSIONS_VERIFIED] = false;
            $success = false;
        } else {
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_FILE_PERMISSIONS_VERIFIED] = true;
        }

        if (file_exists(MAGENTO_BP . '/var/' . self::CRON_JOB_STATUS_FILE)) {
            $jsonData = json_decode(file_get_contents(MAGENTO_BP . '/var/' . self::CRON_JOB_STATUS_FILE), true);
            if (isset($jsonData[self::KEY_CURRENT_TIMESTAMP])) {
                $resultJsonRawData[self::KEY_LAST_TIMESTAMP] = $jsonData[self::KEY_CURRENT_TIMESTAMP];
            }
        }
        $resultJsonRawData[self::KEY_CURRENT_TIMESTAMP] = time();

        $resultJson = json_encode($resultJsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(MAGENTO_BP . '/var/' . self::CRON_JOB_STATUS_FILE, $resultJson);

        // If non-accessible paths are found, log an 'error' entry for the same in update.log
        if ( !$success && !empty($outputString) ) {
            $updateLoggerFactory = new UpdateLoggerFactory();
            $logger = $updateLoggerFactory->create();
            $logger->log(\Psr\Log\LogLevel::ERROR, $outputString);
        }
        return $success;
    }

    /**
     * Check file permissions recursively
     *
     * @return PermissionInfo
     */
    private function checkPermissionsRecursively()
    {
        // For backward compatibility, initialize the list wth magento root directory.
        $dirAndFileList[] = '';

        // Get the list of magento specific directories and files
        $setupCronJobStatusFilePath = MAGENTO_BP . '/var/' . self::SETUP_CRON_JOB_STATUS_FILE;
        if (is_readable($setupCronJobStatusFilePath)) {
            $fileContents = json_decode(file_get_contents($setupCronJobStatusFilePath), true);

            if (isset($fileContents[self::KEY_FILE_PATHS][self::KEY_LIST])) {
                $dirAndFileList = $fileContents[self::KEY_FILE_PATHS][self::KEY_LIST];
            }
        }

        $nonWritablePaths = [];
        $nonReadablePaths = [];
        foreach ($dirAndFileList as $path) {
            $path = MAGENTO_BP . '/' . $path;
            if (is_dir($path)) {
                try {
                    $filesystemIterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($path),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                    $filesystemIterator = new ExcludeFilter(
                        $filesystemIterator,
                        [
                            MAGENTO_BP . '/update',
                            MAGENTO_BP . '/var/session',
                            '.git',
                            '.idea'
                        ]
                    );
                    foreach ($filesystemIterator as $item) {
                        $path = $item->__toString();
                        if (!is_writable($path)) {
                            $nonWritablePaths[] = $path;
                        }
                    }
                } catch (\UnexpectedValueException $e) {
                    $nonReadablePaths[] = $path;
                }
            } else {
                if (!is_writable($path)) {
                    $nonWritablePaths[] = $path;
                }
            }
        }
        return new PermissionInfo($nonWritablePaths, $nonReadablePaths);
    }
}
