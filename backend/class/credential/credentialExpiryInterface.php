<?php

namespace codename\core\credential;

/**
 * Definition for expiring credential data
 * @package core
 * @since 2018-02-26
 */
interface credentialExpiryInterface
{
    /**
     * returns the authenticating component of this credential
     * may be an array, string or even an object
     *
     * @return mixed
     */
    public function getExpiry(): mixed;
}
