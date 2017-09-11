<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

/**
 * Magento updater application 'remove_backups' job.
 */
class JobRemoveBackups extends AbstractJob
{
    const BACKUPS_FILE_NAMES = 'backups_file_names';
    
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $filesToDelete = [];
        if (isset($this->params[self::BACKUPS_FILE_NAMES])) {
            $filesToDelete = $this->params[self::BACKUPS_FILE_NAMES];
        }
        if ($this->maintenanceMode->isOn() || $this->status->isUpdateError()) {
            throw new \RuntimeException("Cannot remove backup archives while setup is in progress.");
        }
        foreach ($filesToDelete as $archivePath) {
            if (file_exists($archivePath) && unlink($archivePath)) {
                $this->status->add(sprintf('"%s" was deleted successfully.', $archivePath), \Psr\Log\LogLevel::INFO);
            } else {
                throw new \RuntimeException(sprintf('Could not delete backup archive "%s"', $archivePath));
            }
        }
    }
}
