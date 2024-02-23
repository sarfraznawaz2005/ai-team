<?php

namespace Sarfraznawaz2005\AiTeam;

use Sarfraznawaz2005\AiTeam\Contracts\DataProviderInterface;
use Sarfraznawaz2005\AiTeam\Contracts\LLMProvider;

class Member
{
    public string $name;
    public string $role;
    public bool $excludeReply = false;
    public bool $verbose = true;
    
    private LLMProvider $llmProvider;
    private mixed $data;
    private string $task;
    private string $result;

    /**
     * @var Member[] Array of members
     */
    private array $feedbackMembers = [];
    private int $maxFeedbacks;
    private int $feedbackDelayMilliSeconds;
    private bool $exitOnFailedFeedback;

    public const MEMBER_RULES = [
        '------------------------------------------------',
        'Rules You Must Follow For Your Reply:',
        '1. You must not ask any questions.',
        '2. You must not argue.',
        '------------------------------------------------',
    ];

    public const INSTRUCTION_WORDS = 'Your answer must be based on answers below by fellow members:';

    /**
     * @param string $name
     * @param string $role
     * @param LLMProvider $llmProvider
     * @param bool $verbose
     */
    public function __construct(string $name, string $role, LLMProvider $llmProvider, bool $verbose = true)
    {
        $this->data = null;
        $this->name = $name;
        $this->role = $role;
        $this->llmProvider = $llmProvider;
        $this->verbose = $verbose;
    }

    /**
     * @param DataProviderInterface|callable $data
     * @return $this
     */
    public function withData(DataProviderInterface|callable $data): static
    {
        $this->data = $data instanceof DataProviderInterface ? $data->get() : call_user_func($data);

        return $this;
    }

    public function assignTask(string $task): static
    {
        $this->task = $task;

        return $this;
    }

    /**
     * @param array $members
     * @param int $maxFeedbacks
     * @param int $feedbackDelayMilliSeconds
     * @param bool $exitOnFailedFeedback
     * @return $this
     */
    public function provideFeedbackTo(array $members, int $maxFeedbacks = 3, int $feedbackDelayMilliSeconds = 2, bool $exitOnFailedFeedback = false): static
    {
        $this->feedbackMembers = $members;
        $this->maxFeedbacks = $maxFeedbacks;
        $this->feedbackDelayMilliSeconds = $feedbackDelayMilliSeconds;
        $this->exitOnFailedFeedback = $exitOnFailedFeedback;

        return $this;
    }

    public function performTask(array $previousMemberResults = []): void
    {
        $this->provideFeedbackIfNeeded();

        if ($this->excludeReply) {
            $this->result = '';

            return;
        }

        $prompt = $this->generatePrompt($this->task, $previousMemberResults);

        if (!is_null($this->data)) {
            $prompt .= "\n\nInformation/Data:\n" . $this->data;
        }

        //Helper::outputText($prompt, 'blue');

        $result = $this->getLLMResult($prompt);

        if ($this->verbose) {
            Helper::outputText($this->name . " performed task with result:", 'green');
            Helper::outputText("$result\n", 'white');
        }

        $this->result = $result;
    }

    public function generatePrompt(string $taskDescription, array $previousMemberResults = []): string
    {
        $previousResultsStr = '';

        if ($previousMemberResults) {
            $previousResultsStr .= "\n\n" . self::INSTRUCTION_WORDS . "\n\n";

            foreach ($previousMemberResults as $memberName => $memberResult) {
                $previousResultsStr .= "\n\n" . $memberName . "'s" . " Answer:\n";
                $previousResultsStr .= $memberResult;
            }
        }

        $roleTask = "Role: " . $this->role . "\n\nTask: " . $taskDescription;

        if ($this->verbose) {
            // let's not show feedback prompts
            // TODO: prompting texts should not be hard-coded in this class.
            if (!str_contains(strtolower($roleTask), 're-write and correct your answer based on suggestions given')) {
                Helper::outputText($this->name . " performing the task:", 'green');
                Helper::outputText($roleTask, 'yellow');
            }
        }

        return $roleTask . $previousResultsStr;
    }

    public function getLLMResult(string $prompt): string
    {
        $rules = implode(PHP_EOL, self::MEMBER_RULES);

        $prompt = $rules . PHP_EOL . PHP_EOL . $prompt;

        //Helper::outputText($prompt, 'blue', 'bold');

        return $this->llmProvider->generateText($prompt);
    }

    public function getResult(): string
    {
        return $this->result;
    }

