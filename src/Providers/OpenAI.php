<?php
namespace Sarfraznawaz2005\AiTeam\Providers;

use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;
use Sarfraznawaz2005\AiTeam\Exceptions\AITeamException;

class OpenAI implements LLMProvider
{
    private string $apiKey;

    private array $options = ['model' => 'gpt-3.5-turbo', 'api_end_point' => 'https://api.openai.com/v1/chat/completions'];

    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->options = array_merge($this->options, $options);
    }

    public function generateText(string $prompt): string
    {
        $this->options['messages'] = [
            [
                "role" => "user",
                "content" => $prompt,
            ],
        ];

        $ch = curl_init($this->options['api_end_point']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->options));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || empty($response)) {
            throw new AITeamException($error);
        }

        $responseData = json_decode($response, true);

        if (isset($responseData['choices'][0]['message']['content'])) {
            return $responseData['choices'][0]['message']['content'];
        }
        
        if (isset($responseData['error'])) {
            throw new AITeamException($responseData['error']['message']);
        }

        throw new AITeamException('No or invalid response! Make sure you have specified correct API key.');
    }
}
