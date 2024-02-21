<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Executions\SequentialExecution;
use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Task;
use Sarfraznawaz2005\AiTeam\Team;

$apiKey = getenv('GEMINI_API_KEY');

$SeniorResearchAnalyst = (new Member(
    'Senior Research Analyst',
    'You are an expert at a technology research group, skilled in identifying trends and analyzing complex data.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task("Analyze 2024's AI advancements. Find major trends, new technologies, and their effects. Provide a detailed report.")
);

$TechContentStrategist = (new Member(
    'Tech Content Strategist',
    'You are a content strategist known for making complex tech topics interesting and easy to understand.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task('Create a blog post about major AI advancements using your insights. Make it interesting, clear, and suited for tech enthusiasts. It should be at least 4 paragraphs long.')
);

$blogTeam = new Team('See the findings of experts and write an engaging and consolidated blog post based on their findings.', new GoogleGeminiAI($apiKey));

$blogTeam
    ->addMembers([$SeniorResearchAnalyst, $TechContentStrategist])
    ->withExecutionType(new SequentialExecution());

$result = $blogTeam->performTasks();
echo $result;
