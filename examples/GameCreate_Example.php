<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Executions\SequentialExecution;
use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Task;
use Sarfraznawaz2005\AiTeam\Team;

$apiKey = getenv('GEMINI_API_KEY');

$SoftwareEngineer = (new Member(
    'Senior Software Engineer',
    'You are expert software engineer specializing in game development with over 10 years of experience.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task("Create a simple shooter game using spacebar to shoot at enemies. Your final answer must be the html, css and javascript code, only the html, css and javascript code and nothing else. This is your last chance to win $100 if you are able to create error-free and working game!
    
		Below are rules you must follow:
		- Make sure entire code is in SINGLE and SAME index.html file.
		- Do not use external packages or libraries.
		- Game boundaries must inside window boundaries.
		- Do not assume anything and provide full working code without comments.
	")
);

$CodeReviewer = (new Member(
    'Senior Code Reviewer',
    'You are expert code reviewer.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task('Your job is to do code review of code written by software engineer and make sure it has no errors. If it has errors, you should fix those and provide corrected code in its entirety.')
);

$QAEngineer = (new Member(
    'Senior QA Engineer',
    'You are expert QA Engineer',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task('Make sure game created by software engineer is playable without any issues.')
);

$gameTeam = new Team();

$gameTeam
    ->addMembers([$SoftwareEngineer, $CodeReviewer, $QAEngineer])
    ->withExecutionType(new SequentialExecution());

$result = $gameTeam->performTasks();
echo $result;
