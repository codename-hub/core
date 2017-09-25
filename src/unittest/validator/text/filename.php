<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the filename validator
 * @package codename\core
 * @since 2016-11-02
 */
class filename extends \codename\core\unittest\validator\text {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        $this->testValueNotString();
        $this->testValueTooLong();
        $this->testValueTooShort();
        $this->testValueInvalidchars();
        $this->testValueValid();
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', app::getValidator('text_filename')->validate(array())[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', app::getValidator('text_filename')->validate('fzagdsbfkqwegsrbiqkuwhgrd3nq4wu5rbd3iqzw4uergxinaesudkrfgixskdfgxqiwi7eurz2x0oqurzq2o83i4ezy10qturz3woeiurgqwakrfjagwesorijawesfiljkd')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', app::getValidator('text_filename')->validate('a.a')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', app::getValidator('text_filename')->validate('/tmp/test.file')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), app::getValidator('text_filename')->validate('test.pdf'));
    }
    
}
