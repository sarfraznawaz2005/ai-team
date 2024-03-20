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

    private string $finalResult;

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

        $this->aiTeamLogo();
    }

    private function aiTeamLogo(): void
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
     * @return Team
     * @throws Exception
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
            if ($memberResult) {
                $membersOverAllResult .= "$memberName:\n\n$memberResult\n\n";
            }
        }

        $this->finalResult = $membersOverAllResult;

        if (!$this->overallGoal) {
            return Helper::Text('FINAL TEAM RESULT:', 'green', 'bold') . "\n$this->finalResult";
        }

        $finalPrompt = $this->overallGoal . "\n\n" . self::INSTRUCTION_WORDS . "\n\n$this->finalResult";

        $this->finalResult = $this->llmProvider->generateText($finalPrompt);

        return Helper::Text('FINAL TEAM RESULT:', 'green', 'bold') . "$this->finalResult\n";
    }

    /**
     * @param string $filePath Where to save results.
     * @param bool $removeMemberNames Remove member names from final result
     * @return $this
     */
    public function saveToFile(string $filePath, bool $removeMemberNames = false): static
    {
        if ($removeMemberNames) {
            foreach ($this->members as $member) {
                $this->finalResult = str_replace($member->name . ":\n\n", '', $this->finalResult);
            }
        }

        if (file_put_contents($filePath, $this->finalResult)) {
            Helper::outputText('FILE SAVED!', 'green', 'bold');
        } else {
            Helper::outputText('Could not save file!', 'red', 'bold');
        }

        return $this;
    }
}
