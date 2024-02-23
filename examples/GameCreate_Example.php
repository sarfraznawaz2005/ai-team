<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Team;

$apiKey = getenv('GEMINI_API_KEY');

$SoftwareEngineer = (new Member(
    'Senior Software Engineer',
    'You are expert software engineer specializing in game development with over 10 years of experience.',
    new GoogleGeminiAI($apiKey)
))->assignTask(
    'Create a simple shooter game to shoot at enemies that can be played by pressing spacebar key to shoot at enemies. 
Enemies keep coming from the top and as the time passes, game should get harder and enemies keep on coming they never stop. 
Your final answer must be the html, css and javascript code, only the html, css and javascript code and nothing else.

You will get $1000 if you are able to create error-free, working and really playable game!

Below are rules you must follow:
- Make sure entire code is in SINGLE and in SAME index.html file.
- Do not use external packages or libraries.
- Game boundaries must inside window boundaries.
- Do not assume anything and provide full working code without comments.
'
);

$CodeReviewer = (new Member(
    'Senior Code Reviewer',
    'You are expert code reviewer.',
    new GoogleGeminiAI($apiKey)
))
    ->assignTask(
        'Your job is to do code review of code written by software engineer and make sure it has no errors. 
        If it has errors, you should fix those and provide corrected code in its entirety.'
    )
    // max 2 feedback attempts to try to get correct code answer from software engineer
    ->provideFeedbackTo([$SoftwareEngineer], 2);

$QAEngineer = (new Member(
    'Senior QA Engineer',
    'You are expert QA Engineer',
    new GoogleGeminiAI($apiKey)
))
    ->assignTask('Senior Code Reviewer will provide you code for a game, your job is to make sure game is playable
     and has no errors. Otherwise list the issues you identify.')
    // max 2 feedback attempts to try to get correct code answer from software engineer
    ->provideFeedbackTo([$SoftwareEngineer], 2);

$gameTeam = new Team();

$gameTeam
    ->addMembers([$SoftwareEngineer, $CodeReviewer, $QAEngineer])
    ->excludeResults([$CodeReviewer, $QAEngineer]);

$result = $gameTeam->performTasks();
echo $result;
