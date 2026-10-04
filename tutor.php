<?php

header("Content-Type: application/json");

ob_start();

/*
|--------------------------------------------------------------------------
| Error Handling
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set("display_errors", "0");

set_error_handler(function ($severity, $message, $file, $line) {

    throw new ErrorException(
        $message,
        0,
        $severity,
        $file,
        $line
    );

});


set_exception_handler(function ($exception) {

    ob_clean();

    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $exception->getMessage()
    ]);

    exit;

});


register_shutdown_function(function () {

    $error = error_get_last();

    if ($error !== null) {

        ob_clean();

        echo json_encode([
            "success" => false,
            "error" => "Fatal error: " . $error["message"]
        ]);

    }

});


/*
|--------------------------------------------------------------------------
| Load Gemini API Configuration
|--------------------------------------------------------------------------
*/

require_once "config/ai_config.php";


/*
|--------------------------------------------------------------------------
| Check API Key
|--------------------------------------------------------------------------
*/

if (
    !defined("GEMINI_API_KEY") ||
    empty(GEMINI_API_KEY) ||
    GEMINI_API_KEY === "PASTE_YOUR_GEMINI_KEY_HERE"
) {

    ob_clean();

    echo json_encode([
        "success" => false,
        "error" => "Gemini API key was not configured."
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Read JSON Request
|--------------------------------------------------------------------------
*/

$input = json_decode(
    file_get_contents("php://input"),
    true
);


if (!$input) {

    ob_clean();

    echo json_encode([
        "success" => false,
        "error" => "Invalid request data."
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Get Language and Question
|--------------------------------------------------------------------------
*/

$language = trim(
    $input["language"] ?? ""
);

$question = trim(
    $input["question"] ?? ""
);


/*
|--------------------------------------------------------------------------
| Validate Input
|--------------------------------------------------------------------------
*/

if ($language === "") {

    ob_clean();

    echo json_encode([
        "success" => false,
        "error" => "Programming language was not provided."
    ]);

    exit;

}


if ($question === "") {

    ob_clean();

    echo json_encode([
        "success" => false,
        "error" => "Please enter a programming question."
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Gemini Model
|--------------------------------------------------------------------------
*/

$model = "gemini-3.5-flash-lite";


/*
|--------------------------------------------------------------------------
| AI Tutor Prompt
|--------------------------------------------------------------------------
*/

$prompt = <<<PROMPT

You are an AI Programming Tutor inside a Programming Learning Assistant System.

The student is a diploma-level programming student.

Your job is to teach programming clearly and simply.

Programming Language:
$language

Student Question:
$question


IMPORTANT TEACHING RULES:

1. Explain the answer in simple beginner-friendly language.

2. Avoid unnecessary complicated terminology.

3. If technical terminology is necessary, explain it immediately.

4. Give a clear programming example when appropriate.

5. Explain the example step by step.

6. Mention common mistakes students should avoid.

7. Give the student one short practice question at the end.

8. Do not assume the student already understands advanced programming concepts.

9. If the student's question contains incorrect code, explain what is wrong and show corrected code.

10. Encourage learning rather than simply giving an answer.

Structure your response using these sections:

## Explanation

Explain the concept or answer clearly.

## Example

Give a simple example in $language when appropriate.

## How It Works

Explain the example step by step.

## Common Mistake

Mention an important mistake students should avoid.

## Practice

Give one short practice question for the student.

PROMPT;


/*
|--------------------------------------------------------------------------
| Gemini API URL
|--------------------------------------------------------------------------
*/

$url =
    "https://generativelanguage.googleapis.com/v1beta/models/"
    . $model .
    ":generateContent";


/*
|--------------------------------------------------------------------------
| Gemini Request
|--------------------------------------------------------------------------
*/

$data = [

    "contents" => [

        [

            "parts" => [

                [

                    "text" => $prompt

                ]

            ]

        ]

    ],

    "generationConfig" => [

        "temperature" => 0.3,

        "maxOutputTokens" => 1800

    ]

];


$jsonData = json_encode($data);


/*
|--------------------------------------------------------------------------
| Initialize cURL
|--------------------------------------------------------------------------
*/

$ch = curl_init($url);


curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS => $jsonData,

    CURLOPT_HTTPHEADER => [

        "Content-Type: application/json",

        "x-goog-api-key: " . GEMINI_API_KEY

    ],

    CURLOPT_TIMEOUT => 60,

]);


/*
|--------------------------------------------------------------------------
| Execute Request
|--------------------------------------------------------------------------
*/

$response = curl_exec($ch);


/*
|--------------------------------------------------------------------------
| Check cURL Error
|--------------------------------------------------------------------------
*/

if ($response === false) {

    $curlError = curl_error($ch);

    curl_close($ch);

    ob_clean();

    echo json_encode([

        "success" => false,

        "error" => "Gemini connection failed: " . $curlError

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Get HTTP Status
|--------------------------------------------------------------------------
*/

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


curl_close($ch);


/*
|--------------------------------------------------------------------------
| Check Gemini HTTP Response
|--------------------------------------------------------------------------
*/

if ($httpCode < 200 || $httpCode >= 300) {

    $errorData = json_decode(
        $response,
        true
    );

    $errorMessage =
        $errorData["error"]["message"]
        ?? "Gemini API returned an error.";

    ob_clean();

    echo json_encode([

        "success" => false,

        "error" =>
            "Gemini API Error (HTTP "
            . $httpCode
            . "): "
            . $errorMessage

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Decode Gemini Response
|--------------------------------------------------------------------------
*/

$result = json_decode(
    $response,
    true
);


if (!$result) {

    ob_clean();

    echo json_encode([

        "success" => false,

        "error" =>
            "Gemini returned an invalid response."

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Extract AI Response
|--------------------------------------------------------------------------
*/

$aiText = "";


if (
    isset($result["candidates"][0]["content"]["parts"])
) {

    foreach (
        $result["candidates"][0]["content"]["parts"]
        as $part
    ) {

        if (
            isset($part["text"])
        ) {

            $aiText .= $part["text"];

        }

    }

}


/*
|--------------------------------------------------------------------------
| Check Empty Response
|--------------------------------------------------------------------------
*/

if (trim($aiText) === "") {

    ob_clean();

    echo json_encode([

        "success" => false,

        "error" =>
            "The AI Tutor did not return an answer."

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Successful Response
|--------------------------------------------------------------------------
*/

ob_clean();

echo json_encode([

    "success" => true,

    "result" => $aiText

]);

exit;

?>