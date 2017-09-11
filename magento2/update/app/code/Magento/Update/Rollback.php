<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;
use Magento\Update\Backup\BackupInfo;
use Magento\Update\ExcludeFilter;

/**
 * Class for rollback capabilities
 */
class Rollback
{
    /**
     * @var string
     */
    protected $backupFileDir;

    /**
     * @var string
     */
    protected $restoreTargetDir;

    /**
     * @var Status
     */
    protected $status;

    /**
     * Initialize rollback.
     *
     * @param string|null $backupFileDir
     * @param string|null $restoreTargetDir
     * @param Status|null $status
     * @param BackupInfo|null $backupInfo
     */
    public function __construct(
        $backupFileDir = null,
        $restoreTargetDir = null,
        Status $status = null,
        BackupInfo $backupInfo = null
    ) {
        $this->backupFileDir = $backupFileDir ? $backupFileDir : BACKUP_DIR;
        $this->restoreTargetDir = $restoreTargetDir ? $restoreTargetDir : MAGENTO_BP;
        $this->status = $status ? $status : new Status();
        $this->backupInfo = $backupInfo ? $backupInfo : new BackupInfo();
    }

    /**
     * Restore Magento code from the backup archive.
     *
     * Rollback to the code/media version stored in the specified backup archive.
     *
     * @param string $backupFilePath
     * @return void
     */
    public function execute($backupFilePath)
    {
        $this->status->add(sprintf('Restoring archive from "%s" ...', $backupFilePath), \Psr\Log\LogLevel::INFO);
        $this->unzipArchive($backupFilePath);
    }

    /**
     * Unzip specified archive
     *
     * @param string $backupFilePath
     * @throws \RuntimeException
     * @return $this
     */
    private function unzipArchive($backupFilePath)
    {
        $phar = new \PharData($backupFilePath);
        $tarFile = str_replace('.tgz', '.tar', $backupFilePath);
        if (@file_exists($tarFile)) {
            @unlink($tarFile);
        }
        $phar->decompress();
        $tar = new \PharData($tarFile);

        if (strpos($backupFilePath, BackupInfo::BACKUP_MEDIA) > 0 ) {
            $this->deleteDirectory($this->restoreTargetDir . '/pub/media');
        } elseif (strpos($backupFilePath, BackupInfo::BACKUP_CODE) > 0 ) {
            $blackListFolders = $this->backupInfo->getBlacklist();
            $exclusions = [];
            foreach ($blackListFolders as $blackListFolder) {
                $exclusions[] = $this->restoreTargetDir . '/' . $blackListFolder;
            }
            try {
                $this->deleteDirectory($this->restoreTargetDir, $exclusions);
            } catch (\Exception $e) {
                $this->status->setUpdateError();
                $this->status->add('Error during rollback ' . $e->getMessage(), \Psr\Log\LogLevel::ERROR);
            }
        } else {
            $this->status->setUpdateError();
            $this->status->add('Invalid backup type', \Psr\Log\LogLevel::INFO);
        }
        $tar->extractTo($this->restoreTargetDir , null, true);
        @unlink($tarFile);

        //TODO Temporary solution, can be removed when MAGETWO-38589 is fixed.
        if (strpos($backupFilePath, BackupInfo::BACKUP_MEDIA) > 0 ) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->restoreTargetDir . '/pub/media'),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach($iterator as $item) {
                @chmod($item, 0777);
            }
        } elseif (strpos($backupFilePath, BackupInfo::BACKUP_CODE) > 0 ) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->restoreTargetDir),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach($iterator as $item) {
                @chmod($item, 0755);
            }
            $writeAccessFolders = ['/pub/media', '/pub/static', '/var'];
            foreach ($writeAccessFolders as $folder) {
                if (file_exists($this->restoreTargetDir . $folder)) {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($this->restoreTargetDir . $folder),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                    foreach($iterator as $item) {
                        @chmod($item, 0777);
                    }
                }
            }
        }
        //TODO Till here
    }

    /**
     * Recursively remove files and directories
     *
     * @param string $dir
     * @param array $exclude
     * @return bool
     */
    private function deleteDirectory($dir, $exclude = []) {

        $filesystemIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator = new ExcludeFilter($filesystemIterator, $exclude);

        foreach ($iterator as $item) {
            $itemToBeDeleted = $item->__toString();
            if ($item->isDir()) {
                rmdir($itemToBeDeleted);
            } else {
                unlink($itemToBeDeleted);
            }
        }

        // If $dir is empty with no child items, iterator will not be valid.
        // See http://php.net/manual/en/directoryiterator.valid.php
        if (is_dir($dir) && !(new \FilesystemIterator($dir))->valid()) {
            rmdir($dir);
        }
    }
}
