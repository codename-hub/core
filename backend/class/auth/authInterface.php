<?php

namespace codename\core\auth;

/**
 * Definition for \codename\core\auth
 * @package core
 * @since 2016-04-05
 */
interface authInterface
{
    /**
     * Returns an array of data, identifying the user that logged in with this request
     * Returns an empty array if authentication failed
     * @param string $username <i>Try authentication for this user...</i>
     * @param string $password <i>... using this password</i>
     * @return array <i>Array of user information. Is <b>EMPTY</b> on authentication failure</i>
     * @access public
     */
    public function authenticate(string $username, string $password): array;

    /**
     * Returns the hashed password value
     * You may want to create your own hashing algo using this method.
     * This method is public to be accessible for contexts and models (e.g. automatic user creation, password resetting)
     * @param string $username <i>The username to hash</i>
     * @param string $password <i>The password to hash</i>
     * @return string <i>The hashed combination of $username and $password</i>
     * @access public
     */
    public function passwordMake(string $username, string $password): string;

    /**
     * Returns true if the current user is member of the given usergroup.
     * @param string $usergroup_name
     * @return bool
     */
    public function memberOf(string $usergroup_name): bool;
}
