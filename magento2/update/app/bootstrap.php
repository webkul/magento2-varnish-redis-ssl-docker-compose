<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

error_reporting(E_ALL);

define('UPDATER_BP', realpath(__DIR__ . '/../'));
if (!defined('MAGENTO_BP')) {
    define('MAGENTO_BP', realpath(__DIR__ . '/../../'));
}
define('BACKUP_DIR', MAGENTO_BP . '/var/backups/');

require_once UPDATER_BP . '/vendor/autoload.php';
