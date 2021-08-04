<?php
namespace codename\core\tests;

/**
 * Test some errorstack functionality
 */
class errorstackTest extends base {

  /**
   * [testErrorstack description]
   */
  public function testErrorstack(): void {
    $errorstack = new \codename\core\errorstack('example');

    $this->assertEquals([], $errorstack->jsonSerialize());
    $this->assertTrue($errorstack->isSuccess());

    $errorstack->addError('example', 'test', 'lalelu');
    $errorstack->addErrors($errorstack->getErrors());

    $this->assertEquals([
      [
        '__IDENTIFIER'  => 'example',
        '__CODE'        => 'EXAMPLE.test',
        '__TYPE'        => 'EXAMPLE',
        '__DETAILS'     => 'lalelu',
      ],
      [
        '__IDENTIFIER'  => 'example',
        '__CODE'        => 'EXAMPLE.test',
        '__TYPE'        => 'EXAMPLE',
        '__DETAILS'     => 'lalelu',
      ]
    ], $errorstack->getErrors());

    $this->assertFalse($errorstack->isSuccess());

    $errorstack->reset();

    $this->assertEmpty($errorstack->getErrors());

    $errorstack->addErrorstack((new \codename\core\errorstack('example')));

    $this->assertEmpty($errorstack->getErrors());

  }

}
