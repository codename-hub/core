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
   * defines the run method of the this context
   * @return void
   */
  function run();

  /**
   * whether context allows public (unauthenticated) access
   * @return bool
   */
  function isPublic() : bool;
}
