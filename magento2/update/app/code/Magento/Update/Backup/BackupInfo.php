<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup;

/**
 * Data object, which stores information about files to be archived.
 */
class BackupInfo
{
    /**#@+
     * Types of backup
     */
    const BACKUP_CODE = 'code';
    const BACKUP_MEDIA = 'media';
    const BACKUP_DB = 'db';
    /**#@-*/

    /**
     * @var string
     */
    protected $blacklistFilePath;

    /**
     * @var string[]
     */
    protected $blacklist;

    /**
     * Init backup info
     *
     * @param string $blacklistFilePath
     */
    public function __construct($blacklistFilePath = null)
    {
        $this->blacklistFilePath = $blacklistFilePath ? $blacklistFilePath : __DIR__ . '/../etc/backup_blacklist.txt';
    }

    /**
     * Generate backup filename based on current timestamp.
     *
     * @return string
     */
    public function generateBackupFilename()
    {
        $currentDate = gmdate('Y-m-d-H-i-s', time());
        return 'backup-' . $currentDate . '.zip';
    }

    /**
     * Return files/directories, which need to be excluded from backup
     *
     * @throws \RuntimeException
     * @return string[]
     */
    public function getBlacklist()
    {
        if (null === $this->blacklist) {
            $blacklistContent = file_get_contents($this->blacklistFilePath);
            if ($blacklistContent === FALSE) {
                throw new \RuntimeException('Could not read the blacklist file: ' . $this->blacklistFilePath);
            }
            /** Ignore commented and empty lines */
            $blacklistArray = explode("\n", $blacklistContent);
            $blacklistArray = array_filter(
                $blacklistArray,
                function ($value) {
                    $value = trim($value);
                    return (empty($value) || strpos($value, '#') === 0) ? false : true;
                }
            );
            $this->blacklist = $blacklistArray;
        }
        return $this->blacklist;
    }

    /**
     * Return path to a directory, which need to be archived
     *
     * @return string
     */
    public function getArchivedDirectory()
    {
        return MAGENTO_BP;
    }

    /**
     * Return path, where backup have to be saved
     *
     * @return string
     */
    public function getBackupPath()
    {
        return BACKUP_DIR;
    }

    /**
     * Returns list of backup file paths
     *
     * @return array
     */
    public function getBackupFilePaths()
    {
        $timeStamp = $this->getLastBackupFileTimestamp();
        $backupTypes = [self::BACKUP_CODE, self::BACKUP_MEDIA];
        $backupPaths = [];
        foreach ($backupTypes as $backupType) {
            $fileName = BACKUP_DIR . $timeStamp . '_filesystem_' . $backupType .'.tgz';
            if (!file_exists($fileName)) {
                $backupPaths['error'][] = 'Backup file does not exist for "' . $backupType . '"';
            } else {
                $backupPaths[$backupType]['filename'] = $fileName;
                $backupPaths[$backupType]['type'] = 'rollback';
            }
        }
        $fileName = BACKUP_DIR . $timeStamp  .'_' . self::BACKUP_DB .'.gz';
        if (!file_exists($fileName)) {
            $backupPaths['error'][] = 'Backup file does not exist for "' . self::BACKUP_DB . '"';
        } else {
            $backupPaths[self::BACKUP_DB]['filename'] = $fileName;
            $backupPaths[self::BACKUP_DB]['type'] = 'setup:rollback';
        }
        return $backupPaths;
    }

    /**
     * Find the timestamp of the last backup file from backup directory.
     *
     * @throws \RuntimeException
     * @return string
     */
    private function getLastBackupFileTimestamp()
    {
        $allFileList = scandir(BACKUP_DIR, SCANDIR_SORT_DESCENDING);
        $backupFileName = '';

        if (isset($allFileList) && !empty($allFileList)) {
            $backupFileName = explode('_', $allFileList[0])[0];
        }

        if (empty($backupFileName)) {
            throw new \RuntimeException('Backup directory is empty');
        }
        return $backupFileName;
    }
}
