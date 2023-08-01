<?php

namespace codename\core\model\schematic;

use codename\core\model\modelInterface;

/**
 * postgreSQL's specific SQL commands
 * @package core
 * @since 2016-02-04
 */
abstract class postgresql extends sql implements modelInterface
{
    /**
     * @todo DOCUMENTATION
     */
    public const DB_TYPE = 'postgresql';

    /**
     * {@inheritDoc}
     */
    public function getFilterQuery(
        array &$appliedFilters = [],
        ?string $mainAlias = null
    ): string {
        $mainAlias = $mainAlias ?? "$this->schema.$this->table";
        return parent::getFilterQuery($appliedFilters, $mainAlias);
    }
}
