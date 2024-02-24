<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Team;

$apiKey = getenv('GEMINI_API_KEY');

$WebDesigner = (new Member(
    'Senior Web Designer',
    'You are an expert web designer with over 10 years of experience.',
    new GoogleGeminiAI($apiKey)
))->assignTask(
    'Create an admin dashboard template using bootstrap 5 for "My Web Store". It must
    have navbar, sidebar. Template must be responsive and modern, it must have black 
    navbar and sidebar but white middle content area. It should also have dropdown on 
    top right corner with user and setting links. Put all links that are necessary for 
    an admin dashboard. You must provide code with all html and css in it without 
    separate files.

	Below are rules you must follow:
	- Make sure entire code is in SINGLE and in SAME index.html file including CSS.
	- Use CDN for latest version of bootstrap.
	- Do not put crossorigin integrity stuff in links.
	- Do not assume anything and provide full working code without comments.
	'
);

$CodeReviewer = (new Member(
    'Senior Code Reviewer',
    'You are expert code reviewer.',
    new GoogleGeminiAI($apiKey)
))->assignTask('Your job is to do code review code of Web Designer and notify about any issues in it.')
    // max 2 feedback attempts to try to get correct code answer from software engineer
    ->provideFeedbackTo([$WebDesigner], 2);


$team = (new Team())
    ->addMembers([$WebDesigner, $CodeReviewer])
    ->excludeResults([$CodeReviewer]);

$team->performTasks();

// save results to file
$team->saveToFile('dashboard.html');