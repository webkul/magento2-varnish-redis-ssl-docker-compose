<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

/**
 * Magento updater application job factory.
 */
class JobFactory
{
    /**#@+
     * Job name
     */
    const NAME_UPDATE = 'update';
    const NAME_BACKUP = 'backup';
    const NAME_ROLLBACK = 'rollback';
    const NAME_REMOVE_BACKUPS = 'remove_backups';
    const NAME_UNINSTALL = 'uninstall';
    const NAME_MAINTENANCE_MODE = 'maintenance_mode';
    /**#@-*/

    /**
     * Create job instance.
     *
     * @param string $name
     * @param array $params
     * @return AbstractJob
     * @throws \RuntimeException
     */
    public function create($name, array $params)
    {
        switch ($name) {
            case self::NAME_UPDATE:
                return new JobUpdate($name, $params);
                break;
            case self::NAME_BACKUP:
                return new JobBackup($name, $params);
                break;
            case self::NAME_ROLLBACK:
                return new JobRollback($name, $params);
                break;
            case self::NAME_REMOVE_BACKUPS:
                return new JobRemoveBackups($name, $params);
                break;
            case self::NAME_MAINTENANCE_MODE:
                return new JobMaintenanceMode($name, $params);
                break;
            case self::NAME_UNINSTALL:
                return new JobComponentUninstall($name, $params);
                break;
            default:
                throw new \RuntimeException(sprintf('"%s" job is not supported.', $name));
        }
    }
}
