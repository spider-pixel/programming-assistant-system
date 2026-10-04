<?php

require_once "includes/auth.php";
require_once "config/database.php";

if (!isset($_GET["id"])) {
    header("Location: lessons.php");
    exit();
}

$lesson_id = intval($_GET["id"]);
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION["user_id"];

    $stmt = $conn->prepare(
        "INSERT INTO learning_progress
        (user_id, lesson_id, completed, completed_at)
        VALUES (?, ?, TRUE, NOW())

        ON DUPLICATE KEY UPDATE
        completed = TRUE,
        completed_at = NOW()"
    );

    $stmt->bind_param(
        "ii",
        $user_id,
        $lesson_id
    );

    $stmt->execute();

    $stmt->close();

    $completed_message = "Lesson marked as completed!";
}

$stmt = $conn->prepare(
    "SELECT * FROM lessons WHERE id = ?"
);

$stmt->bind_param("i", $lesson_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    header("Location: lessons.php");

    exit();
}

$lesson = $result->fetch_assoc();

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        <?php echo htmlspecialchars($lesson["title"]); ?>
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<nav>

    <h2>Programming Learning Assistant</h2>

    <a href="dashboard.php">Dashboard</a>

    <a href="lessons.php">Lessons</a>

    <a href="logout.php">Logout</a>

</nav>

<main>

    <h1>
        <?php echo htmlspecialchars($lesson["title"]); ?>
    </h1>

    <p>

        <strong>Programming Language:</strong>

        <?php echo htmlspecialchars($lesson["language"]); ?>

    </p>

    <div class="lesson-content">

        <p>
            <?php
            echo nl2br(
                htmlspecialchars($lesson["content"])
            );
            ?>
        </p>

    </div>

    <br>

    <a href="lessons.php">
        ← Back to Lessons
    </a>

</main>

</body>

</html>