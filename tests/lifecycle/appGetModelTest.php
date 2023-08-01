<?php

namespace codename\core\tests\lifecycle;

use codename\core\app;
use codename\core\exception;
use codename\core\tests\base;
use ReflectionException;

class appGetModelTest extends base
{
    /**
     * @var bool
     */
    protected static bool $initialized = false;

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$initialized = false;
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testAppGetModel(): void
    {
        $sampleModel = app::getModel('sample');
        static::assertEquals([
          'sample_id',
          'sample_created',
          'sample_modified',
          'sample_text',
        ], $sampleModel->getFields());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testAppGetModelAgain(): void
    {
        $sampleModel = app::getModel('sample');
        static::assertEquals([
          'sample_id',
          'sample_created',
          'sample_modified',
          'sample_text',
        ], $sampleModel->getFields());
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();

        // Additional overrides to get a more complete app lifecycle
        // and allow static global app::getModel() to work correctly
        $app::__setApp('lifecycletest');
        $app::__setVendor('codename');
        $app::__setNamespace('\\codename\\core\\tests\\lifecycle');

        $app::getAppstack();

        // avoid re-init
        if (static::$initialized) {
            return;
        }

        static::$initialized = true;

        static::setEnvironmentConfig([
          'test' => [
            'database' => [
                // NOTE: by default, we do these tests using
                // pure in-memory sqlite.
              'default' => [
                'driver' => 'sqlite',
                'database_file' => ':memory:',
              ],
            ],
            'cache' => [
              'default' => [
                'driver' => 'memory',
              ],
            ],
            'filesystem' => [
              'local' => [
                'driver' => 'local',
              ],
            ],
            'log' => [
              'default' => [
                'driver' => 'system',
                'data' => [
                  'name' => 'dummy',
                ],
              ],
            ],
          ],
        ]);

        static::createModel('lifecycle', 'sample', [
          'field' => [
            'sample_id',
            'sample_created',
            'sample_modified',
            'sample_text',
          ],
          'primary' => [
            'sample_id',
          ],
          'datatype' => [
            'sample_id' => 'number_natural',
            'sample_created' => 'text_timestamp',
            'sample_modified' => 'text_timestamp',
            'sample_text' => 'text',
          ],
          'connection' => 'default',
        ]);

        static::architect('lifecycletest', 'codename', 'test');
    }
}
