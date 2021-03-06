<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the filename validator
 * @package codename\core
 * @since 2016-11-02
 */
class filename extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('fzagdsbfkqwegsrbiqkuwhgrd3nq4wu5rbd3iqzw4uergxinaesudkrfgixskdfgxqiwi7eurz2x0oqurzq2o83i4ezy10qturz3woeiurgqwakrfjagwesorijawesfiljkd')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('a.a')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('/tmp/test.file')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('test.pdf'));
    }

}
