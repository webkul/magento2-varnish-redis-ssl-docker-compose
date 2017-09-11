<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Update\Backup;
use Magento\Update\Backup\BackupInfo;
use Magento\Update\Status;
use Magento\Update\MaintenanceMode;

/**
 * Magento updater application 'backup' job.
 */
class JobBackup extends AbstractJob
{
    /** @var BackupInfo */
    protected $backupInfo;

    /**
     * Initialize job instance.
     *
     * @param string $name
     * @param array $params
     * @param Status|null $status
     * @param MaintenanceMode|null $maintenanceMode
     * @param BackupInfo|null $backupInfo
     */
    public function __construct(
        $name,
        array $params,
        Status $status = null,
        MaintenanceMode $maintenanceMode = null,
        $backupInfo = null
    ) {
        parent::__construct($name, $params, $status, $maintenanceMode);
        $backupInfo = $backupInfo ? $backupInfo : new BackupInfo();
        $this->backup = new Backup($backupInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->backup->execute();
        return $this;
    }
}
