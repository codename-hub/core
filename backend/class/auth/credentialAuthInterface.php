<?php

namespace codename\core\auth;

use codename\core\credential;

/**
 * Definition for \codename\core\auth
 * and for usage with the new credential classes
 *
 * @package core
 * @since 2018-02-22
 */
interface credentialAuthInterface
{
    /**
     * Returns an array of data, identifying the user that logged in with this request
     * Returns an empty array if authentication failed
     * @param string $username <i>Try authentication for this user...</i>
     * @param string $password <i>... using this password</i>
     * @return array <i>Array of user information. Is <b>EMPTY</b> on authentication failure</i>
     * @access public
     */

    /**
     * Authenticates using the given credential object
     * returns a data array that is associated with this credential
     * (e.g. session data, user/client data, etc.)
     *
     * @param credential $credential [description]
     * @return array                                  [description]
     */
    public function authenticate(credential $credential): array;

    /**
     * Returns the hashed password value
     * You may want to create your own hashing algo using this method.
     * This method is public to be accessible for contexts and models (e.g. automatic user creation, password resetting)
     * @param string $username <i>The username to hash</i>
     * @param string $password <i>The password to hash</i>
     * @return string <i>The hashed combination of $username and $password</i>
     * @access public
     */

    /**
     * returns a new credential object from the given parameters
     * note the type depends on the auth class used
     *
     * @param array $parameters [parameter array]
     * @return credential    [credential object]
     */
    public function createCredential(array $parameters): credential;

    /**
     * creates a hash using the given credential object
     *
     * @param credential $credential [description]
     * @return string                             [description]
     */
    public function makeHash(credential $credential): string;

    /**
     * Returns true if we have a valid authentication
     * returns false otherwise
     * @return bool [authentication success]
     */
    public function isAuthenticated(): bool;
}
