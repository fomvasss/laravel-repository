<?php

namespace Fomvasss\Repository\Events;

/**
 * Class RepositoryEntityCreated
 *
 * @package Fomvasss\Repository\Events
 */
class RepositoryEntityCreated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "created";
}
