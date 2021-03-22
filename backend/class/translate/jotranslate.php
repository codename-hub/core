<?php
namespace codename\core\translate;

class jotranslate extends \codename\core\translate implements \codename\core\translate\translateInterface {

    /**
     * Contains the jotranslate client instance
     * @var \codename\core\api\codename\jotranslate
     */
    protected $instance = null;

    /**
     * Creates instance
     * @param array $data
     * @return \codename\core\translate\jotranslate
     */
     public function __construct(array $data)  {
        $this->instance = new \codename\core\api\codename\jotranslate($data);
        return $this;
    }

    /**
     * Returns translated text for the required $key
     * @param string $key
     * @return string
     */
    protected function getTranslation(string $key) : string {
        return $this->instance->translate(strtoupper($key));
    }

    /**
     * @inheritDoc
     */
    public function getAllTranslations(string $prefix): ?array
    {
      throw new \LogicException('Not implemented'); // TODO
    }

}
