<?php

namespace Sarfraznawaz2005\AiTeam\Executions;

use Exception;
use Sarfraznawaz2005\AiTeam\Contracts\ExecutionInterface;
use Sarfraznawaz2005\AiTeam\Exceptions\AITeamException;
use Sarfraznawaz2005\AiTeam\Member;

/**
 * In this, each member performs independently without passing any info to next agent.
 * All members are run in parallel mode.
 * 
 */
class ParallelExecution implements ExecutionInterface
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
    public function __construct(int $delayMilliSeconds = 100)
    {
        $this->delayMilliSeconds = $delayMilliSeconds;
    }

    /**
     * @param Member[] $members Array of members
     * @return array Array of results
     */
    public function executeWork(array $members): array
    {
        if (!function_exists('pcntl_fork')) {
            throw new AITeamException('pcntl extension is required.');
        }

        return $this->runParallel($members);
    }

    private function runParallel(array $members)
    {
        $results = [];
        $children = [];

        foreach ($members as $member) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                // Handle fork failure
                continue; // Optionally, decide how to handle this failure: skip, retry, or abort
            } elseif ($pid) {
                // Parent process
                $children[$pid] = true;
            } else {
                // Child process
                try {
                    $results[] = $member->performTask();
                } catch (Exception $e) {
                    throw new AITeamException(get_class($member) . ' - execution failed: ' . $e->getMessage());
                }

                exit(0);
            }
        }

        // Non-blocking wait for all child processes to finish
        while (!empty($children)) {
            foreach ($children as $pid => $_) {
                $status = null;
                $res = pcntl_waitpid($pid, $status, WNOHANG);

                if ($res == -1) {
                    // Handle waitpid failure
                    throw new AITeamException("Failed to wait for process $pid");
                    unset($children[$pid]); // Remove the child from the list to avoid infinite loop
                } elseif ($res > 0) {
                    // Child has exited
                    unset($children[$pid]);
                }
            }

            usleep($this->delayMilliSeconds * 1000);
        }

        return $results;
    }
}
