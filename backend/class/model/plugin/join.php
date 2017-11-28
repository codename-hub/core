<?php
namespace codename\core\model\plugin;

/**
 * Provide model joining as a plugin
 * @package core
 * @since 2017-11-28
 */
class join extends \codename\core\model\plugin {

  /**
   * use current model default
   * @var string
   */
  public const TYPE_DEFAULT = 'DEFAULT';

  /**
   * perform a left join
   * @var string
   */
  public const TYPE_LEFT = 'LEFT';

  /**
   * perform a right join
   * @var string
   */
  public const TYPE_RIGHT = 'RIGHT';

  /**
   * perform a full join
   * @var string
   */
  public const TYPE_FULL = 'FULL';

  /**
   * perform an inner join
   * @var string
   */
  public const TYPE_INNER = 'INNER';

  /**
   * $model used for joining
   * @var \codename\core\model
   */
  public $model = null;

  /**
   * Contains the join type
   * @var string $type
   */
  public $type = null;

  /**
   * Contains the field to be joined upon (reference - the OTHER model)
   * @var string
   */
  public $referenceField = null;

  /**
   * Contains the field to be joined upon (this model)
   * @var string
   */
  public $modelField = null;

  /**
   *
   * {@inheritDoc}
   * @see \codename\core\model_plugin_filter::__CONSTRUCT(string $field, string $value, string $operator)
   */
  public function __CONSTRUCT(\codename\core\model $field, string $type, string $modelField, string $referenceField) {
      $this->model = $model;
      $this->type = $type;
      $this->referenceField = $referenceField;
      $this->modelField = $modelField;
      // TODO: perform null check?
      return $this;
  }

  /**
   * return the database-driver-specific keyword for this join
   * @return string
   */
  public abstract function getJoinMethod() : string;

}
