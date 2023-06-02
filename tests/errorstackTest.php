<?php

namespace codename\core\tests;

use codename\core\errorstack;

/**
 * Test some errorstack functionality
 */
class errorstackTest extends base
{
    /**
     * @return void
     */
    public function testErrorstack(): void
    {
        $errorstack = new errorstack('example');

        static::assertEquals([], $errorstack->jsonSerialize());
        static::assertTrue($errorstack->isSuccess());

        $errorstack->addError('example', 'test', 'lalelu');
        $errorstack->addErrors($errorstack->getErrors());

        static::assertEquals([
          [
            '__IDENTIFIER' => 'example',
            '__CODE' => 'EXAMPLE.test',
            '__TYPE' => 'EXAMPLE',
            '__DETAILS' => 'lalelu',
          ],
          [
            '__IDENTIFIER' => 'example',
            '__CODE' => 'EXAMPLE.test',
            '__TYPE' => 'EXAMPLE',
            '__DETAILS' => 'lalelu',
          ],
        ], $errorstack->getErrors());

        static::assertFalse($errorstack->isSuccess());

        $errorstack->reset();

        static::assertEmpty($errorstack->getErrors());

        $errorstack->addErrorstack((new errorstack('example')));

        static::assertEmpty($errorstack->getErrors());
    }
}
