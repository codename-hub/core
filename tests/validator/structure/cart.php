<?php
namespace codename\core\tests\validator\structure;

use \codename\core\app;

/**
 * I will test the cart validator
 * @package codename\core
 * @since 2016-11-02
 */
class cart extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidProduct() {
        $this->assertEquals('VALIDATION.INVALID_PRODUCT_FOUND', $this->getValidator()->validate([[]])[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate([]));
    }

}
