<?php

// Always return JSON
header("Content-Type: application/json");

// Prevent PHP warnings/notices from being mixed into our JSON response
ob_start();


// --------------------------------------------------
// Error handler
// --------------------------------------------------

set_error_handler(function ($severity, $message, $file, $line) {

    echo json_encode([
        "success" => false,
        "error" => "PHP Error: " . $message,
        "details" => "File: " . $file . " | Line: " . $line
    ]);

    exit;

});


// --------------------------------------------------
// Fatal error handler
// --------------------------------------------------

register_shutdown_function(function () {

    $error = error_get_last();

    if ($error !== null) {

        $fatalTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR
        ];

        if (in_array($error["type"], $fatalTypes)) {

            // Clear anything PHP already printed
            if (ob_get_length()) {
                ob_clean();
            }

            echo json_encode([
                "success" => false,
                "error" => "PHP Fatal Error: " . $error["message"],
                "details" =>
                    "File: " . $error["file"] .
                    " | Line: " . $error["line"]
            ]);
        }
    }

});


// --------------------------------------------------
// Load Gemini API configuration
// --------------------------------------------------

require_once "config/ai_config.php";


// Check API key exists
if (!defined("GEMINI_API_KEY") || empty(GEMINI_API_KEY)) {

    echo json_encode([
        "success" => false,
        "error" => "Gemini API key was not found."
    ]);

    exit;
}


// --------------------------------------------------
// Check request method
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "error" => "Invalid request method. Use POST."
    ]);

    exit;
}


// --------------------------------------------------
// Get JSON request
// --------------------------------------------------

$rawInput = file_get_contents("php://input");

$input = json_decode($rawInput, true);


if ($input === null) {

    echo json_encode([
        "success" => false,
        "error" => "The server could not read the JSON request."
    ]);

    exit;
}


// --------------------------------------------------
// Get language and code
// --------------------------------------------------

$language = $input["language"] ?? "";

$code = $input["code"] ?? "";


if (empty(trim($code))) {

    echo json_encode([
        "success" => false,
        "error" => "No programming code was provided."
    ]);

    exit;
}


// --------------------------------------------------
// Create AI prompt
// --------------------------------------------------

$prompt = "

You are an AI programming tutor and code debugger.

A student submitted code written in {$language}.

Analyze the code carefully.

Your job is to help the student understand and correct the code.

Use these sections:

ERRORS

Identify all syntax, logical, or runtime errors.

LOCATION

Explain where each error occurs.
Mention the line number when possible.

EXPLANATION

Explain each error in simple language suitable
for a programming student.

CORRECTED CODE

Provide the complete corrected version of the code.

LEARNING TIP

Give a short programming tip that will help
the student avoid this type of error in the future.

If the code has no errors, clearly explain that
the code appears correct.

Do not criticize the student.

Programming language:

{$language}

Student code:

{$code}

";


// --------------------------------------------------
// Gemini model
// --------------------------------------------------

$model = "gemini-3.5-flash-lite";


// Gemini REST API
$url =
    "https://generativelanguage.googleapis.com/v1beta/models/" .
    $model .
    ":generateContent";


// --------------------------------------------------
// Prepare request
// --------------------------------------------------

$requestData = [

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

        "temperature" => 0.2,

        "maxOutputTokens" => 1500

    ]

];


$jsonData = json_encode($requestData);


// --------------------------------------------------
// Check cURL
// --------------------------------------------------

if (!function_exists("curl_init")) {

    echo json_encode([
        "success" => false,
        "error" =>
            "PHP cURL is not enabled in XAMPP."
    ]);

    exit;
}


// --------------------------------------------------
// Send request to Gemini
// --------------------------------------------------

$ch = curl_init($url);


curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [

    "Content-Type: application/json",

    "x-goog-api-key: " . GEMINI_API_KEY

]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

curl_setopt($ch, CURLOPT_TIMEOUT, 60);


// Execute request

$response = curl_exec($ch);


// --------------------------------------------------
// Check cURL error
// --------------------------------------------------

if ($response === false) {

    $curlError = curl_error($ch);

    curl_close($ch);

    echo json_encode([

        "success" => false,

        "error" =>
            "Could not connect to Gemini.",

        "details" =>
            $curlError

    ]);

    exit;
}


// --------------------------------------------------
// Get HTTP information
// --------------------------------------------------

$httpCode =
    curl_getinfo($ch, CURLINFO_HTTP_CODE);


curl_close($ch);


// --------------------------------------------------
// Decode Gemini response
// --------------------------------------------------

$result = json_decode($response, true);


// --------------------------------------------------
// Handle invalid Gemini response
// --------------------------------------------------

if ($result === null) {

    echo json_encode([

        "success" => false,

        "error" =>
            "Gemini returned a response that was not valid JSON.",

        "http_code" =>
            $httpCode,

        "response" =>
            substr($response, 0, 1000)

    ]);

    exit;
}


// --------------------------------------------------
// Handle Gemini API error
// --------------------------------------------------

if ($httpCode < 200 || $httpCode >= 300) {

    $errorMessage =
        "Gemini API returned HTTP " .
        $httpCode;


    if (
        isset(
            $result["error"]["message"]
        )
    ) {

        $errorMessage =
            $result["error"]["message"];

    }


    echo json_encode([

        "success" => false,

        "error" => $errorMessage,

        "http_code" => $httpCode

    ]);

    exit;
}


// --------------------------------------------------
// Extract AI response
// --------------------------------------------------

$aiText = "";


if (
    isset(
        $result["candidates"][0]["content"]["parts"]
    )
) {

    foreach (
        $result["candidates"][0]["content"]["parts"]
        as $part
    ) {

        if (isset($part["text"])) {

            $aiText .=
                $part["text"];

        }

    }

}


// --------------------------------------------------
// Check AI response
// --------------------------------------------------

if (empty(trim($aiText))) {

    echo json_encode([

        "success" => false,

        "error" =>
            "Gemini did not return any text.",

        "response" => $result

    ]);

    exit;
}


// --------------------------------------------------
// Success
// --------------------------------------------------

echo json_encode([

    "success" => true,

    "result" => $aiText

]);

?>