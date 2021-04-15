<?php
namespace codename\core\tests\validator\structure\config\crud;

use \codename\core\app;

/**
 * I will test the pagination validator
 * @package codename\core
 * @since 2016-11-02
 */
class pagination extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $errors = $this->getValidator()->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidLimit() {
      $config = [
        'limit' => 'AAA'
      ];
      $this->assertEquals('VALIDATION.INVALID_LIMIT', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooSmall() {
      $config = [
        'limit' => -1
      ];
      $this->assertEquals('VALIDATION.LIMIT_TOO_SMALL', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooHigh() {
      $config = [
        'limit' => 1111111111111111111111111111111111111
      ];
      $this->assertEquals('VALIDATION.LIMIT_TOO_HIGH', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'limit'     => 10,
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
