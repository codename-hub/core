<?php

namespace codename\core;

/**
 * [interface description]
 * @var [type]
 */
interface clientInterface
{
    /**
     * [setClientName description]
     * @param string $name [description]
     */
    public function setClientName(string $name): void;

    /**
     * [getClientName description]
     * @param string $name [description]
     * @return string [type]       [description]
     */
    public function getClientName(string $name): string;
}
