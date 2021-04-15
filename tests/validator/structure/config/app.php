<?php
namespace codename\core\tests\validator\structure\config;

/**
 * I will test the app validator
 * @package codename\core
 * @since 2016-11-02
 */
class app extends \codename\core\tests\validator\structure {

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
    public function testValueInvalidContext() {
      $config = [
        'context'    => [
          []
        ],
        'defaultcontext'  => '*123ABC',
        'defaulttemplate' => 'core',
      ];
      $this->assertEquals('VALIDATION.KEY_CONTEXT_INVALID', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidDefaultcontext() {
      $config = [
        'context'    => [
        ],
        'defaultcontext'  => '*123ABC',
        'defaulttemplate' => 'core',
      ];
      $this->assertEquals('VALIDATION.KEY_DEFAULTCONTEXT_INVALID', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidDefaulttemplate() {
      $config = [
        'context'    => [
        ],
        'defaultcontext'  => 'core',
        'defaulttemplate' => '*123ABC',
      ];
      $this->assertEquals('VALIDATION.KEY_DEFAULTTEMPLATE_INVALID', $this->getValidator()->validate($config)[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueContextCustom() {
        $config = [
          'context'    => [
            [
              'custom' => true,
            ]
          ],
          'defaultcontext'  => 'core',
          'defaulttemplate' => 'core',
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'context'    => [
          ],
          'defaultcontext'  => 'core',
          'defaulttemplate' => 'core',
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
