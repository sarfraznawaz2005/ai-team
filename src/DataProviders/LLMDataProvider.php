<?php

namespace Sarfraznawaz2005\AiTeam\DataProviders;

use Sarfraznawaz2005\AiTeam\Contracts\DataProviderInterface;
use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;

/**
 * Provides results for given prompt by asking LLM model. It can be used in "withData"
 * callback for a member for example to provide further context to a member.
 */
class LLMDataProvider implements DataProviderInterface
{
    private LLMProvider $llm;
    private string $prompt;

    public function __construct(LLMProvider $llm, string $prompt)
    {
        $this->llm = $llm;
        $this->prompt = $prompt;
    }

    public function get(): string
    {
        return $this->llm->generateText($this->prompt);
    }
}
