<?php

namespace codename\core\tests;

use codename\core\request;

/**
 * Test some request functionality
 */
class requestTest extends base
{
    /**
     * @return void
     */
    public function testRequestDatacontainer(): void
    {
        $request = new request();

        static::assertEquals([], $request->getData());
    }
}
