<?php

namespace codename\core\credential;

/**
 * Definition for \codename\core\credential
 * @package core
 * @since 2018-02-22
 */
interface credentialInterface
{
    /**
     * returns an identifier for this credential
     *
     * @return string [identifier string]
     */
    public function getIdentifier(): string;

    /**
     * returns the authenticating component of this credential
     * may be an array, string or even an object
     *
     * @return mixed
     */
    public function getAuthentication(): mixed;
}
