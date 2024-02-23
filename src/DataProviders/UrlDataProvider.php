<?php

namespace Sarfraznawaz2005\AiTeam\DataProviders;

use Sarfraznawaz2005\AiTeam\Contracts\DataProviderInterface;
use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;
use Sarfraznawaz2005\AiTeam\Exceptions\AITeamException;

/**
 * Provides results for given URL by asking LLM model. It can be used in "withData"
 * callback for a member for example to provide further context to a member.
 */
class UrlDataProvider implements DataProviderInterface
{
    private LLMProvider $llm;
    private string $url;
    private string $customPrompt;

    /**
     * @param LLMProvider $llm
     * @param string $url
     * @param string $customPrompt
     */
    public function __construct(LLMProvider $llm, string $url, string $customPrompt = '')
    {
        $this->llm = $llm;
        $this->url = $url;
        $this->customPrompt = $customPrompt;
    }

    /**
     * @throws AITeamException
     */
    public function get(): string
    {
        // Validate the URL
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new AITeamException('Invalid URL!');
        }

        $options = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $context = stream_context_create($options);
        $contents = file_get_contents($this->url, false, $context);

        if ($contents === false) {
            throw new AITeamException('Unable to fetch data!');
        }

        $prompt = <<<prompt
        You are amazing Researcher on the web content.

        Analyze below content and provided detailed summary with all relevant information in the summary.
        Do not tell anything about url/page itself, just the content on it. Your answer must be pure text
        without any html, etc.

        Content:
        $contents
        prompt;

        if ($this->customPrompt) {
            $prompt = <<<prompt
            $this->customPrompt

            Analyze below content for your answer.

            Content:
            $contents
            prompt;
        }

        return strip_tags($this->llm->generateText($prompt));
    }
}
