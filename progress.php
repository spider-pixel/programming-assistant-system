<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

$user_id = (int) $_SESSION['user_id'];

// Retrieve the logged-in user's quiz statistics
$sql = "SELECT
            COUNT(*) AS total_attempts,
            MAX(percentage) AS best_percentage,
            AVG(percentage) AS average_percentage
        FROM quiz_results
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$stats = $result->fetch_assoc();

$stmt->close();


// Retrieve the user's quiz history
$sql = "SELECT
            score,
            total_questions,
            percentage,
            date_taken
        FROM quiz_results
        WHERE user_id = ?
        ORDER BY date_taken DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$history = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Learning Progress</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <h1>My Learning Progress</h1>

    <p>
        Track your programming quiz performance.
    </p>


    <section class="progress-summary">

        <div class="progress-card">

            <h3>Total Quiz Attempts</h3>

            <p>
                <?= (int) $stats['total_attempts'] ?>
            </p>

        </div>


        <div class="progress-card">

            <h3>Best Score</h3>

            <p>
                <?php
                echo $stats['best_percentage'] !== null
                    ? number_format(
                        (float) $stats['best_percentage'],
                        2
                    ) . '%'
                    : 'No score yet';
                ?>
            </p>

        </div>


        <div class="progress-card">

            <h3>Average Score</h3>

            <p>
                <?php
                echo $stats['average_percentage'] !== null
                    ? number_format(
                        (float) $stats['average_percentage'],
                        2
                    ) . '%'
                    : 'No score yet';
                ?>
            </p>

        </div>

    </section>


    <h2>Quiz History</h2>

    <?php if ($history->num_rows > 0): ?>

        <table border="1" cellpadding="10">

            <thead>

                <tr>
                    <th>Date</th>
                    <th>Score</th>
                    <th>Percentage</th>
                </tr>

            </thead>

            <tbody>

                <?php while ($row = $history->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($row['date_taken']) ?>
                        </td>

                        <td>
                            <?= (int) $row['score'] ?>
                            /
                            <?= (int) $row['total_questions'] ?>
                        </td>

                        <td>
                            <?= number_format(
                                (float) $row['percentage'],
                                2
                            ) ?>%
                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>
            You have not completed any quizzes yet.
            Complete a quiz to start tracking your progress.
        </p>

    <?php endif; ?>


    <br>

    <a href="quiz.php">Take a Quiz</a>

    <br><br>

    <a href="dashboard.php">Back to Dashboard</a>

</body>

</html>

<?php

$stmt->close();

?>