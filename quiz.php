<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = (int) $_SESSION["user_id"];

$message = "";
$questions = [];
$answers = [];
$score = 0;
$total = 0;
$percentage = 0;
$submitted = false;

// Available programming categories
$categories = [
    "Java",
    "Python",
    "HTML",
    "CSS",
    "JavaScript"
];

// Available difficulty levels
$difficulties = [
    "Beginner",
    "Intermediate",
    "Advanced"
];

// Get selected category and difficulty
$category = $_POST["category"] ?? $_GET["category"] ?? "Java";
$difficulty = $_POST["difficulty"] ?? $_GET["difficulty"] ?? "Beginner";

// Validate category and difficulty
if (!in_array($category, $categories, true)) {
    $category = "Java";
}

if (!in_array($difficulty, $difficulties, true)) {
    $difficulty = "Beginner";
}

// Process quiz submission
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["submit_quiz"])) {

    $submitted = true;

    // Retrieve the questions for this category and difficulty.
    // Correct answers are fetched only for server-side marking.
    $stmt = $conn->prepare(
        "SELECT id, question, option_a, option_b,
                option_c, option_d, correct_answer, explanation
         FROM quiz_questions
         WHERE topic = ? AND difficulty = ?
         ORDER BY id ASC
         LIMIT 10"
    );

    $stmt->bind_param("ss", $category, $difficulty);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }

    $stmt->close();

    if (count($questions) === 0) {

        $message = "No questions are available for this category and difficulty.";

    } else {

        $total = count($questions);

        foreach ($questions as $question) {

            $question_id = (int) $question["id"];

            $user_answer = "";

            if (
                isset($_POST["answers"][$question_id]) &&
                is_string($_POST["answers"][$question_id])
            ) {
                $user_answer = strtoupper(
                    $_POST["answers"][$question_id]
                );
            }

            $correct_answer = strtoupper(
                $question["correct_answer"]
            );

            $answers[$question_id] = $user_answer;

            if (
                in_array($user_answer, ["A", "B", "C", "D"], true) &&
                $user_answer === $correct_answer
            ) {
                $score++;
            }
        }

        $percentage = ($score / $total) * 100;

        // Save the quiz result
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
            $message = "Your quiz result has been saved successfully.";
        } else {
            $message = "Your score could not be saved.";
        }

        $stmt->close();
    }

} else {

    // Load a random selection of questions for the quiz.
    $stmt = $conn->prepare(
        "SELECT id, question, option_a, option_b,
                option_c, option_d
         FROM quiz_questions
         WHERE topic = ? AND difficulty = ?
         ORDER BY RAND()
         LIMIT 10"
    );

    $stmt->bind_param("ss", $category, $difficulty);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }

    $stmt->close();
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

    <style>
        .quiz-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
        }

        .quiz-settings,
        .quiz-question,
        .quiz-result {
            background: #ffffff;
            color: #222222;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .quiz-settings select {
            padding: 10px;
            margin: 8px;
            min-width: 150px;
        }

        .quiz-question label {
            display: block;
            margin: 12px 0;
            cursor: pointer;
        }

        .quiz-question input {
            margin-right: 8px;
        }

        .quiz-question.correct {
            border-left: 5px solid #198754;
        }

        .quiz-question.incorrect {
            border-left: 5px solid #dc3545;
        }

        .correct-answer {
            color: #198754;
            font-weight: bold;
        }

        .wrong-answer {
            color: #dc3545;
            font-weight: bold;
        }

        .quiz-button {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin: 8px 5px 8px 0;
        }

        .quiz-feedback {
            background: #f3f5f7;
            padding: 12px;
            border-radius: 6px;
            margin-top: 12px;
        }

        .quiz-links a {
            display: inline-block;
            margin: 10px 15px 10px 0;
        }
    </style>

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
    <a href="logout.php">Logout</a>

</nav>

