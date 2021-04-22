<?php
namespace codename\core\tests;

/**
 * Test some datacontainer functionality
 */
class datacontainerTest extends base {

  /**
   * [testDatacontainer description]
   */
  public function testDatacontainer(): void {
    $datacontainer = new \codename\core\datacontainer([
      'string'  => 'abc',
      'integer' => 123,
      'array'   => [ 'abc', 123 ],
      'nested'  => [
        'string'  => 'def',
        'integer' => 123
      ]
    ]);

    $this->assertEquals('abc', $datacontainer->getData('string'));
    $this->assertEquals(123, $datacontainer->getData('integer'));

    $this->assertEquals([ 'abc', 123 ], $datacontainer->getData('array'));
    $this->assertEquals([ 'string' => 'def', 'integer' => 123 ], $datacontainer->getData('nested'));
    $this->assertEquals('def', $datacontainer->getData('nested>string'));
    $this->assertEquals(123, $datacontainer->getData('nested>integer'));

    // modify, nested
    $datacontainer->setData('nested>string', 'xyz');
    $this->assertEquals([ 'string' => 'xyz', 'integer' => 123 ], $datacontainer->getData('nested'));

    // add nested
    $datacontainer->setData('nested2>string', 'vwu');
    $this->assertEquals([ 'string' => 'vwu' ], $datacontainer->getData('nested2'));

    $datacontainer->addData([
      'integer' => 456,
      'nested'  => [
        'changed' => true
      ]
    ]);

    $this->assertEquals(456, $datacontainer->getData('integer'));
    $this->assertEquals([ 'changed' => true ], $datacontainer->getData('nested'));

    $datacontainer->setData('string', 'ghi');
    $datacontainer->unsetData('nested2');
    $datacontainer->unsetData('');
    $datacontainer->unsetData('fake');

    $this->assertEquals([
      'string'  => 'ghi',
      'integer' => 456,
      'array'   => [ 'abc', 123 ],
      'nested'  => [
        'changed' => true
      ]
    ], $datacontainer->getData());

    $this->assertTrue($datacontainer->isDefined('string'));
    $this->assertTrue($datacontainer->isDefined('nested>changed'));

    $this->assertFalse($datacontainer->isDefined('nested2'));
    $this->assertFalse($datacontainer->isDefined('nonexisting'));
    $this->assertFalse($datacontainer->isDefined('nonexisting>nonexisting_subkey'));

    $datacontainer->setData('null_value', null);
    $this->assertTrue($datacontainer->isDefined('null_value'));

    $this->assertNull($datacontainer->getData('nonexisting'));
    $this->assertNull($datacontainer->getData('nonexisting>nonexisting_subkey'));

    $this->assertEmpty($datacontainer->setData('', 'test'));
  }
}
