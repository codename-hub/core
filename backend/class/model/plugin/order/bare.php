<?php
namespace codename\core\model\plugin\order;

/**
 * Bare ordering plugin
 * @package core
 * @author Kevin Dargel
 * @since 2017-12-05
 */
class bare extends \codename\core\model\plugin\order
  implements \codename\core\model\plugin\order\orderInterface, \codename\core\model\plugin\order\executableOrderInterface {

  /**
   * @inheritDoc
   */
  public function order(array $data): array
  {
    $key = $this->field->get();
    self::stable_usort($data, function(array $left, array $right) use ($key) {
      if($left[$key] == $right[$key]) {
        return 0;
      }
      $prepSort = array($left[$key], $right[$key]);
      sort($prepSort);
      if($prepSort[0] === $left[$key]) {
        return $this->direction == 'ASC' ? -1 : 1;
      } else {
        return $this->direction == 'ASC' ? 1 : -1;
      }
    });
    return $data;
  }

  /**
   * stable usort function
   * @var [type]
   */
  protected static function stable_usort(array &$array, $value_compare_func)
	{
		$index = 0;
		foreach ($array as &$item) {
			$item = array($index++, $item);
		}
		$result = usort($array, function($a, $b) use($value_compare_func) {
			$result = call_user_func($value_compare_func, $a[1], $b[1]);
			return $result == 0 ? $a[0] - $b[0] : $result;
		});
		foreach ($array as &$item) {
			$item = $item[1];
		}
		return $result;
	}
}
