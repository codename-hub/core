<?php
namespace codename\core\response;

class callback extends \codename\core\response {

    /**
     * @todo DOCUMENTATION
     */
    protected $javascriptActions = array();

    /**
     * @todo DOCUMENTATION
     */
    public function addJs(string $js) {
        $jsdo = $this->getData('jsdo');

        if(is_null($jsdo)) {
            $jsdo = array();
        }

        $jsdo[] = $js;
        $this->setData('jsdo', $jsdo);
        return;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function addNotification(string $subject, string $text, string $image, string $sound) {
        $this->addJs("joNotify('{$subject}', '{$text}', '{$image}', '{$sound}'");
        return;
    }

    /**
     * @inheritDoc
     */
    public function setHeader(string $header)
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    protected function translateStatus()
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    public function pushOutput()
    {
      throw new \LogicException('Not implemented'); // TODO
    }

}
