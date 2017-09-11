<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

/**
 * Class PermissionInfo
 *
 * Data object for returning lists of non-writable, non-readable paths found by cron readiness permission's check
 */
class PermissionInfo
{

    /** @var  string[] */
    private $nonWritablePaths;

    /** @var  string[] */
    private $nonReadablePaths;

    /**
     * PermissionInfo constructor.
     *
     * @param string[] $nonWritablePaths List of paths which are not writable
     * @param string[] $nonReadablePaths List of paths which are not readable
     */
    public function __construct($nonWritablePaths, $nonReadablePaths)
    {
        $this->nonWritablePaths = $nonWritablePaths;
        $this->nonReadablePaths = $nonReadablePaths;
    }

    /**
     * Get array of non-writable paths
     * 
     * @return \string[]
     */
    public function getNonWritablePaths() {
        return $this->nonWritablePaths;
    }

    /**
     * Get array of non-readable paths
     * 
     * @return \string[]
     */
    public function getNonReadablePaths() {
        return $this->nonReadablePaths;
    }

    /**
     * See if there are any non-writable or non-readable
     * @return bool
     */
    public function containsPaths() {
        return !empty($this->nonWritablePaths) || !empty($this->nonReadablePaths);
    }
}
