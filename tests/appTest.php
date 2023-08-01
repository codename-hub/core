<?php

namespace codename\core\tests;

use codename\core\app;
use codename\core\exception;
use ReflectionException;

/**
 * Test some generic app-class functions
 */
class appTest extends base
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testGetInheritedClassNonexisting(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(app::EXCEPTION_GETINHERITEDCLASS_CLASSFILENOTFOUND);
        app::getInheritedClass('nonexisting');
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testGetInheritedClassExisting(): void
    {
        $class = app::getInheritedClass('database');
        static::assertEquals('\\codename\\core\\database', $class);
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();
        $app::getAppstack();

        static::setEnvironmentConfig([
          'test' => [
              // 'database' => [
              //   'default' => [
              //     'driver' => 'sqlite',
              //     'database_file' => ':memory:',
              //   ]
              // ],
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
    }
}
