<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Update\Rollback;

/**
 * Magento updater application 'rollback' job.
 */
class JobRollback extends AbstractJob
{
    const BACKUP_FILE_NAME = 'backup_file_name';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $rollBack = new Rollback();
        $backupFileName = !isset($this->params[self::BACKUP_FILE_NAME]) ? null : $this->params[self::BACKUP_FILE_NAME];
        $rollBack->execute($backupFileName);
        return $this;
    }
}
