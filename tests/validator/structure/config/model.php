<?php
namespace codename\core\tests\validator\structure\config;

/**
 * I will test the model validator
 * @package codename\core
 * @since 2016-11-02
 */
class model extends \codename\core\tests\validator\structure {

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
    public function testValueKeyFieldNotAArray() {
      $config = [
        'field'    => 'AAA',
        'primary'  => [
        ],
        'datatype' => [
        ],
      ];
      $this->assertEquals('VALIDATION.KEY_FIELD_NOT_A_ARRAY', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueKeyPrimaryNotAArray() {
      $config = [
        'field'    => [
        ],
        'primary'  => 'AAA',
        'datatype' => [
        ],
      ];
      $this->assertEquals('VALIDATION.KEY_PRIMARY_NOT_A_ARRAY', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueKeyDatatypeNotAArray() {
      $config = [
        'field'    => [
          'AAA'
        ],
        'primary'  => [
        ],
        'datatype' => 'AAA',
      ];
      $this->assertEquals('VALIDATION.KEY_DATATYPE_NOT_A_ARRAY', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidKeyField() {
      $config = [
        'field'    => [
          'A'
        ],
        'primary'  => [
        ],
        'datatype' => [
        ],
      ];
      $this->assertEquals('VALIDATION.KEY_FIELD_INVALID', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueKeyPrimaryNotInKeyField() {
      $config = [
        'field'    => [
          'AAA'
        ],
        'primary'  => [
          'BBB'
        ],
        'datatype' => [
          'AAA'     => 'AAA'
        ],
      ];
      $this->assertEquals('VALIDATION.PRIMARY_KEY_NOT_CONTAINED_IN_FIELD_ARRAY', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingDatatypeConfig() {
      $config = [
        'field'    => [
          'AAA'
        ],
        'primary'  => [
        ],
        'datatype' => [
          'BBB'     => 'AAA'
        ],
      ];
      $this->assertEquals('VALIDATION.DATATYPE_CONFIG_MISSING', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'field'    => [
          ],
          'primary'  => [
          ],
          'datatype' => [
          ],
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
