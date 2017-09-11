<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

use Magento\Update\Backup\BackupInfo;
use Magento\Update\Status;

/**
 * Class for creating Magento codebase backups.
 */
class Backup
{
    /**
     * @var BackupInfo
     */
    protected $backupInfo;

    /**
     * @var Status
     */
    protected $status;

    /**
     * Initialize dependencies.
     *
     * @param BackupInfo|null $backupInfo
     * @param Status|null $status
     */
    public function __construct(BackupInfo $backupInfo = null, Status $status = null)
    {
        $this->backupInfo = $backupInfo ? $backupInfo : new BackupInfo();
        $this->status = $status ? $status : new Status();
    }

    /**
     * Create backup archive using unix zip tool.
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function execute()
    {
        $backupFilePath = $this->backupInfo->getBackupPath() . $this->backupInfo->generateBackupFilename();
        $command = $this->buildShellCommand($backupFilePath);
        $this->status->add(sprintf('Creating backup archive "%s" ...', $backupFilePath), \Psr\Log\LogLevel::INFO);
        exec($command, $output, $return);
        if ($return) {
            throw new \RuntimeException(
                sprintf('Cannot create backup with command "%s": %s', $command, implode("\n", $output),
                    \Psr\Log\LogLevel::ERROR
                )
            );
        }
        $this->status->add(sprintf('Backup archive "%s" has been created.', $backupFilePath), \Psr\Log\LogLevel::INFO);
        return $this;
    }

    /**
     * Construct shell command for creating backup archive.
     *
     * @param string $backupFilePath
     * @return string
     */
    protected function buildShellCommand($backupFilePath)
    {
        $excludedElements = '';
        foreach ($this->backupInfo->getBlacklist() as $excludedElement) {
            $elementPath = $excludedElement;
            $fullPath = $this->backupInfo->getArchivedDirectory() . '/' . $elementPath;
            $excludedElements .= is_dir($fullPath) ? $elementPath . '\* ' : $elementPath . ' ';
        }
        $changeDirectoryCommand = sprintf("cd %s", $this->backupInfo->getArchivedDirectory());
        $zipCommand = sprintf("zip -r %s . -x %s", $backupFilePath, $excludedElements);
        return $changeDirectoryCommand . ' && ' . $zipCommand;
    }
}
