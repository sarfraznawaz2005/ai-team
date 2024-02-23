<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Team;

// our api key
$apiKey = getenv('GEMINI_API_KEY');

// define our team and overall goal
$myTeam = new Team('Please provide details about cricketer with most centuries.', new GoogleGeminiAI($apiKey));

// define members with roles, goals and tasks with same or different AI models

$Researcher = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey)))
    ->assignTask('Provide the list of cricketers with more than one centuries.')
    ->withData(function () {
        // this could come from your database or api for example.
        return <<<data
		Name: Sachin Tendulkar
		Centuries: 100

		Name: Ricky Ponting
		Centuries: 71

		Name: Virat Kohli
		100s: 70

		Name: John Doe
		Centuries: 0
		data;
    });

$Analyst = (new Member('Analyst', 'You are an Analyst', new GoogleGeminiAI($apiKey)))
    ->assignTask('Retrieve the name of a cricketer with most centuries.');

// add members to the team
$myTeam->addMembers([$Researcher, $Analyst]);

// get team of members to do their work
$result = $myTeam->performTasks();
echo $result;
