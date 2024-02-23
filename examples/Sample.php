<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\LocalAI;
use Sarfraznawaz2005\AiTeam\Team;

$llm = new LocalAI('my-api-key', ['api_end_point' => 'http://localhost:1234/v1/chat/completions']);

$SoftwareEngineer = (
new Member('Junior Software Engineer', 'You are an junior level software engineer who often makes coding mistakes.', $llm))
    ->assignTask(
        '
        You need to write a program to display the Fibonacci sequence up to 5th term using javascript. Do not explain
        anything, just provide the code.
        '
    );

$CodeReviewer = (new Member('Code Reviewer', 'You are an Expert Code Reviewer with over 10 years of experience.', $llm))
    ->assignTask('You have to identify code errors and just inform about those nothing else. Your answer must not be more than 30 words.')
    // max 2 feedback attempts to try to get correct code answer from software engineer
    ->provideFeedbackTo([$SoftwareEngineer], 2);

$QAEngineer = (new Member(
    'Senior QA Engineer',
    'You are expert QA Engineer',
    $llm
))
    ->assignTask('Your job is to make sure code works without any errors. Otherwise list the issues you identify.')
    // max 2 feedback attempts to try to get correct code answer from software engineer
    ->provideFeedbackTo([$SoftwareEngineer], 2);

$myTeam = new Team();

$myTeam
    ->addMembers([$SoftwareEngineer, $CodeReviewer, $QAEngineer])
    ->excludeResults([$CodeReviewer, $QAEngineer]);

$result = $myTeam->performTasks();
echo $result;
