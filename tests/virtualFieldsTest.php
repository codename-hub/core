<?php
namespace codename\core\tests;

/**
 * Test Virtual field functionality
 */
class virtualFieldsTest extends base {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = $this->createApp();
    $app->getAppstack();

    define('CORE_ENVIRONMENT', 'vfields');

    $this->setEnvironmentConfig([
      'vfields' => [
        'database' => [
          'default' => [
            'driver' => 'sqlite',
            'database_file' => ':memory:',
          ]
        ],
        'cache' => [
          'default' => [
            'driver' => 'memory'
          ]
        ],
        'filesystem' =>[
          'local' => [
            'driver' => 'local',
          ]
        ],
        'log' => [
          'default' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ]
        ],
      ]
    ]);

    $this->createModel('schema1', 'model1', [
      'field' => [
        'model1_id',
        'model1_created',
        'model1_modified',
        'model1_value',
      ],
      'primary' => [
        'model1_id'
      ],
      'datatype' => [
        'model1_id'       => 'number_natural',
        'model1_created'  => 'text_timestamp',
        'model1_modified' => 'text_timestamp',
        'model1_value'    => 'text',
      ],
      'connection' => 'default'
    ]);

    //
    // A secondary model with a reference to model1
    // including a virtual field that may display/represent a model1-dataset
    //
    $this->createModel('schema2', 'model2', [
      'field' => [
        'model2_id',
        'model2_created',
        'model2_modified',
        'model2_value',
        'model2_model1_id',
        'model2_model1',
      ],
      'primary' => [
        'model2_id'
      ],
      'children' => [
        'model2_model1' => [
          'type'  => 'foreign',
          'field' => 'model2_model1_id'
        ]
      ],
      'foreign' => [
        'model2_model1_id' => [
          'schema' => 'schema1',
          'model'  => 'model1',
          'key'    => 'model1_id'
        ]
      ],
      'datatype' => [
        'model2_id'         => 'number_natural',
        'model2_created'    => 'text_timestamp',
        'model2_modified'   => 'text_timestamp',
        'model2_value'      => 'text',
        'model2_model1_id'  => 'number_natural',
        'model2_model1'     => 'virtual',
      ],
      'connection' => 'default'
    ]);

    $this->architect('vfieldstest', 'codename', 'vfields');
  }

  /**
   * Tests saving virtual field data with enabled models
   * @return void
   */
  public function testVirtualFieldSaving() {

    $model2 = $this->getModel('model2')->setVirtualFieldResult(true)
      ->addModel($model1 = $this->getModel('model1')->setVirtualFieldResult(true))
    ;

    $model2->saveWithChildren([
      'model2_value'    => 'm2',
      'model2_model1'  => [
        'model1_value'  => 'm1'
      ]
    ]);

    // Assert we have a lastInsertId generated in *both* models
    $this->assertGreaterThan(0, $model1->lastInsertId());
    $this->assertGreaterThan(0, $model2->lastInsertId());
  }

  /**
   * Tests saving virtual field data with enabled models
   * and checks result output, including hidden fields
   * @return void
   */
  public function testVirtualFieldWithRedactedFields() {
    $model2 = $this->getModel('model2')->setVirtualFieldResult(true)
      ->addModel($model1 = $this->getModel('model1')->setVirtualFieldResult(true))
    ;

    $model2->saveWithChildren([
      'model2_value'    => 'abc',
      'model2_model1'  => [
        'model1_value'  => 'def'
      ]
    ]);
    $id = $model2->lastInsertId();

    // just allow one field for the child model
    $model1->hideAllFields()->addField('model1_value');

    $res = $model2
      ->addFilter($model2->getPrimarykey(), $id)
      ->search()->getResult();

    // Make sure we have the right count being returned
    // and the data is the same as in the beginning
    // NOTE: we have reduced field output here!
    $this->assertCount(1, $res);
    $this->assertEquals([ 'model1_value'  => 'def' ], $res[0]['model2_model1']);
  }

}
