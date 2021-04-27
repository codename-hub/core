<?php
namespace codename\core\tests\lifecycle\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class sample extends \codename\core\tests\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('lifecycle', 'sample', [
      'field' => [
        'sample_id',
        'sample_created',
        'sample_modified',
        'sample_text',
      ],
      'primary' => [
        'sample_id'
      ],
      'datatype' => [
        'sample_id'       => 'number_natural',
        'sample_created'  => 'text_timestamp',
        'sample_modified' => 'text_timestamp',
        'sample_text'     => 'text',
      ],
      'connection' => 'default'
    ]);
  }
}
