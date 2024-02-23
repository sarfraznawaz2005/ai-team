<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\DataProviders\UrlDataProvider;
use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;

// our api key
$apiKey = getenv('GEMINI_API_KEY');

$Researcher = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey)))
    ->assignTask('Provide the list of top 5 news items from proivded data related to technology especially.')
    ->withData(function () use ($apiKey) {

        // using built-in UrlDataProvider to provide some context data to our member.
        $llmDataProvider = new UrlDataProvider(
            new GoogleGeminiAI($apiKey),
            'https://news.ycombinator.com/newest'
        );

        return $llmDataProvider->get();
    });

$Researcher->performTask();
$result = $Researcher->getResult();
echo $result;
