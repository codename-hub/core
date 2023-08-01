<?php

namespace codename\core\translate;

use codename\core\app;
use codename\core\config;

class dummy extends json
{
    /**
     * {@inheritDoc}
     */
    public function getAllTranslations(string $prefix): ?array
    {
        return $this->getSourceInstance($prefix) ? $this->getSourceInstance($prefix)->get() : null;
    }

    /**
     * [getSourceInstance description]
     * @param string $name [description]
     * @return config|null            [description]
     */
    protected function getSourceInstance(string $name): ?config
    {
        $stackname = 'translation/' . $this->getPrefix() . '/' . $name . '.json';
        if (array_key_exists($stackname, $this->instances)) {
            return $this->instances[$stackname];
        } else {
            $this->instances[$stackname] = new config([]);
        }
        return $this->instances[$stackname];
    }

    /**
     * {@inheritDoc}
     */
    public function getPrefix(): string
    {
        return app::getRequest()->getData('lang');
    }
}
