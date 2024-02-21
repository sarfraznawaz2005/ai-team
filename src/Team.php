<?php

namespace Sarfraznawaz2005\AiTeam;

use Sarfraznawaz2005\AiTeam\Contracts\ExecutionInterface;
use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;

class Team
{
    private ?LLMProvider $llmProvider;
    private ExecutionInterface $execution;

    /**
     * @param Member[] Array of members
     */
    private $members = [];

    private string $overallGoal;

    public const INSTRUCTION_WORDS = "Build your answer based on findings in following Context:\n\nContext:";

    public function __construct(string $overallGoal = '', LLMProvider $llmProvider = null)
    {
        $this->overallGoal = $overallGoal;
        $this->llmProvider = $llmProvider;
    }

    public function withExecutionType(ExecutionInterface $execution)
    {
        $this->execution = $execution;

        return $this;
    }

    /**
     * @param Member[] Array of members
     */
    public function addMembers(array $members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * @param Member[] $members Array of members
     */
    public function getMembers()
    {
        return $this->members;
    }

    public function performTasks()
    {
        $results = $this->execution->executeWork($this->members);

        $membersOverAllResult = implode(PHP_EOL, $results);

        if (!$this->overallGoal) {
            return 'FINAL TEAM RESULT:' . $membersOverAllResult . PHP_EOL . PHP_EOL;
        }

        // remove members instructions
        $membersOverAllResult = str_ireplace(Member::INSTRUCTION_WORDS, '', $membersOverAllResult);

        $finalPrompt = $this->overallGoal . "\n\n" . self::INSTRUCTION_WORDS . "\n\n$membersOverAllResult";

        $finalResult = $this->llmProvider->generateText($finalPrompt);

        // append members result
        $finalResult = $membersOverAllResult . PHP_EOL . PHP_EOL . Helper::Text('FINAL TEAM RESULT:', 'green', 'bold') . $finalResult . PHP_EOL . PHP_EOL;

        return $finalResult;
    }
}
