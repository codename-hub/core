<?php

namespace codename\core\tests\lifecycle\model;

use codename\core\exception;
use codename\core\tests\sqlModel;
use ReflectionException;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class sample extends sqlModel
{
    /**
     * {@inheritDoc}
     * @param array $modeldata
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('lifecycle', 'sample', [
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
    }
}
