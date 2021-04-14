<?php
namespace codename\core\tests\validator\structure\api\codename;

use \codename\core\app;

/**
 * I will test the response validator
 * @package codename\core
 * @since 2016-11-02
 */
class response extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $errors = $this->getValidator()->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidKeySuccess() {
        $config = [
          'success' => 'A',
          'data'    => 'example'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.INVALID_SUCCESS_IDENTIFIER', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'success' => 1,
          'data'    => 'example'
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
