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

$apiKey = getenv('GEMINI_API_KEY');

// define our team and overall goal
$myTeam = new Team('Provide short introduction of the cricketer.', new GoogleGeminiAI($apiKey));

// define members with roles, goals and tasks with same or different LLMs/AI models

$Researcher = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey)))
    ->assignTask('Provide the list of cricketers with more than one centuries.')
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

$Analyst = (new Member('Analyst', 'You are an Analyst', new GoogleGeminiAI($apiKey)))
    ->assignTask('Retrieve the name of a cricketer with most centuries.');

// add members to the team
$myTeam->addMembers([$Researcher, $Analyst]);

// get team of members to do their work
$result = $myTeam->performTasks();
echo $result;

```

:raised_hands: Result:

```bash
Researcher performing the task:

Role: You are a Researcher

Task: Provide the list of cricketers with more than one centuries.

Researcher performed task with result:

1. Sachin Tendulkar (100 centuries)
2. Ricky Ponting (71 centuries)
3. Virat Kohli (70 centuries)


Analyst performing the task:

Role: You are an Analyst

Task: Retrieve the name of a cricketer with most centuries.

Analyst performed task with result:

Sachin Tendulkar


FINAL TEAM RESULT:

Sachin Tendulkar is a legendary Indian cricketer widely regarded as one of the greatest batsmen in the history of the sport. Known as the "Master Blaster," Tendulkar holds the record for most centuries (100) in international cricket.
```

[See more examples here](https://github.com/sarfraznawaz2005/ai-team/tree/main/examples)

## How it works ?

Each member performs his tasks and passes result to next member as context. Next members uses that information as context
to perform his task and provide own results and so on. Therefore result of each member is passed to next to build
collective knowledge and infer results based on that.

## Supported LLMs

- OpenAI
- Google Gemini ([They provide reasonable free plan](https://ai.google.dev/tutorials/rest_quickstart))
- Local LLMs ([see example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/LocalAI_Example.php))

LLM Signature:

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
(string $name, string $role, LLMProvider $llmProvider, bool $verbose = true)
```

```php

$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey)))
 ->assignTask("Perform Awesome Task!");
```

Providing Context Data to Member:

```php
$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey)))
 ->assignTask("Perform Awesome Task!")
  ->withData(function () {
  // this could come from your database or api for example.
  return <<<data
  Some Data
  data;
 });
```

or pass a class that should implement [DataProviderInterface](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/Contracts/DataProviderInterface.php) interface:

```php
$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey)))
 ->assignTask("Perform Awesome Task!")
 ->withData(new MyDataProvider());
```

Two built-in data providers are provided by the package:

- [LLMDataProvider](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/DataProviders/LLMDataProvider.php): Provides results for given prompt by asking LLM model. [See Example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/TripPlan_Example.php)
- [UrlDataProvider](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/DataProviders/UrlDataProvider.php): Provides results for given URL by asking LLM model. [See Example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/UrlResearch_Example.php)

**Creating a Team**

Signature:

```php
(string $overallGoal = '', LLMProvider $llmProvider = null, ExecutionInterface $execution = null, int $executionDelayMilliSeconds = 500)
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
$myTeam->addMembers([$researcher, $analyst]);
```

Third parameter of `Team` class is execution type and currently only `SequentialExecution` type is supported.

