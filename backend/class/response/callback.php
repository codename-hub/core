<?php

namespace codename\core\response;

use codename\core\response;
use LogicException;

class callback extends response
{
    /**
     * @var array
     */
    protected array $javascriptActions = [];

    /**
     * @todo DOCUMENTATION
     */
    public function addNotification(string $subject, string $text, string $image, string $sound): void
    {
        $this->addJs("joNotify('$subject', '$text', '$image', '$sound'");
    }

    /**
     * @todo DOCUMENTATION
     */
    public function addJs(string $js): void
    {
        $jsdo = $this->getData('jsdo');

        if (is_null($jsdo)) {
            $jsdo = [];
        }

        $jsdo[] = $js;
        $this->setData('jsdo', $jsdo);
    }

    /**
     * {@inheritDoc}
     */
    public function setHeader(string $header)
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function pushOutput(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    protected function translateStatus(): mixed
    {
        throw new LogicException('Not implemented'); // TODO
    }
}
