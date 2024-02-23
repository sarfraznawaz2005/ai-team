<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sarfraznawaz2005\AiTeam\Member;
use Sarfraznawaz2005\AiTeam\Providers\GoogleGeminiAI;

// our api key
$apiKey = getenv('GEMINI_API_KEY');

// this example does not use team but a single member is tasked with a single job

$webDesigner =
    (new Member('Web Designer', 'You are Expert Web Designer with over 10 years of experience.', new GoogleGeminiAI($apiKey)))
        ->assignTask(
            '
        Consider the following JSON Schema based on the 2020-12 specification:
        
        ```json
        
        [{
            "file_name": "index.html or styles.css",
            "code": "code for index.html or styles.css"
        }]
        
        ```
        
        This JSON Schema represents the format I want you to follow to generate your answer.
        
        Now, generate a JSON object that will contain the following information:
        
        Generate HTML and CSS for a modern website homepage. Include a responsive navbar, hero section with
        a call-to-action button, a services section showcasing three services, a testimonial slider, and a footer. Focus on a
        clean, professional aesthetic suitable for a tech company. Create two files; index.html and styles.css. styles.css
        will be in same folder as index.html file. For images, use images placeholder service such as picsum.photos or some other.
        
        '
        );

// get result performed by designer
$webDesigner->verbose = true;
$webDesigner->performTask();
$result = $webDesigner->getResult();

// todo: there should be better way of getting json

// normalize results as model
$result = trim(str_ireplace(['json', 'JSON', '`', "Web Designer's Findings:"], '', $result));

$data = json_decode($result, true);

// check if $data is valid json
if (json_last_error() === JSON_ERROR_NONE) {
    // now let's actually create these files in our file system.
    foreach ($data as $code) {
        file_put_contents($code['file_name'], $code['code']);
        echo 'Files Generated!';
    }
} else {
    // sometimes model cannot generate correct json so might true to run few times.
    echo "not valid json returned";
}
