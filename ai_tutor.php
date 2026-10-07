
<?php

require_once "includes/auth.php";

?>

<!DOCTYPE html>

<html>

<head>

    <title>AI Programming Tutor</title>

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

    <div class="tutor-page">

        <h1>AI Programming Tutor</h1>

        <p class="tutor-intro">

            Ask the AI Tutor any programming question and get
            a simple explanation, examples and guidance.

        </p>


        <div class="tutor-container">


            <!-- Programming Language -->

            <label for="language">

                Programming Language

            </label>


            <select id="language">

                <option value="Java">

                    Java

                </option>

                <option value="Python">

                    Python

                </option>

                <option value="C++">

                    C++

                </option>

                <option value="C">

                    C

                </option>

                <option value="JavaScript">

                    JavaScript

                </option>

                <option value="PHP">

                    PHP

                </option>

            </select>


            <!-- Student Question -->

            <label for="question">

                Ask Your Programming Question

            </label>


            <textarea
                id="question"
                class="tutor-question"
                placeholder="Example: Explain constructors in Java in a simple way..."
            ></textarea>


            <!-- Ask Button -->

            <button
                type="button"
                class="tutor-button"
                onclick="askTutor()"
            >

                 Ask AI Tutor

            </button>


            <!-- AI Response -->

            <div
                id="tutor-result"
                class="tutor-result"
            >

                <div class="tutor-placeholder">

                    <div class="tutor-icon">

                        

                    </div>


                    <h2>

                        AI Tutor Ready

                    </h2>


                    <p>

                        Ask a programming question and
                        your AI Tutor will explain it here.

                    </p>

                </div>

            </div>

        </div>

    </div>

</main>


<script>


async function askTutor() {


    const language =
        document.getElementById("language").value;


    const question =
        document.getElementById("question").value;


    const result =
        document.getElementById("tutor-result");


    /*
        Check if question is empty
    */

    if (question.trim() === "") {

        result.innerHTML = `

            <div class="tutor-message warning">

                <h2>⚠️ No Question Entered</h2>

                <p>

                    Please enter a programming question
                    before asking the AI Tutor.

                </p>

            </div>

        `;

        return;

    }


    /*
        Show loading message
    */

    result.innerHTML = `

        <div class="tutor-loading">

            <div class="tutor-spinner"></div>

            <h2>

                 AI Tutor is thinking...

            </h2>

            <p>

                Preparing an explanation for you.

            </p>

        </div>

    `;


    try {


        /*
            Send question to PHP backend
        */

        const response = await fetch(

            "tutor.php",

            {

                method: "POST",

                headers: {

                    "Content-Type":
                        "application/json"

                },

                body: JSON.stringify({

                    language: language,

                    question: question

                })

            }

        );


        /*
            Check HTTP response
        */

        if (!response.ok) {

            throw new Error(

                "Server returned HTTP error " +
                response.status

            );

        }


        /*
            Convert response to JSON
        */

        const data =
            await response.json();


        console.log(

            "Tutor response:",

            data

        );


        /*
            Check backend error
        */

        if (!data.success) {

            result.innerHTML = `

                <div class="tutor-message error">

                    <h2>

                        ❌ AI Tutor Error

                    </h2>

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


        /*
            Display AI response
        */

        result.innerHTML = `

            <div class="tutor-response">

                <div class="tutor-response-header">

                    <h2>

                         AI Tutor Response

                    </h2>


                    <span class="tutor-language">

                        ${escapeHTML(
                            language
                        )}

                    </span>

                </div>


                <div class="tutor-answer">

                    ${formatTutorResponse(
                        data.result
                    )}

                </div>

            </div>

        `;


    }


    catch (error) {


        console.error(

            "Tutor error:",

            error

        );


        result.innerHTML = `

            <div class="tutor-message error">

                <h2>

                    ❌ Connection Error

                </h2>


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

function formatTutorResponse(text) {


    if (!text) {

        return `

            <p>

                No response was received from
                the AI Tutor.

            </p>

        `;

    }


    /*
        Escape HTML first
    */

    let formatted =
        escapeHTML(text);


    /*
        Format markdown headings
    */

    formatted = formatted.replace(

        /^### (.*?)$/gm,

        "<h3>$1</h3>"

    );


    formatted = formatted.replace(

        /^## (.*?)$/gm,

        "<h3>$1</h3>"

    );


    formatted = formatted.replace(

        /^# (.*?)$/gm,

        "<h2>$1</h2>"

    );


    /*
        Format bold text
    */

    formatted = formatted.replace(

        /\*\*(.*?)\*\*/g,

        "<strong>$1</strong>"

    );


    /*
        Format code blocks
    */

    formatted = formatted.replace(

        /```(?:java|python|javascript|php|cpp|c)?\s*([\s\S]*?)```/gi,

        "<pre><code>$1</code></pre>"

    );


    /*
        Convert line breaks
    */

    formatted = formatted.replace(

        /\n/g,

        "<br>"

    );


    return formatted;

}


/*
    Protect page from HTML
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

