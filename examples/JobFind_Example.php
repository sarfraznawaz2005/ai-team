<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Executions\SequentialExecution;
use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;
use Sarfraznawaz2005\AiTeam\Task;
use Sarfraznawaz2005\AiTeam\Team;

require_once __DIR__ . '/DataProviders/JobSearcherDataProvider.php';

$apiKey = getenv('GEMINI_API_KEY');

// define our team and overall goal

$jobFindingTeam = new Team("Find the best jobs", new GoogleGeminiAI($apiKey));

// define members with roles, goals and tasks with same or different AI models

$JobSearcher = (new Member(
    'Job Searcher',
    'You are actively searching for job opportunities in your field, ready to utilize and expand your skill set in a new role.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task("Search for current job openings for the Senior Data Scientist role in New York using the provided information.
		Find 5 vacant positions in total. Emphasize the key skills required. Reply in JSON format with the
    	following schema: {'role': '<role>', 'location': '<location>', 'num_results': <number>}.
    	Ensure to format the input accordingly.")
)->withData(new JobSearcherDataProvider());

$SkillsDevelopmentAdvisor = (new Member(
    'Skills Development Advisor',
    'As a skills development advisor, you assist job searchers in identifying crucial skills for their target roles and
	recommend ways to develop these skills.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task('Based on the identified job openings, list the key skills required for each position separately.
    Provide recommendations on how candidates can acquire or improve these skills through courses, self-study,
	or practical experience.')
);

$InterviewPreparationCoach = (new Member(
    'Interview Preparation Coach',
    'Expert in coaching job searchers on successful interview techniques, including mock interviews and feedback.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task('Prepare job searchers for interviews by conducting mock interviews and offering feedback on their responses,
	presentation, and communication skills, for each role separately.')
);

$CareerAdvisor = (new Member(
    'Career Advisor',
    'Experienced in guiding candidates through their job search journey, offering personalized advice on career development
	 and application processes.',
    new GoogleGeminiAI($apiKey),
    true
))->assignTask(
    new Task('Offer guidance on resume building, optimizing LinkedIn profiles, and effective networking strategies to enhance
	 job application success, for each role separately.')
);

// add members to the team
$jobFindingTeam
    ->addMembers([$JobSearcher, $SkillsDevelopmentAdvisor, $InterviewPreparationCoach, $CareerAdvisor])
    ->withExecutionType(new SequentialExecution());

// get team of members to do their work
$result = $jobFindingTeam->performTasks();
echo $result;
