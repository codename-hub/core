<?php
namespace codename\core\tests\validator\structure\api\codename;

use \codename\core\app;

/**
 * I will test the serviceprovider validator
 * @package codename\core
 * @since 2016-11-02
 */
class serviceprovider extends \codename\core\tests\validator\structure {

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
    public function testTextInvalidKeyHost() {
        $config = [
          'host'  => '://example.com',
          'port'  => '80'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.HOST_INVALID', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidKeySuccess() {
        $config = [
          'host'  => 'http://example.com',
          'port'  => 'example'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.PORT_INVALID', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'host'  => 'http://example.com',
          'port'  => '80'
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