<main class="quiz-container">

    <h1>Programming Quiz</h1>

    <p>
        Select a programming language and difficulty level
        to test your knowledge.
    </p>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <?php if ($submitted && count($questions) > 0): ?>

        <section class="quiz-result">

            <h2>Your Quiz Results</h2>

            <p>
                Category:
                <?= htmlspecialchars($category) ?>
            </p>

            <p>
                Difficulty:
                <?= htmlspecialchars($difficulty) ?>
            </p>

            <h3>
                Score:
                <?= $score ?> / <?= $total ?>
            </h3>

            <h3>
                Percentage:
                <?= number_format($percentage, 2) ?>%
            </h3>

            <?php if ($percentage >= 80): ?>

                <p>Excellent work! You understand this topic well.</p>

            <?php elseif ($percentage >= 50): ?>

                <p>Good effort! Review the explanations to improve.</p>

            <?php else: ?>

                <p>Keep practising. Review the explanations and try again.</p>

            <?php endif; ?>

        </section>


        <h2>Review Your Answers</h2>

        <?php foreach ($questions as $index => $question): ?>

            <?php

            $question_id = (int) $question["id"];

            $user_answer = $answers[$question_id] ?? "";

            $correct_answer = strtoupper(
                $question["correct_answer"]
            );

            $options = [
                "A" => $question["option_a"],
                "B" => $question["option_b"],
                "C" => $question["option_c"],
                "D" => $question["option_d"]
            ];

            $is_correct = ($user_answer === $correct_answer);

            ?>

            <div class="quiz-question <?= $is_correct ? 'correct' : 'incorrect' ?>">

                <h3>Question <?= $index + 1 ?></h3>

                <p>
                    <?= htmlspecialchars($question["question"]) ?>
                </p>

                <?php foreach ($options as $letter => $option): ?>

                    <p>

                        <?= $letter ?>.
                        <?= htmlspecialchars($option) ?>

                        <?php if ($letter === $correct_answer): ?>

                            <span class="correct-answer">
                                — Correct answer
                            </span>

                        <?php endif; ?>

                        <?php if (
                            $letter === $user_answer &&
                            !$is_correct
                        ): ?>

                            <span class="wrong-answer">
                                — Your answer
                            </span>

                        <?php endif; ?>

                    </p>

                <?php endforeach; ?>

                <div class="quiz-feedback">

                    <strong>Explanation:</strong>

                    <?= htmlspecialchars(
                        $question["explanation"]
                        ?? "No explanation has been added for this question yet."
                    ) ?>

                </div>

            </div>

        <?php endforeach; ?>


        <div class="quiz-links">

            <a href="quiz.php?category=<?= urlencode($category) ?>&difficulty=<?= urlencode($difficulty) ?>">
                Try Again
            </a>

            <a href="progress.php">
                View My Progress
            </a>

            <a href="dashboard.php">
                Back to Dashboard
            </a>

        </div>


    <?php else: ?>

        <form method="POST" action="quiz.php">

            <section class="quiz-settings">

                <h2>Quiz Settings</h2>

                <label for="category">
                    Programming Language:
                </label>

                <select name="category" id="category" required>

                    <?php foreach ($categories as $item): ?>

                        <option
                            value="<?= htmlspecialchars($item) ?>"
                            <?= $category === $item ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($item) ?>
                        </option>

                    <?php endforeach; ?>

                </select>


                <br><br>

                <label for="difficulty">
                    Difficulty:
                </label>

                <select
                    name="difficulty"
                    id="difficulty"
                    required
                >

                    <?php foreach ($difficulties as $item): ?>

                        <option
                            value="<?= htmlspecialchars($item) ?>"
                            <?= $difficulty === $item ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($item) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <br><br>

                <button
                    type="submit"
                    formaction="quiz.php"
                    formmethod="GET"
                    class="quiz-button"
                >
                    Load Selected Quiz
                </button>

            </section>

        </form>


        <?php if (count($questions) > 0): ?>

            <h2>
                <?= htmlspecialchars($category) ?>
                — <?= htmlspecialchars($difficulty) ?>
            </h2>

            <p>
                Questions available:
                <?= count($questions) ?>
            </p>

            <form method="POST" action="quiz.php">

                <input
                    type="hidden"
                    name="category"
                    value="<?= htmlspecialchars($category) ?>"
                >

                <input
                    type="hidden"
                    name="difficulty"
                    value="<?= htmlspecialchars($difficulty) ?>"
                >

                <?php foreach ($questions as $index => $question): ?>

                    <?php

                    $question_id = (int) $question["id"];

                    $options = [
                        "A" => $question["option_a"],
                        "B" => $question["option_b"],
                        "C" => $question["option_c"],
                        "D" => $question["option_d"]
                    ];

                    ?>

                    <div class="quiz-question">

                        <h3>Question <?= $index + 1 ?></h3>

                        <p>
                            <?= htmlspecialchars($question["question"]) ?>
                        </p>

                        <?php foreach ($options as $letter => $option): ?>

                            <label>

                                <input
                                    type="radio"
                                    name="answers[<?= $question_id ?>]"
                                    value="<?= $letter ?>"
                                    required
                                >

                                <?= $letter ?>.
                                <?= htmlspecialchars($option) ?>

                            </label>

                        <?php endforeach; ?>

                    </div>

                <?php endforeach; ?>

                <button
                    type="submit"
                    name="submit_quiz"
                    value="1"
                    class="quiz-button"
                >
                    Submit Quiz
                </button>

            </form>

        <?php else: ?>

            <section class="quiz-question">

                <p>
                    No questions are available for this category
                    and difficulty yet.
                </p>

                <p>
                    Add questions to the database or choose another
                    category and difficulty.
                </p>

            </section>

        <?php endif; ?>

    <?php endif; ?>

</main>

</body>

</html>