    private function provideFeedbackIfNeeded(): void
    {
        // see if any members needs feedback
        if ($this->feedbackMembers) {

            foreach ($this->feedbackMembers as $member) {
                $retryCount = 0;

                if ($this->verbose) {
                    Helper::outputText("$this->name has entered into feedback loop with $member->name.", 'green');
                }

                do {

                    usleep($this->feedbackDelayMilliSeconds * 1000); // sleep a little

                    if ($retryCount < $this->maxFeedbacks) {
                        $retryCount++;
                    } else {
                        if ($this->verbose) {
                            Helper::outputText("$this->name finished the feedback loop with $member->name with no success!\n", 'red');
                        }

                        break;
                    }

                    $feedback = $this->assessResultAndGenerateFeedback($member);

                    if ($feedback->isSuccessful() && $this->verbose) {
                        if ($retryCount === 1) {
                            Helper::outputText("$this->name is satisfied with answer of $member->name!", 'green');
                        } else {
                            Helper::outputText("Successful collaboration between $this->name and $member->name!", 'green');
                            Helper::outputText("$member->name has replied with satisfying answer!", 'green');
                        }

                        Helper::outputText("$this->name exiting the feedback loop with $member->name.\n", 'green');
                        break;
                    }

                    if ($this->exitOnFailedFeedback) {
                        exit;
                    }

                    if ($this->verbose) {
                        Helper::outputText("\nFeedback #$retryCount:", 'green');
                    }

                    // request member to re-evaluate his answer based on feedback
                    $feedbackPrompt = <<<feedback
                    \n\n
                    Your last answer was:
                    \n > $member->result\n
                    ---
                    \n\n
                    However, you have received following feedback from $this->name, you must respect suggestions given.
                    Please re-write and correct your answer based on suggestions given by $this->name below:
                    \n\n
                    Suggestions By $this->name:
                    > $feedback->suggestion\n
                    feedback;

                    $prompt = $member->generatePrompt($member->task . $feedbackPrompt);
                    //Helper::outputText($prompt, 'blue');

                    $result = $this->getLLMResult($prompt);

                    // save new result for the member
                    $member->result = $result;

                    if ($this->verbose) {
                        Helper::outputText("\n$member->name has replied with following updated answer:", 'green');
                        Helper::outputText($member->result, 'white');
                    }
                } while (!$feedback->isSuccessful());
            }
        }
    }

    private function assessResultAndGenerateFeedback(Member $member): Feedback
    {
        #######################################################
        # For Testing
        #######################################################
//        if (1) {
//            if ($this->verbose) {
//                Helper::outputText("\n$this->name has provided following feedback to $member->name:", 'green');
//                Helper::outputText("\n" . 'code has a syntax error', 'yellow');
//            }
//
//            return new Feedback(false, 'code has a syntax error');
//        }
        #######################################################

        $feedbackMemberName = $member->name;
        $feedbackMemberRole = $member->role;
        $feedbackMemberTask = $member->task;
        $feedbackMemberResult = $member->result;

        $prompt = $this->generatePrompt($this->task);

        $prompt .= <<<prompt
        \n
        Please provide feedback to $feedbackMemberName whose answer is below.
        Your feedback must be realistic and correct without any assumptions and
        must only be based on answer given below by $feedbackMemberName. Please
        never reply with 'no feedback';

        ---

        \n$feedbackMemberName's role is '$feedbackMemberRole' and he was assigned this task: '$feedbackMemberTask'
        and his answer was:\n
        > $feedbackMemberResult\n
        ---

        \nFor your feedback, you must respect following JSON format all the times:

        {
            "impression" : "Reply with one word 'Positive' if firstly you are satisfied with answer of $feedbackMemberName and secondly you don't have any suggestions for him. Otherwise reply with one word 'Negative'.",
            "feedback": "Your feedback here based on $feedbackMemberName's answer"
        }
        prompt;

        $result = $this->getLLMResult($prompt);
        $result = str_ireplace(['```json', '```JSON', '```'], '', $result);

        $json = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // skip in case of errors
            return new Feedback(true, "Everything looks good to me!");
        }

        // nothing to do if no suggestion
        if (!trim($json['feedback'])) {
            return new Feedback(true, "Everything looks good to me!");
        }

        if ($this->verbose) {
            Helper::outputText("\n$this->name has provided following feedback to $member->name:", 'green');
            Helper::outputText("\n" . $json['feedback'], 'yellow');
        }

        return new Feedback(str_contains(strtolower($json['impression']), 'positive'), $json['feedback']);
    }
}
