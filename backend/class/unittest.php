<?php
namespace codename\core;

/*
if(getenv('core_bootstrap_path') !== false) {
  require_once getenv('core_bootstrap_path');
} else {
  require_once DIRNAME(DIRNAME(DIRNAME(DIRNAME(DIRNAME(DIRNAME(__FILE__)))))).'/src/bootstrap.php';
}
*/

/**
 * I will automatically start all test in the testing framework
 * <br />Be sure to check out my method testAll() to
 * @package codename\core
 */
class unittest extends \PHPUnit\Framework\TestCase {

    /**
     * I will test all validators
     * @return void
     */
    /* public function testValidator() {
        $this->testAll();
    }*/

    /**
     * I will try finding all tests that are located below the current test.
     * <br />I must be overridden in the last testing level or endless recursion will be created.
     * @return void
     */
    public function testAll() {
        $tests = $this->getSubtests();
        foreach($tests as $test) {
            $testclass = "\\".(new \ReflectionClass($this))->getName() . "\\{$test}";
            (new $testclass)->testAll();
        }
        return;
    }

    /**
     * I will return all subtests of the current test instance
     * <br />To find subtests, I will have a look inside the subdirectory of my class' folder
     * @return array
     */
    protected function getSubtests() : array {
        $subclasses = array();
        $folder = DIRNAME(__FILE__) . '/' . str_replace('codename/core/', '', str_replace('\\', '/', (new \ReflectionClass($this))->getName()));
        foreach((new \codename\core\filesystem\local())->dirList($folder) as $subtest) {
            $subclasses[] = str_replace('.php', '', $subtest);
        }
        return array_unique($subclasses);
    }

}
