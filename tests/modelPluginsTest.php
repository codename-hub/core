<?php
namespace codename\core\tests;

/**
 * Test some model plugins functionality
 */
class modelPluginsTest extends base {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = $this->createApp();
    $app->getAppstack();

    define('CORE_ENVIRONMENT', 'modelplugins');

    $this->setEnvironmentConfig([
      'modelplugins' => [
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

    $this->createModel('testschema', 'testdata', [
      'field' => [
        'testdata_id',
        'testdata_created',
        'testdata_modified',
        'testdata_datetime',
        'testdata_text',
        'testdata_date',
        'testdata_number',
        'testdata_integer'
      ],
      'primary' => [
        'testdata_id'
      ],
      'datatype' => [
        'testdata_id'       => 'number_natural',
        'testdata_created'  => 'text_timestamp',
        'testdata_modified' => 'text_timestamp',
        'testdata_datetime' => 'text_timestamp',
        'testdata_text'     => 'text',
        'testdata_date'     => 'text_date',
        'testdata_number'   => 'number',
        'testdata_integer'  => 'number_natural'
      ],
      'connection' => 'default'
    ]);

    $this->architect('modelpluginstest', 'codename', 'modelplugins');
  }

  /**
   * Tests saving virtual field data with enabled models
   * @return void
   */
  public function testAggregatePlugins() {
    $testdataModel = $this->getModel('testdata');

    $entries = [
      [
        'testdata_text'     => 'foo',
        'testdata_datetime' => '2021-03-22 12:34:56',
        'testdata_date'     => '2021-03-22',
        'testdata_number'   => 3.14,
        'testdata_integer'  => 3
      ],
      [
        'testdata_text'     => 'bar',
        'testdata_datetime' => '2021-03-22 12:34:56',
        'testdata_date'     => '2021-03-22',
        'testdata_number'   => 4.25,
        'testdata_integer'  => 2
      ],
      [
        'testdata_text'     => 'foo',
        'testdata_datetime' => '2021-03-23 23:34:56',
        'testdata_date'     => '2021-03-23',
        'testdata_number'   => 5.36,
        'testdata_integer'  => 1
      ],
      [
        'testdata_text'     => 'bar',
        'testdata_datetime' => '2019-01-01 00:00:01',
        'testdata_date'     => '2019-01-01',
        'testdata_number'   => 0.99,
        'testdata_integer'  => 42
      ],
    ];

    foreach($entries as $dataset) {
      $testdataModel->save($dataset);
    }

    //
    // Aggregate: count plugin
    //
    $testCountModel = $this->getModel('testdata');
    $testCountModel->addAggregateField('entries_count', 'count', 'testdata_id');

    // count w/o filters
    $this->assertEquals(4, $testCountModel->search()->getResult()[0]['entries_count']);

    // w/ simple filter added
    $testCountModel->addFilter('testdata_datetime', '2020-01-01', '>=');
    $this->assertEquals(3, $testCountModel->search()->getResult()[0]['entries_count']);

    //
    // Aggregate: count_distinct plugin
    //
    $testCountDistinctModel = $this->getModel('testdata');
    $testCountDistinctModel->addAggregateField('entries_count', 'count_distinct', 'testdata_text');

    // count w/o filters
    $this->assertEquals(2, $testCountDistinctModel->search()->getResult()[0]['entries_count']);

    // w/ simple filter added - we only expect a count of 1
    $testCountDistinctModel
      ->addFilter('testdata_datetime', '2021-03-23', '>=');
    $this->assertEquals(1, $testCountDistinctModel->search()->getResult()[0]['entries_count']);

    //
    // Aggregate: sum plugin
    //
    $testSumModel = $this->getModel('testdata');
    $testSumModel->addAggregateField('entries_sum', 'sum', 'testdata_integer');

    // count w/o filters
    $this->assertEquals(48, $testSumModel->search()->getResult()[0]['entries_sum']);

    // w/ simple filter added
    $testSumModel->addFilter('testdata_datetime', '2020-01-01', '>=');
    $this->assertEquals(6, $testSumModel->search()->getResult()[0]['entries_sum']);

    // no entries matching filter
    $testSumModel->addFilter('testdata_datetime', '2019-01-01', '<=');
    $this->assertEquals(0, $testSumModel->search()->getResult()[0]['entries_sum']);


    //
    // Aggregate: avg plugin
    //
    $testSumModel = $this->getModel('testdata');
    $testSumModel->addAggregateField('entries_avg', 'avg', 'testdata_number');

    // count w/o filters
    $this->assertEquals((3.14 + 4.25 + 5.36 + 0.99)/4, $testSumModel->search()->getResult()[0]['entries_avg']);

    // w/ simple filter added
    $testSumModel->addFilter('testdata_datetime', '2020-01-01', '>=');
    $this->assertEquals((3.14 + 4.25 + 5.36)/3, $testSumModel->search()->getResult()[0]['entries_avg']);

    // no entries matching filter
    $testSumModel->addFilter('testdata_datetime', '2019-01-01', '<=');
    $this->assertEquals(0, $testSumModel->search()->getResult()[0]['entries_avg']);

    //
    // Aggregate DateTime plugin
    //
    $testYearModel = $this->getModel('testdata');
    $testYearModel->addAggregateField('entries_year1', 'year', 'testdata_datetime');
    $testYearModel->addAggregateField('entries_year2', 'year', 'testdata_date');
    $testYearModel->addOrder('testdata_id', 'ASC');
    $yearRes = $testYearModel->search()->getResult();
    $this->assertEquals([2021, 2021, 2021, 2019], array_column($yearRes, 'entries_year1'));
    $this->assertEquals([2021, 2021, 2021, 2019], array_column($yearRes, 'entries_year2'));

    // add a grouping modifier (WARNING, instance modified)
    // introduce additional summing
    // and order by calculated/aggregate field
    $testYearModel->addGroup('entries_year1');
    $testYearModel->addAggregateField('entries_sum', 'sum', 'testdata_integer');
    $testYearModel->addOrder('entries_year1', 'ASC');
    $yearGroupedRes = $testYearModel->search()->getResult();

    $this->assertEquals(2019, $yearGroupedRes[0]['entries_year1']);
    $this->assertEquals(42,   $yearGroupedRes[0]['entries_sum']);
    $this->assertEquals(2021, $yearGroupedRes[1]['entries_year1']);
    $this->assertEquals(6,    $yearGroupedRes[1]['entries_sum']);

    // //
    // // Aggregate Quarter plugin
    // //
    // $testQuarterModel = $this->getModel('testdata');
    // $testQuarterModel->addAggregateField('entries_quarter1', 'quarter', 'testdata_datetime');
    // $testQuarterModel->addAggregateField('entries_quarter2', 'year', 'testdata_date');
    // $testQuarterModel->addOrder('testdata_id', 'ASC');
    // $res = $testQuarterModel->search()->getResult();
    // $this->assertEquals([2021, 2021, 2021, 2019], array_column($res, 'entries_quarter1'));
    // $this->assertEquals([2021, 2021, 2021, 2019], array_column($res, 'entries_quarter2'));

    //
    // Aggregate DateTime plugin
    //
    $testMonthModel = $this->getModel('testdata');
    $testMonthModel->addAggregateField('entries_month1', 'month', 'testdata_datetime');
    $testMonthModel->addAggregateField('entries_month2', 'month', 'testdata_date');
    $testMonthModel->addOrder('testdata_id', 'ASC');
    $res = $testMonthModel->search()->getResult();
    $this->assertEquals([3, 3, 3, 1], array_column($res, 'entries_month1'));
    $this->assertEquals([3, 3, 3, 1], array_column($res, 'entries_month2'));

  }

}
