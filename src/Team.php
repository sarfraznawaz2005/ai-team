<?php

namespace Sarfraznawaz2005\AiTeam;

use Exception;
use Sarfraznawaz2005\AiTeam\Contracts\ExecutionInterface;
use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;
use Sarfraznawaz2005\AiTeam\Executions\SequentialExecution;

class Team
{
    private ?LLMProvider $llmProvider;
    private ?ExecutionInterface $execution;
    private string $overallGoal;

    private const INSTRUCTION_WORDS = "Build your answer based on findings in following Context:\n\nContext:";

    /**
     * @param Member[] $members Array of members
     */
    private array $members = [];

    /**
     * @param string $overallGoal
     * @param LLMProvider|null $llmProvider
     * @param ExecutionInterface|null $execution
     * @param int $executionDelayMilliSeconds delay time between members before getting result of each member
     */
    public function __construct(string $overallGoal = '', LLMProvider $llmProvider = null, ExecutionInterface $execution = null, int $executionDelayMilliSeconds = 500)
    {
        $this->overallGoal = $overallGoal;
        $this->llmProvider = $llmProvider;
        $this->execution = $execution ?: new SequentialExecution($executionDelayMilliSeconds);

        $this->aiTEamLogo();
    }

    private function aiTEamLogo(): void
    {
        echo "
         █████  ██       ████████ ███████  █████  ███    ███ 
        ██   ██ ██          ██    ██      ██   ██ ████  ████ 
        ███████ ██ █████    ██    █████   ███████ ██ ████ ██ 
        ██   ██ ██          ██    ██      ██   ██ ██  ██  ██ 
        ██   ██ ██          ██    ███████ ██   ██ ██      ██         
        \n\n";
    }

    /**
     * @param Member[] $members Array of members
     * @throws Exception If a member with the same name already exists
     */
    public function addMembers(array $members): static
    {
        foreach ($members as $member) {
            $name = $member->name;

            foreach ($this->members as $existingMember) {
                if ($existingMember->name === $name) {
                    throw new Exception("Member with name '$name' already exists.");
                }
            }

            $this->members[] = $member;
        }

        return $this;
    }

    /**
     * @param Member[] $resultExcludeMembers Array of members
     * @return $this
     */
    public function excludeResults(array $resultExcludeMembers): static
    {
        foreach ($resultExcludeMembers as $member) {
            $member->excludeReply = true;
        }

        return $this;
    }

    public function performTasks(): string
    {
        $membersOverAllResult = '';

        $results = $this->execution->executeWork($this->members);

        foreach ($results as $memberName => $memberResult) {
            $membersOverAllResult .= "$memberName:\n\n$memberResult\n\n";
        }

        if (!$this->overallGoal) {
            return Helper::Text('FINAL TEAM RESULT:', 'green', 'bold') . "\n$membersOverAllResult";
        }

        // remove members instructions
        $membersOverAllResult = str_ireplace(Member::INSTRUCTION_WORDS, '', $membersOverAllResult);

        $finalPrompt = $this->overallGoal . "\n\n" . self::INSTRUCTION_WORDS . "\n\n$membersOverAllResult";

        $finalResult = $this->llmProvider->generateText($finalPrompt);

        return Helper::Text('FINAL TEAM RESULT:', 'green', 'bold') . "$finalResult\n";
    }
}
