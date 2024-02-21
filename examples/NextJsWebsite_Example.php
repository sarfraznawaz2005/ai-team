<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Executions\SequentialExecution;
use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Task;
use Sarfraznawaz2005\AiTeam\Team;

// our api key
$apiKey = getenv('GEMINI_API_KEY');

// define our team and overall goal

$nextJSWebsiteteam = new Team(
    "Based on information provided, create nextjs components in following format:\nComponent Name:\nComponent Path:\nComponent Code:",
    new GoogleGeminiAI($apiKey)
);

// define members with roles, goals and tasks with same or different AI models

$designer = (new Member(
    'Web Designer',
    'You are Expert Web Designer with over 10 years of experience.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task("Generate HTML and CSS for a modern Next.js website homepage. Include a responsive navbar, hero section with
	 a call-to-action button, a services section showcasing three services, a testimonial slider, and a footer. Focus on a
	 clean, professional aesthetic suitable for a tech company. For images, use images placeholder service. Use cdn for css
	 and js where possible.")
);

$softwareEngineer = (new Member(
    'Software Engineer',
    'You are Expert Software Engineer with over 10 years of experience.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task('Translate the HTML and CSS provided by Web Designer into into Next.js components. Ensure the website is optimized
	 for performance. Implement dynamic rendering for the services section using server-side rendering for initial load and
	 client-side rendering for interaction. Include a simple contact form in the footer that submits to a placeholder URL.')
);

// add members to the team

$nextJSWebsiteteam
    ->addMembers([$designer, $softwareEngineer]) // order matters here in case of SequentialExecution
    ->withExecutionType(new SequentialExecution());

// get team of members to do their work
$result = $nextJSWebsiteteam->performTasks();
echo $result;
