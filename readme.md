# :robot: AI-Team

A package allowing to create team of AI members that can work and collaborate together to achieve a common goal.

## Installation

`composer require sarfraznawaz2005/ai-team:dev-main`

## Workflow

1. :busts_in_silhouette: Create Team (with optional overall goal)
2. :bust_in_silhouette: Create Members (with optional data provided to them)
3. :clipboard: Define Role of Members
4. :pencil: Assign Tasks to Members
5. :running: Run

## Example

Here we create a team with overall goal along with two members assigned with tasks.

```php

// our api key
$apiKey = getenv('GEMINI_API_KEY');

// define our team and overall goal
$myTeam = new Team('Who is cricketer with most centuries?', new GoogleGeminiAI($apiKey));

// define members with roles, goals and tasks with same or different LLMs

$Researcher = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey), true))
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

$Analyst = (new Member('Analyst', 'You are an Analyst', new GoogleGeminiAI($apiKey), true))
 ->assignTask(new Task('Retrieve the name of a cricketer with most centuries.'));

// add members to the team
$myTeam
 ->addMembers([$Researcher, $Analyst]) // order matters here in case of SequentialExecution
 ->withExecutionType(new SequentialExecution());

// get team of members to do their work
$result = $myTeam->performTasks();
echo $result;

```

:raised_hands: Result:

```bash
Researcher has started working...

Researcher proceeding with following details:

ROLE: You are a Researcher

TASK: Provide the list of cricketers with more than one centuries.

Information/Data:

Name: Sachin Tendulkar
Centuries: 100

Name: Ricky Ponting
Centuries: 71

Name: Virat Kohli
100s: 70

Name: John Doe
Centuries: 0

Researcher has finished working with result:

- Sachin Tendulkar (100 centuries)
- Ricky Ponting (71 centuries)
- Virat Kohli (70 centuries)

Analyst has started working...

Analyst proceeding with following details:

ROLE: You are an Analyst

TASK: Retrieve the name of a cricketer with most centuries.

Analyst has finished working with result:

Sachin Tendulkar

Researcher's Findings:

- Sachin Tendulkar (100 centuries)
- Ricky Ponting (71 centuries)
- Virat Kohli (70 centuries)


Analyst's Findings:

Sachin Tendulkar

FINAL TEAM RESULT:

Sachin Tendulkar
```

[See more examples here](https://github.com/sarfraznawaz2005/ai-team/tree/main/examples)

## How it works ?

With execution type set to `SequentialExecution`, each member performs his tasks and passes result to next member as context.
Next members uses that information as context to perform his task and provide own results and so on. Therefore result of each
member is passed to next.

## Supported LLMs

- OpenAI
- Google Gemini ([They provide reasonable free plan](https://ai.google.dev/tutorials/rest_quickstart))
- Local LLMs ([see example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/LocalAI_Example.php))

**LLM Signature**

```php
(string $apiKey, array $options = [])
```

In `$options`, it is possible to pass LLM settings such as `model`, `temperature`, `topP`, etc. Make sure it's as per the LLM requirements/format.

### Adding Your Own LLMs

To add your own LLM providers, implement the [LLMProvider](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/Contracts/LLMProvider.php) interface:

```php
interface LLMProvider {
    public function generateText(string $prompt): string;
}
```

## Usage Details

**Creating a Team Member**

Signature:

```php
(string $name, string $role, LLMProvider $llmProvider, bool $verbose = false)
```

```php

$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey), true))
 ->assignTask(new Task("Perform Awesome Task!"));
 ```

Providing Context Data to Member:

```php
$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey), true))
 ->assignTask(new Task("Perform Awesome Task!"))
  ->withData(function () {
  // this could come from your database or api for example.
  return <<<data
  Some Data
  data;
 });
```

or pass a class that should implement [DataProviderInterface](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/Contracts/DataProviderInterface.php) interface:

```php
$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey), true))
 ->assignTask(new Task("Perform Awesome Task!"))
 ->withData(new MyDataProvider());
```

Two built-in data providers are provided by the package:

- [LLMDataProvider](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/DataProviders/LLMDataProvider.php): Provides results for given prompt by asking LLM model. [See Example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/TripPlan_Example.php)
- [UrlDataProvider](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/DataProviders/UrlDataProvider.php): Provides results for given URL by asking LLM model. [See Example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/UrlResearch_Example.php)

**Creating a Team**

Signature:

```php
(string $overallGoal = '', LLMProvider $llmProvider = null)
```

```php
$myTeam = new Team();
```

or with overall goal of the team in which case after all members have returned results,
LLM will be used again against overall goal with context as result of all members.

```php
$myTeam = new Team("Team's overall goal here...", new GoogleGeminiAI($apiKey));
```

Adding members to the team:

```php
$myTeam
 ->addMembers([$researcher, $analyst])
 ->withExecutionType(new SequentialExecution());
```

Execution Type can be `SequentialExecution` or `ParallelExecution`

- [SequentialExecution](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/Executions/SequentialExecution.php): In this mode, all members are run sequentially in the order passed to `addMembers` method.
    In this mode, each members passes his result to next member as context and so on.

- [ParallelExecution](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/Executions/ParallelExecution.php): In this mode, all members perform thier tasks independently without passing any information
    to next member. They just perform their assigned tasks and return the results individually.

To add your own execution types, implement [ExecutionInterface](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/Contracts/ExecutionInterface.php) interface.

**Getting Final Results**

Call to get final team result:

```php
echo $myTeam->performTasks();
```

**Note** As can be seen in above examples, by passing LLM instance, team and members can use different LLMs specialized for their tasks.

**Using a Member without a Team**

It is possible to use a one or members to get the job done without being part of a team:

```php
$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey), true))
 ->assignTask(new Task("Perform Awesome Task!"));

 echo $awesomeMember->performTask();
```

[See Example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/CodeGenerator_Example.php)

## Note

Although inspired by excellent [CrewAI](https://github.com/joaomdmoura/crewai/) python package, AI-Team ended up being
different and simple.

Not recommended to be used in production, watch out for costs, use at your own risk!

**Probably I won't be updating this package much (hence no release created) due to lack of time but I hope someoen in PHP community comes up with better and robust package for PHP. CrewAI is good starting point.**