- [SequentialExecution](https://github.com/sarfraznawaz2005/ai-team/blob/main/src/Executions/SequentialExecution.php): In this mode, all members are run sequentially in the order passed to `addMembers` method. In this mode, each members passes his result to next member as context and so on.

**Getting Final Results**

Call to get final team result:

```php
echo $myTeam->performTasks();
```

**Note** As can be seen in above examples, by passing LLM instance with options, team and members can use different LLMs and models specialized for their tasks.

**Using a Member without a Team**

It is possible to use one or members to get the job done without being part of a team:

```php
$awesomeMember = (new Member('Researcher', 'You are a Researcher', new GoogleGeminiAI($apiKey)))
 ->assignTask("Perform Awesome Task!");

 $awesomeMember->performTask();
 $result = $awesomeMember->getResult();
```

[See Example](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/CodeGenerator_Example.php)

## Enabling Collaboration Between Members

Collaboration or communication between members to achieve given task can be enabled via `provideFeedbackTo` method.
It has following signature:

```php
(array $members, int $maxFeedbacks = 3, int $feedbackDelayMilliSeconds = 2, bool $exitOnFailedFeedback = false)
```

- `$members` specifies which members to collaborate with.
- `$maxFeedbacks` specifies total number of attempts to get desired results from `$members`
- `$feedbackDelayMilliSeconds` specifies delay time before new communication attempt is made
- `$exitOnFailedFeedback` specifies whether to exit the program if collaboration fails.

Let's understand with an example. Let's say we want to create a game. For this we create three AI members:

- Software Engineer
- Code Reviewer
- QA Engineer

We want to make sure code written by Software Engineer is reviewed by Code Reviewer and its QA is done by QA Engineer.
If at any point, either of Code Reviewer or QA Engineer find any issues in game code written by Software Engineer, they
would inform him about his mistake until hopefully Software Engineer comes up with acceptable result. Here is how we can
put this together:

```php
$apiKey = getenv('GEMINI_API_KEY');

$SoftwareEngineer = (new Member(
    'Senior Software Engineer',
    'You are expert software engineer specializing in game development with over 10 years of experience.',
    new GoogleGeminiAI($apiKey)
))->assignTask(
    'Create a simple shooter game to shoot at enemies that can be played by pressing spacebar key to shoot at enemies. 
Enemies keep coming from the top and as the time passes, game should get harder and enemies keep on coming they never stop. 
Your final answer must be the html, css and javascript code, only the html, css and javascript code and nothing else.

You will get $100 if you are able to create error-free, working and really playable game!

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
     and you have no errors. Otherwise list the issues you identify.')
    // max 2 feedback attempts to try to get correct code answer from software engineer
    ->provideFeedbackTo([$SoftwareEngineer], 2);

$gameTeam = new Team();

$gameTeam
    ->addMembers([$SoftwareEngineer, $CodeReviewer, $QAEngineer])
    ->excludeResults([$CodeReviewer, $QAEngineer]);

$result = $gameTeam->performTasks();
echo $result;
```

It would result in something like:

```bash
Senior Software Engineer performing the task:

Role: You are expert software engineer specializing in game development with over 10 years of experience.

Task: Create a simple shooter game to shoot at enemies that can be played by pressing spacebar key to shoot at enemies.
Enemies keep coming from the top and as the time passes, game should get harder and enemies keep on coming they never stop.
Your final answer must be the html, css and javascript code, only the html, css and javascript code and nothing else.

Senior Software Engineer performed task with result:

[actual code written by software engineer skipped for brevity]

Senior Code Reviewer has entered into feedback loop with Senior Software Engineer.

Senior Code Reviewer has provided following feedback to Senior Software Engineer:

Seems like game is not working properly, fix the code so that it works as expected.

Feedback #1:

Senior Software Engineer has replied with following updated answer:

[actual code written by software engineer skipped for brevity]

Senior Code Reviewer has provided following feedback to Senior Software Engineer:

No further feedback

Successful collaboration between Senior Code Reviewer and Senior Software Engineer!

Senior Code Reviewer exiting the feedback loop with Senior Software Engineer.

Senior QA Engineer has entered into feedback loop with Senior Software Engineer.

Senior QA Engineer has provided following feedback to Senior Software Engineer:

Well done.

Senior QA Engineer is satisfied with answer of Senior Software Engineer!

Senior QA Engineer exiting the feedback loop with Senior Software Engineer.


FINAL TEAM RESULT:

Senior Software Engineer:

[actual code written by software engineer skipped for brevity]
```

[See Example Code Here](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/GameCreate_Example.php) with [Actual Output Here](https://github.com/sarfraznawaz2005/ai-team/blob/main/examples/GameCreate_Example_Output.txt)

Notice the use of `excludeResults` method in above example. If used, the results of that member will not be shown in
final team output and will not be passed to any other member except for whom they are in feedback/communication loop.

## Note

Although inspired by excellent [CrewAI](https://github.com/joaomdmoura/crewai) python package, AI-Team ended up being
different and simple.

Not recommended to be used in production, watch out for costs, use at your own risk!

**Probably I won't be updating this package much (hence no release created) due to lack of time but I hope someone in PHP community comes up with better and robust package for PHP as PHP Community deserves too!. CrewAI is good starting point to follow.**

**PRs are welcomed though.**
