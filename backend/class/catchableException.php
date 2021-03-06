<?php
namespace codename\core;

/**
 * Why should we even use exceptions, if we can't catch them?
 * Gotta catch 'em all!
 */
class catchableException extends \codename\core\exception {

    /**
     * Create an errormessage
     * @param string $code
     * @param int $levels
     * @param mixed|null $info
     */
    public function __CONSTRUCT(string $code, int $level, $info = null) {
      $this->message = $this->translateExceptionCode($code);
      $this->code = $code;
      $this->info = $info;
  	  app::getHook()->fire($code);
     	app::getHook()->fire('EXCEPTION');
      return $this;
    }


}
