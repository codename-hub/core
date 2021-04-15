<?php
namespace codename\core\tests\validator\structure\config;

use \codename\core\app;

/**
 * I will test the crud validator
 * @package codename\core
 * @since 2016-11-02
 */
class crud extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $errors = $this->getValidator()->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertCount(3, $errors);
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[2]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidPaginationLimit() {
      $config = [
        'pagination'    => [
          'limit'       => 'AAA'
        ],
        'visibleFields' => 'AAA',
        'order'         => 'AAA',
      ];
      $this->assertEquals('VALIDATION.PAGINATION_CONFIGURATION_INVALID', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValuePaginationLimitTooSmall() {
      $config = [
        'pagination'    => [
          'limit'       => -1
        ],
        'visibleFields' => 'AAA',
        'order'         => 'AAA',
      ];
      $this->assertEquals('VALIDATION.PAGINATION_CONFIGURATION_INVALID', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValuePaginationLimitTooHigh() {
      $config = [
        'pagination'    => [
          'limit'       => 1111111111111111111111111111111111111
        ],
        'visibleFields' => 'AAA',
        'order'         => 'AAA',
      ];
      $this->assertEquals('VALIDATION.PAGINATION_CONFIGURATION_INVALID', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'pagination'    => [
            'limit'       => 10
          ],
          'visibleFields' => 'AAA',
          'order'         => 'AAA',
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
