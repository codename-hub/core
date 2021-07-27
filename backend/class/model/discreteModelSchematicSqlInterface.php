<?php
namespace codename\core\model;

/**
 * interface that defines how to access a model
 * that is self-contained or displays an arbitrary query
 */
interface discreteModelSchematicSqlInterface
{
  /**
   * returns the (sub)query or arbitrary query
   * that contains the model's data
   * @param  array  &$params
   * @return string
   */
  function getDiscreteModelQuery(array &$params) : string;
}
