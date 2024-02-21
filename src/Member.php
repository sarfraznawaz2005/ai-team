<?php

namespace Sarfraznawaz2005\AiTeam;

use Sarfraznawaz2005\AiTeam\Contracts\DataProviderInterface;
use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;

class Member
{
    private LLMProvider $llmProvider;
    private Task $task;

    private string $name;
    private string $role;
    private $data;

    private bool $verbose;

    public const INSTRUCTION_WORDS = 'Build your answer based on following findings:';

    public const MEMBER_RULES = [
        '------------------------------------------------',
        'Rules You Must Follow For Your Reply:',
        '1. You must not ask any questions.',
        '2. You must not argue.',
        '------------------------------------------------',
    ];

    public function __construct(string $name, string $role, LLMProvider $llmProvider, bool $verbose = false)
    {
        $this->data = null;
        $this->name = $name;
        $this->role = $role;
        $this->verbose = $verbose;
        $this->llmProvider = $llmProvider;
    }

    public function withData(DataProviderInterface | callable $data)
    {
        if (is_callable($data)) {
            $this->data = $data();
        } else {
            $this->data = $data->get();
        }

        return $this;
    }

    public function assignTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function performTask(array $previousMemberResults = [])
    {
        $rules = implode(PHP_EOL, self::MEMBER_RULES);

        $prompt = '';

        if ($this->verbose) {
            echo Helper::Text($this->name . ' has started working...', 'green', 'bold');
        }

        if (empty($previousMemberResults)) {
            $prompt = 'ROLE: ' . $this->role . "\n\nTASK: " . $this->task->getDescription();
        } else {
            $previousResults = implode("\n\n", $previousMemberResults);
            $prompt = 'ROLE: ' . $this->role . "\n\nTASK: " . $this->task->getDescription() . "\n\n" . self::INSTRUCTION_WORDS . "\n\n" . $previousResults;
        }

        if (!is_null($this->data)) {
            $prompt = $prompt . "\n\nInformation/Data:\n\n" . $this->data;
        }

        if ($this->verbose) {
            echo Helper::Text($this->name . ' proceeding with following details:', 'green', 'bold');
            echo Helper::Text($prompt, 'white', 'bold');
        }

        $result = $this->llmProvider->generateText($rules . PHP_EOL . PHP_EOL . $prompt);

        if ($this->verbose) {
            echo Helper::Text($this->name . " has finished working with result:", 'green', 'bold');
            echo Helper::Text($result, 'white', 'bold');
        }

        return "\n\n$this->name's Findings:\n\n" . $result;
    }
}
