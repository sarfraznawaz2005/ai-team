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
    'Please write HTML & CSS for an admin dashboard template for "My Web Store". 
    It must have navbar, sidebar. It should be responsive and modern. It should also 
    have dropdown on top right corner with user and setting links and other links that are
    necessary for the admin dashboard. Prefer bootstrap for admin dashboard.

	Below are rules you must follow:
	- Make sure entire code is in SINGLE and in SAME index.html file including CSS.
	- Use CDN for latest versions.
	- Please do not put "crossorigin" integrity check in links.
	- Please do not assume anything and provide full working code without comments.
	'
);

$CodeReviewer = (new Member(
    'Senior Code Reviewer',
    'You are an expert code reviewer.',
    new GoogleGeminiAI($apiKey)
))->assignTask('Please do code review code of Senior Web Designer and notify him about any issues in it.')
    // max 2 feedback attempts to try to get correct code answer from software engineer
    ->provideFeedbackTo([$WebDesigner], 2);


$team = (new Team())
    ->addMembers([$WebDesigner, $CodeReviewer])
    ->excludeResults([$CodeReviewer]);

$team->performTasks();

// save results to file
$team->saveToFile('dashboard.html', true);