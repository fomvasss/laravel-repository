<?php

namespace Fomvasss\Repository\Events;

/**
 * Class RepositoryEntityUpdated
 *
 * @package Fomvasss\Repository\Events
 */
class RepositoryEntityUpdated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "updated";
}
