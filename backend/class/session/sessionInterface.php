<?php
namespace codename\core\session;

/**
 * Definition for \codename\core\session
 * @package core
 * @since 2016-02-04
 */
interface sessionInterface {

    /**
     * Creates the instance and saves the data object in it
     * @param array $data
     * @return \codename\core\session
     */
    public function start(array $data) : \codename\core\session;

    /**
     * Ends the session the current user is in
     * @return multitype
     */
    public function destroy();

    /**
     * invalidates a session
     * @param  string|int $sessionId [some kind of session id this driver uses]
     * @return void
     */
    public function invalidate($sessionId);

}
