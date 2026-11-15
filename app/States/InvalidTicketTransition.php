<?php
// Author: Lai Chee Kit
// Module 4: State Pattern. Thrown when a state does not allow an action.

namespace App\States;

use RuntimeException;

class InvalidTicketTransition extends RuntimeException
{
    public function __construct(string $stateName, string $action)
    {
        parent::__construct("A ticket that is {$stateName} cannot be changed with the action \"{$action}\".");
    }
}
