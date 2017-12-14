<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the mobile validator
 * @package codename\core
 * @since 2016-11-02
 */
class mobile extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('+346759823475982347659234759234865923487562394875692384756923487659238476598237652398756329876')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('459"§!§345934')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), $this->getValidator()->validate('+496622918818'));
    }

}
