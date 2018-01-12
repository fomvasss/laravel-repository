<?php

namespace Fomvasss\Repository\Events;

/**
 * Class RepositoryEntityDeleted
 *
 * @package Fomvasss\Repository\Events
 */
class RepositoryEntityDeleted extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "deleted";
}
