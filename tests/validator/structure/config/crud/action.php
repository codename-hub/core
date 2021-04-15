<?php
namespace codename\core\tests\validator\structure\config\crud;

use \codename\core\app;

/**
 * I will test the action validator
 * @package codename\core
 * @since 2016-11-02
 */
class action extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $errors = $this->getValidator()->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertCount(5, $errors);
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[2]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[3]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[4]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'name'        => 'AAA',
          'view'        => 'AAA',
          'context'     => 'AAA',
          'icon'        => 'AAA',
          'btnClass'    => 'AAA',
          'pagination'  => [
            'limit'     => 10
          ],
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
