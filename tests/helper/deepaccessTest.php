<?php

namespace codename\core\tests\helper;

use codename\core\helper\deepaccess;
use codename\core\tests\base;

class deepaccessTest extends base
{
    /**
     * @return void
     */
    public function testDeepaccessMain(): void
    {
        $example = [];

        // set example data
        $example = deepaccess::set($example, ['example1', 'example2'], 'example');

        static::assertEquals([
          'example1' => [
            'example2' => 'example',
          ],
        ], $example);

        // get example data
        $result = deepaccess::get($example, ['example1', 'example2']);

        static::assertEquals('example', $result);

        // get not exists key
        $result = deepaccess::get($example, ['error1', 'error2']);

        static::assertNull($result);
    }
}
