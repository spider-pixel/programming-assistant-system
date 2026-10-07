<?php

require_once "includes/auth.php";

?>

<!DOCTYPE html>

<html>

<head>

    <title>AI Code Debugger</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<nav>

    <h2>Programming Learning Assistant</h2>

    <a href="dashboard.php">Dashboard</a>

    <a href="lessons.php">Lessons</a>

    <a href="ai_tutor.php">AI Tutor</a>

    <a href="debugger.php">Debugger</a>

    <a href="quiz.php">Quiz</a>

    <a href="progress.php">Progress</a>

    <a href="logout.php">Logout</a>

</nav>


<main>

    <h1>AI Code Debugger</h1>

    <p>
        Enter your programming code below and the system will analyze it
        and help you identify possible errors.
    </p>


    <div class="debugger-container">

        <label for="language">
            Programming Language
        </label>

        <select id="language">

            <option value="java">Java</option>

            <option value="python">Python</option>

            <option value="cpp">C++</option>

            <option value="c">C</option>

            <option value="javascript">JavaScript</option>

            <option value="php">PHP</option>

        </select>


        <label for="code">
            Enter Your Code
        </label>

        <textarea
            id="code"
            placeholder="Paste or type your code here..."
        ></textarea>


        <button
            type="button"
            onclick="debugCode()"
        >
             Debug Code
        </button>


        <div id="result" class="debug-result">

            <h2>Debugging Result</h2>

            <p>
                Your debugging results will appear here.
            </p>

        </div>

    </div>

</main>


<script>

async function debugCode() {

    const language =
        document.getElementById("language").value;

    const code =
        document.getElementById("code").value;

    const result =
        document.getElementById("result");


    // Check if code is empty

    if (code.trim() === "") {

        result.innerHTML = `

            <h2>⚠️ No Code Entered</h2>

            <p>
                Please enter some code before debugging.
            </p>

        `;

        return;
    }


    // Show loading message

    result.innerHTML = `

        <h2>AI is analyzing your code...</h2>

        <p>
            Please wait while the AI checks your code.
        </p>

    `;


    try {

        // Send code to PHP backend

        const response = await fetch(
            "debug_code.php",
            {

                method: "POST",

                headers: {

                    "Content-Type":
                        "application/json"

                },

                body: JSON.stringify({

                    language: language,

                    code: code

                })

            }
        );


        // Check HTTP response

        if (!response.ok) {

            throw new Error(
                "Server returned HTTP error " +
                response.status
            );

        }


        // Convert response to JSON

        const data =
            await response.json();


        // Show backend response in browser console

        console.log(
            "Backend response:",
            data
        );


        // Check if backend reported an error

        if (!data.success) {

            result.innerHTML = `

                <h2>❌ AI Error</h2>

                <div class="ai-response">

                    <p>
                        ${escapeHTML(
                            data.error ||
                            "Unknown error occurred."
                        )}
                    </p>

                </div>

            `;

            return;
        }


        // Display successful AI response

        result.innerHTML = `

            <h2> AI Debugging Analysis</h2>

            <div class="ai-response">

                ${formatAIResponse(data.result)}

            </div>

        `;

    }


    catch (error) {

        console.error(
            "Debugger error:",
            error
        );


        result.innerHTML = `

            <h2>❌ Connection Error</h2>

            <div class="ai-response">

                <p>
                    <strong>Error:</strong>
                </p>

                <p>
                    ${escapeHTML(
                        error.message
                    )}
                </p>

                <p>
                    Please check that the AI backend
                    is running correctly.
                </p>

            </div>

        `;

    }

}


/*
    Format AI response
*/

function formatAIResponse(text) {

    if (!text) {

        return "<p>No AI response was received.</p>";

    }


    return escapeHTML(text)

        .replace(
            /```([\s\S]*?)```/g,
            "<pre>$1</pre>"
        )

        .replace(
            /\n/g,
            "<br>"
        );

}


/*
    Protect the page from HTML
    returned by the AI
*/

function escapeHTML(text) {

    const div =
        document.createElement("div");

    div.textContent = text;

    return div.innerHTML;

}

</script>

</body>

</html>

