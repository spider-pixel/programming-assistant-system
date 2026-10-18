<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

$user_id = (int) $_SESSION['user_id'];

$message = "";
$score = 0;
$total = 0;
$percentage = 0;
$submitted = false;

// Retrieve quiz questions
$sql = "SELECT id, question, option_a, option_b,
        option_c, option_d, correct_answer
        FROM quiz_questions
        ORDER BY id ASC
        LIMIT 10";

$result = $conn->query($sql);

$questions = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}

// Process submitted answers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submitted = true;

    if (empty($questions)) {

        $message = "No quiz questions are available.";

    } else {

        $total = count($questions);

        foreach ($questions as $question) {

            $question_id = $question['id'];

            $correct_answer = strtoupper(
                $question['correct_answer']
            );

            $user_answer = '';

            if (
                isset($_POST['answers'][$question_id]) &&
                is_string($_POST['answers'][$question_id])
            ) {
                $user_answer = strtoupper(
                    $_POST['answers'][$question_id]
                );
            }

            if (
                in_array($user_answer, ['A', 'B', 'C', 'D'], true) &&
                $user_answer === $correct_answer
            ) {
                $score++;
            }
        }

        $percentage = ($score / $total) * 100;

        // Save the result for the logged-in user
        $stmt = $conn->prepare(
            "INSERT INTO quiz_results
            (user_id, score, total_questions, percentage)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iiid",
            $user_id,
            $score,
            $total,
            $percentage
        );

        if ($stmt->execute()) {

            $message = "Your quiz has been submitted successfully.";

        } else {

            $message = "Your score could not be saved.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Programming Quiz</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <h1>Programming Quiz</h1>

    <p>Test your programming knowledge.</p>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <?php if ($submitted): ?>

        <h2>Your Results</h2>

        <p>
            Score:
            <?= $score ?> / <?= $total ?>
        </p>

        <p>
            Percentage:
            <?= number_format($percentage, 2) ?>%
        </p>

        <p>
            <?php
            if ($percentage >= 80) {
                echo "Excellent work!";
            } elseif ($percentage >= 50) {
                echo "Good effort! Keep practising.";
            } else {
                echo "Keep learning and try again.";
            }
            ?>
        </p>

        <a href="quiz.php">Try Again</a>

        <br><br>

        <a href="progress.php">View My Progress</a>


    <?php elseif (count($questions) > 0): ?>

        <form method="POST" action="quiz.php">

            <?php foreach ($questions as $index => $question): ?>

                <div class="quiz-question">

                    <h3>
                        Question <?= $index + 1 ?>
                    </h3>

                    <p>
                        <?= htmlspecialchars($question['question']) ?>
                    </p>

                    <?php
                    $options = [
                        'A' => $question['option_a'],
                        'B' => $question['option_b'],
                        'C' => $question['option_c'],
                        'D' => $question['option_d']
                    ];
                    ?>

                    <?php foreach ($options as $letter => $option): ?>

                        <label>

                            <input
                                type="radio"
                                name="answers[<?= (int) $question['id'] ?>]"
                                value="<?= $letter ?>"
                                required
                            >

                            <?= $letter ?>.
                            <?= htmlspecialchars($option) ?>

                        </label>

                        <br>

                    <?php endforeach; ?>

                </div>

                <br>

            <?php endforeach; ?>

            <button type="submit">
                Submit Quiz
            </button>

        </form>

    <?php else: ?>

        <p>
            No questions have been added to the database yet.
        </p>

    <?php endif; ?>


    <br>

    <a href="dashboard.php">Back to Dashboard</a>

</body>

</html>