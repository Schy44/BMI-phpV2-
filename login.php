<?php
session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT AppUserID, Password FROM AppUsers WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($appUserID, $hashedPassword);
    $stmt->fetch();

    if ($hashedPassword && password_verify($password, $hashedPassword)) {
        $_SESSION['AppUserID'] = $appUserID;
        $_SESSION['Username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $message = "Invalid username or password";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #56ab2f, #a8e063); /* Gradient colors to enhance BMI/health vibes */
            font-family: 'Arial', sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.2); /* Translucent white with a blurred effect */
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
            position: relative;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: url('path_to_health_icon_or_illustration.png') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            z-index: -1;
        }

        .login-title {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .form-control {
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.3);
            color: #fff;
            margin-bottom: 10px;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.4);
            border: none;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .btn-primary {
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background-color: #388E3C;
        }

        .alert {
            background-color: #e57373;
            color: #fff;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .text-center a {
            color: #ffd54f;
        }

        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="login-title">Login</h2>
        <?php if ($message): ?>
            <div class="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
