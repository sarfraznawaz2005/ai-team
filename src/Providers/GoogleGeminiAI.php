<?php

namespace Sarfraznawaz2005\AiTeam\Providers;

use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;
use Sarfraznawaz2005\AiTeam\Exceptions\AITeamException;
use Sarfraznawaz2005\AiTeam\Helper;

class GoogleGeminiAI implements LLMProvider
{
    private string $apiKey;

    private array $options = ['model' => 'gemini-pro', 'api_end_point' => 'https://generativelanguage.googleapis.com/v1/models/'];

    /**
     * @param string $apiKey
     * @param array $options
     * @see https://ai.google.dev/tutorials/rest_quickstart
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @throws AITeamException
     */
    public function generateText(string $prompt): string
    {
        $this->options['contents'] = [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
            ],
        ];

        $this->options['generationConfig'] = [
            'maxOutputTokens' => 4096,
            //'temperature' => 0.5,
        ];

        $this->options['safetySettings'] = [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_ONLY_HIGH',
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_ONLY_HIGH',
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_ONLY_HIGH',
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_ONLY_HIGH',
            ],
        ];

        $apiUrl = $this->options['api_end_point'] . $this->options['model'] . ':generateContent?key=' . $this->apiKey;

        $postFields = $this->options;

        if (isset($postFields['api_end_point'])) {
            unset($postFields['api_end_point']);
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
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

        print_r($responseData);
        throw new AITeamException('No or invalid response! Make sure you have specified correct API key.');
    }
}
