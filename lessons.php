<?php

require_once "includes/auth.php";
require_once "config/database.php";

$sql = "SELECT * FROM lessons ORDER BY id ASC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Programming Lessons</title>

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

    <h1>Programming Lessons</h1>

    <p>
        Select a lesson below to begin learning.
    </p>

    <div class="cards">

        <?php if ($result->num_rows > 0): ?>

            <?php while ($lesson = $result->fetch_assoc()): ?>

                <div class="card">

                    <h2>
                        <?php echo htmlspecialchars($lesson["title"]); ?>
                    </h2>

                    <p>
                        <strong>Language:</strong>
                        <?php echo htmlspecialchars($lesson["language"]); ?>
                    </p>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $lesson["description"]
                        );
                        ?>
                    </p>

                    <a
                        href="lesson.php?id=<?php echo $lesson["id"]; ?>"
                    >
                        Open Lesson
                    </a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No lessons available.</p>

        <?php endif; ?>

    </div>

</main>

</body>

</html>