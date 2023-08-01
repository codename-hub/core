<?php

namespace codename\core\auth;

/**
 * Interface definition that allows (user)group checks
 *
 * @package core
 * @since 2018-02-22
 */
interface groupInterface
{
    /**
     * Returns true if the current user/credential/client is member of the given "group"".
     * @param string $groupName
     * @return bool
     */
    public function memberOf(string $groupName): bool;
}
