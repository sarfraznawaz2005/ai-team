<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Executions\SequentialExecution;
use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\LocalAI;
use Sarfraznawaz2005\AiTeam\Task;
use Sarfraznawaz2005\AiTeam\Team;

$localLLM = new LocalAI('my-api-key', ['api_end_point' => 'http://localhost:1234/v1/chat/completions']);

// define our team and overall goal
$myTeam = new Team('Who is cricketer with most centuries?', $localLLM);

// define members with roles, goals and tasks with same or different AI models

$Researcher = (new Member('Researcher', 'You are a Researcher', $localLLM, true))
    ->assignTask(new Task("Provide the list of cricketers with more than one centuries."))
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

$Analyst = (new Member('Analyst', 'You are an Analyst', $localLLM, true))
    ->assignTask(new Task('Retrieve the name of a cricketer with most centuries.'));

// add members to the team
$myTeam
    ->addMembers([$Researcher, $Analyst]) // order matters here in case of SequentialExecution
    ->withExecutionType(new SequentialExecution());

// get team of members to do their work
$result = $myTeam->performTasks();
echo $result;
