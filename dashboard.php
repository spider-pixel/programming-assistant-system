<?php

require_once "includes/auth.php";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Student Dashboard</title>

    <link rel="stylesheet" href="css/style.css">
<script src="theme.js"></script>
</head>

<body>


<nav>
    <h2>Programming Learning Assistant</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="lessons.php">Lessons</a>
    <a href="ai_tutor.php">AI Tutor</a>
    <a href="debugger.php">Debugger</a>
    <a href="quiz.php">Quiz</a>
    <a href="progress.php">Track Progress</a>

    <button type="button" class="theme-toggle" id="themeToggle"
            aria-label="Switch to dark mode">
         Dark Mode
    </button>

    <a href="logout.php">Logout</a>
</nav>


<main>

    <h1>
        Welcome,
        <?php echo htmlspecialchars($_SESSION["name"]); ?>!
    </h1>

    <p>
        Welcome to your AI-powered programming learning assistant.
    </p>


    <!-- Programming Lessons -->

    <div class="card">

        <h3>Programming Lessons</h3>

        <p>
            Learn programming concepts and examples.
        </p>

        <a href="lessons.php">
            Start Learning
        </a>

    </div>


    <!-- AI Programming Tutor -->

    <div class="card">

        <h3>AI Programming Tutor</h3>

        <p>
            Ask questions and get programming explanations
            from your AI Tutor.
        </p>

        <a href="ai_tutor.php" class="dashboard-card">
            Open AI Tutor
        </a>

    </div>


    <!-- Code Debugger -->

    <div class="card">

        <h3>Code Debugger</h3>

        <p>
            Analyze programming code and identify errors.
        </p>

        <a href="debugger.php">
            Open Code Debugger
        </a>

    </div>


    <!-- Programming Quiz -->

    <div class="card">

        <h3>Programming Quiz</h3>

        <p>
            Test your programming knowledge.
        </p>

        <a href="quiz.php">
            Start Quiz
        </a>

    </div>


    <!-- Learning Progress -->

    <div class="card">

        <h3>Learning Progress</h3>

        <p>
            Monitor your programming learning progress.
        </p>

        <a href="progress.php">
            View My Progress
        </a>

    </div>

</main>

</body>

</html>

