<?php

namespace codename\core\tests\request;

use codename\core\request\cli;
use codename\core\tests\requestTest;

/**
 * Test some request functionality
 */
class cliTest extends requestTest
{
    /**
     * @return void
     */
    public function testRequestDatacontainer(): void
    {
        static::markTestIncomplete('CLI Request may contain phpunit/unittest arguments!');

        $request = new cli();
        static::assertEquals(array_merge(['lang' => 'de_DE']), $request->getData());
    }
}
