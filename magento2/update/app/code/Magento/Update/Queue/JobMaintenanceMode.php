<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

/**
 * Magento maintenance mode job
 */
class JobMaintenanceMode extends AbstractJob
{
    /**
     * @param string $name
     * @param array $params
     * @param \Magento\Update\Status|null $status
     */
    public function __construct(
        $name,
        $params,
        \Magento\Update\Status $status = null
    ) {
        parent::__construct($name, $params, $status);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        try {
            if ($this->params['enable'] == true) {
                $this->maintenanceMode->set(true);
            } else {
                $this->maintenanceMode->set(false);
            }
        } catch (\Exception $e) {

        }
        return $this;
    }
}
