<?php

namespace codename\core\tests\request;

use codename\core\request\http;
use codename\core\tests\requestTest;

/**
 * Test some request functionality
 */
class httpTest extends requestTest
{
    /**
     * @return void
     */
    public function testRequestDatacontainer(): void
    {
        $request = new http();
        static::assertEquals(array_merge($_GET, $_POST, ['lang' => 'de_DE']), $request->getData());
    }

    /**
     * @return void
     */
    public function testHttpsSupport(): void
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        new http();
        static::assertEquals('on', $_SERVER['HTTPS']);
    }
}
