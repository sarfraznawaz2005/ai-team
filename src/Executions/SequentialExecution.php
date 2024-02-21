<?php

namespace Sarfraznawaz2005\AiTeam\Executions;

use Sarfraznawaz2005\AiTeam\Contracts\ExecutionInterface;
use Sarfraznawaz2005\AiTeam\Member;

/**
 * In this, each member passes his findings to next agent as context and so on.
 * Each member is run in order provided in Team's addMembers method.
 * 
 */
class SequentialExecution implements ExecutionInterface
{
    /**
     * @var int The delay time in milliseconds
     */
    private $delayMilliSeconds;

    /**
     * SequentialExecution constructor.
     *
     * @param int $delayMilliSeconds The delay time in milliseconds
     */
    public function __construct(int $delayMilliSeconds = 500)
    {
        $this->delayMilliSeconds = $delayMilliSeconds;
    }

    /**
     * @param Member[] $members Array of members
     * @return array Array of results
     */
    public function executeWork(array $members): array
    {
        $results = [];

        foreach ($members as $index => $member) {
            usleep($this->delayMilliSeconds * 1000);

            // Get the previous results
            $previousResults = array_slice($results, 0, $index);

            // Perform the task using the previous results
            $currentResult = $member->performTask($previousResults);

            // Remove role info of previous members from current member's task
            $currentResult = preg_replace('#' . $member->getRole() . '#', '', $currentResult);

            $results[] = $currentResult;
        }

        return $results;
    }
}
