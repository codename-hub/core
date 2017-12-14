<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the BIC validator
 * @package codename\core
 * @since 2016-11-02
 */
class bic extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('A')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*AASDASDASD')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), $this->getValidator()->validate('GENODEF1BEB'));
    }

}
