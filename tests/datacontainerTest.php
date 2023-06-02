<?php

namespace codename\core\tests;

use codename\core\datacontainer;

/**
 * Test some datacontainer functionality
 */
class datacontainerTest extends base
{
    /**
     * @return void
     */
    public function testDatacontainer(): void
    {
        $datacontainer = new datacontainer([
          'string' => 'abc',
          'integer' => 123,
          'array' => ['abc', 123],
          'nested' => [
            'string' => 'def',
            'integer' => 123,
          ],
        ]);

        static::assertEquals('abc', $datacontainer->getData('string'));
        static::assertEquals(123, $datacontainer->getData('integer'));

        static::assertEquals(['abc', 123], $datacontainer->getData('array'));
        static::assertEquals(['string' => 'def', 'integer' => 123], $datacontainer->getData('nested'));
        static::assertEquals('def', $datacontainer->getData('nested>string'));
        static::assertEquals(123, $datacontainer->getData('nested>integer'));

        // modify, nested
        $datacontainer->setData('nested>string', 'xyz');
        static::assertEquals(['string' => 'xyz', 'integer' => 123], $datacontainer->getData('nested'));

        // add nested
        $datacontainer->setData('nested2>string', 'vwu');
        static::assertEquals(['string' => 'vwu'], $datacontainer->getData('nested2'));

        $datacontainer->addData([
          'integer' => 456,
          'nested' => [
            'changed' => true,
          ],
        ]);

        static::assertEquals(456, $datacontainer->getData('integer'));
        static::assertEquals(['changed' => true], $datacontainer->getData('nested'));

        $datacontainer->setData('string', 'ghi');
        $datacontainer->unsetData('nested2');
        $datacontainer->unsetData('');
        $datacontainer->unsetData('fake');

        static::assertEquals([
          'string' => 'ghi',
          'integer' => 456,
          'array' => ['abc', 123],
          'nested' => [
            'changed' => true,
          ],
        ], $datacontainer->getData());

        static::assertTrue($datacontainer->isDefined('string'));
        static::assertTrue($datacontainer->isDefined('nested>changed'));

        static::assertFalse($datacontainer->isDefined('nested2'));
        static::assertFalse($datacontainer->isDefined('nonexisting'));
        static::assertFalse($datacontainer->isDefined('nonexisting>nonexisting_subkey'));

        $datacontainer->setData('null_value', null);
        static::assertTrue($datacontainer->isDefined('null_value'));

        static::assertNull($datacontainer->getData('nonexisting'));
        static::assertNull($datacontainer->getData('nonexisting>nonexisting_subkey'));
    }
}
