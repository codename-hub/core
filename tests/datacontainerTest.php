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
      'string' => 'abc',
      'integer' => 123,
      'array' => [ 'abc', 123 ],
      'nested' => [
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
      'nested' => [
        'changed' => true
      ]
    ]);

    $this->assertEquals(456, $datacontainer->getData('integer'));
    $this->assertEquals([ 'changed' => true ], $datacontainer->getData('nested'));
  }
}
