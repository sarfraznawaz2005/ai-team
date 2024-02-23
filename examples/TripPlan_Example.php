<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\DataProviders\LLMDataProvider;
use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Team;

// our api key
$apiKey = getenv('GEMINI_API_KEY');

// ask user which country they want to visit
$country = readline("Which country do you want to visit ?\n");

// define our team and overall goal
$tripPlanningteam = new Team("Suggest best city to visit in $country", new GoogleGeminiAI($apiKey));

// members
$cityExpert = (new Member(
    'City Selection Expert',
    "You are an expert in analyzing travel data to pick ideal destinations in $country",
    new GoogleGeminiAI($apiKey)
))
    ->assignTask('Come up with best city in provided country to visit based on weather, season and prices.')
    ->withData(function () use ($apiKey, $country) {

        // using built-in LLMDataProvider to provide some context data to our member using format we want.
        $llmDataProvider = new LLMDataProvider(
            new GoogleGeminiAI($apiKey),
            "Provide names of 10 cities in $country. Just list the names and nothing else."
        );

        return $llmDataProvider->get();
    });

$localCityExpert = (new Member(
    'Local City Expert',
    'A knowledgeable local guide with extensive information about the city',
    new GoogleGeminiAI($apiKey)
))->assignTask('Provide any further information about selected city that is important to know.');

$safetyExpert = (new Member(
    'City Safety Expert',
    'You are expert of all cities of world providing information about safety precautions of a city in different countries.',
    new GoogleGeminiAI($apiKey)
))->assignTask('Provide travel advisory and safety precautions for selected city');

// add members to the team

$tripPlanningteam->addMembers([$cityExpert, $localCityExpert, $safetyExpert]);

// get team of members to do their work
$result = $tripPlanningteam->performTasks();
echo $result;
