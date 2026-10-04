<?php
session_start();

// If the user is already logged in, go directly to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Assistant System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            width: 90%;
            max-width: 900px;
            padding: 60px 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }

        h1 {
            color: #1e3c72;
            margin-bottom: 20px;
            font-size: 38px;
        }

        p {
            color: #555;
            font-size: 18px;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .login {
            background: #1e3c72;
            color: white;
        }

        .register {
            background: #2a5298;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 45px;
            flex-wrap: wrap;
        }

        .feature {
            width: 200px;
        }

        .feature h3 {
            color: #1e3c72;
            margin-bottom: 8px;
        }

        .feature p {
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Programming Assistant System</h1>

    <p>
        Welcome to the Programming Assistant System.
        Learn programming, access lessons and improve your
        programming skills through an interactive learning environment.
    </p>

    <div class="buttons">
        <a href="login.php" class="btn login">Log In</a>
        <a href="register.php" class="btn register">Create Account</a>
    </div>

    <div class="features">

        <div class="feature">
            <h3>Learn</h3>
            <p>Access programming lessons and learning materials.</p>
        </div>

        <div class="feature">
            <h3>Practice</h3>
            <p>Improve your programming knowledge through practice.</p>
        </div>

        <div class="feature">
            <h3>Track</h3>
            <p>Keep track of your learning progress.</p>
        </div>

    </div>

</div>

</body>
</html>