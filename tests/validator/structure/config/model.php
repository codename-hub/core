<?php

namespace codename\core\tests\validator\structure\config;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the model validator
 * @package codename\core
 * @since 2016-11-02
 */
class model extends structure
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueMissingArrKeys(): void
    {
        $errors = $this->getValidator()->validate([]);

        static::assertNotEmpty($errors);
        static::assertCount(3, $errors);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[2]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueKeyFieldNotAArray(): void
    {
        $config = [
          'field' => 'AAA',
          'primary' => [
          ],
          'datatype' => [
          ],
        ];
        static::assertEquals('VALIDATION.KEY_FIELD_NOT_A_ARRAY', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueKeyPrimaryNotAArray(): void
    {
        $config = [
          'field' => [
          ],
          'primary' => 'AAA',
          'datatype' => [
          ],
        ];
        static::assertEquals('VALIDATION.KEY_PRIMARY_NOT_A_ARRAY', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueKeyDatatypeNotAArray(): void
    {
        $config = [
          'field' => [
            'AAA',
          ],
          'primary' => [
          ],
          'datatype' => 'AAA',
        ];
        static::assertEquals('VALIDATION.KEY_DATATYPE_NOT_A_ARRAY', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidKeyField(): void
    {
        $config = [
          'field' => [
            'A',
          ],
          'primary' => [
          ],
          'datatype' => [
          ],
        ];
        static::assertEquals('VALIDATION.KEY_FIELD_INVALID', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueKeyPrimaryNotInKeyField(): void
    {
        $config = [
          'field' => [
            'AAA',
          ],
          'primary' => [
            'BBB',
          ],
          'datatype' => [
            'AAA' => 'AAA',
          ],
        ];
        static::assertEquals('VALIDATION.PRIMARY_KEY_NOT_CONTAINED_IN_FIELD_ARRAY', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueMissingDatatypeConfig(): void
    {
        $config = [
          'field' => [
            'AAA',
          ],
          'primary' => [
          ],
          'datatype' => [
            'BBB' => 'AAA',
          ],
        ];
        static::assertEquals('VALIDATION.DATATYPE_CONFIG_MISSING', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $config = [
          'field' => [
          ],
          'primary' => [
          ],
          'datatype' => [
          ],
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
