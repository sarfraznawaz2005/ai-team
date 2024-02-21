<?php

namespace Sarfraznawaz2005\AiTeam\Providers;

use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;
use Sarfraznawaz2005\AiTeam\Exceptions\AITeamException;

class GoogleGeminiAI implements LLMProvider
{
    private string $apiKey;

    private array $options = ['model' => 'gemini-pro', 'api_end_point' => 'https://generativelanguage.googleapis.com/v1/models/'];

    // see on how to pass further model options: https://ai.google.dev/tutorials/rest_quickstart
    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->options = array_merge($this->options, $options);
    }

    public function generateText(string $prompt): string
    {
        $this->options['contents'] = [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
            ],
        ];

        $apiUrl = $this->options['api_end_point'] . $this->options['model'] . ':generateContent?key=' . $this->apiKey;

        unset($this->options['api_end_point']);

        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->options));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new AITeamException($error);
        }

        $responseData = json_decode($response, true);

        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($responseData['error'])) {
            throw new AITeamException($responseData['error']['message']);
        }

        throw new AITeamException('No or invalid response! Make sure you have specified correct API key.');
    }
}
