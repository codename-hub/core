<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the authhash validator
 * @package codename\core
 * @since 2016-11-02
 */
class authhash extends \codename\core\unittest\validator\text {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        $this->testValueNotString();
        $this->testValueTooShort();
        $this->testValueTooLong();
        $this->testValueInvalidchars();
        $this->testValueValid();
        return;
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', app::getValidator('text_authhash')->validate(array())[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', app::getValidator('text_authhash')->validate('A')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', app::getValidator('text_authhash')->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', app::getValidator('text_authhash')->validate('qyDdMHWwcDuC2VZwgKuG3RfDTkZcqzB92EPCHkQvKhpBvFa3QCWzKQ724AmfPMgJ4SLApc5fKvMEkFnS3rXGaYbdkP2F2sZbZXn8pqbBPVMARtnVEzgvzRua6de62An$')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), app::getValidator('text_authhash')->validate('qyDdMHWwcDuC2VZwgKuG3RfDTkZcqzB92EPCHkQvKhpBvFa3QCWzKQ724AmfPMgJ4SLApc5fKvMEkFnS3rXGaYbdkP2F2sZbZXn8pqbBPVMARtnVEzgvzRua6de62Anz'));
    }
    
}
