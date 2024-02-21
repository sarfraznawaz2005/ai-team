<?php

namespace Sarfraznawaz2005\AiTeam\Contracts;

use Sarfraznawaz2005\AiTeam\Member;

interface ExecutionInterface
{
    /**
     * @param Member[] $members Array of members
     */
    public function executeWork(array $members);
}
