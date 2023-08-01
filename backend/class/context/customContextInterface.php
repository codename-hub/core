<?php

namespace codename\core\context;

/**
 * interfaces that defines a context
 * that manages its access/permissions/routes for itself
 * after accessing the entry point (context name),
 * e.g. via /contextname or ?context=contextname
 */
interface customContextInterface
{
    /**
     * defines the run method of the context
     * @return void
     */
    public function run(): void;

    /**
     * whether context allows public (unauthenticated) access
     * @return bool
     */
    public function isPublic(): bool;
}
