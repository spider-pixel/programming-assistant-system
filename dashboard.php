<?php

require_once "includes/auth.php";

?>

<!DOCTYPE html>

<html>

<head>

    <title>Student Dashboard</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<nav>

    <h2>Programming Learning Assistant</h2>

    <a href="dashboard.php">Dashboard</a>

    <a href="#">Lessons</a>

    <a href="#">AI Tutor</a>

    <a href="debugger.php">Debugger</a>

    <a href="#">Quiz</a>

    <a href="#">Progress</a>

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

    <div class="card">

    <h3>Programming Lessons</h3>

    <p>
        Learn programming concepts and examples.
    </p>

    <a href="lessons.php">
        Start Learning
    </a>

</div>

        <div class="card">

            <a href="ai_tutor.php" class="dashboard-card">

    <h3> AI Programming Tutor</h3>

    <p>
        Ask questions and get programming explanations
        from your AI Tutor.
    </p>

</a>

        <div class="card">

    <h3>Code Debugger</h3>

    <p>
        Analyze programming code and identify errors.
    </p>

    <a href="debugger.php">
        Open Code Debugger
    </a>

</div>

        <div class="card">

            <h3>Programming Quiz</h3>

            <p>
                Test your programming knowledge.
            </p>

        </div>

        <div class="card">

            <h3>Learning Progress</h3>

            <p>
                Monitor your programming learning progress.
            </p>

        </div>

    </div>

</main>

</body>

</html>