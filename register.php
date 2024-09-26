<?php

session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists
    $checkUserStmt = $conn->prepare("SELECT Username FROM AppUsers WHERE Username = ?");
    $checkUserStmt->bind_param("s", $username);
    $checkUserStmt->execute();
    $checkUserStmt->store_result();

    if ($checkUserStmt->num_rows > 0) {
        $message = "Username already exists";
    } else {
        // Insert into AppUsers table
        $stmt = $conn->prepare("INSERT INTO AppUsers (Username, Password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            // Get the last inserted AppUserID
            $appUserID = $stmt->insert_id;

            // Insert into BMIUsers table
            $stmtBMI = $conn->prepare("INSERT INTO BMIUsers (Name, Age, Gender) VALUES (?, ?, ?)");
            $stmtBMI->bind_param("sis", $username, $age, $gender);
            if ($stmtBMI->execute()) {
                $message = "Account and BMI User created successfully";
            } else {
                $message = "Error creating BMI User: " . $stmtBMI->error;
            }

            $stmtBMI->close();
        } else {
            $message = "Error creating account: " . $stmt->error;
        }
        $stmt->close();
    }

    $checkUserStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #56ab2f, #a8e063); 
            font-family: 'Arial', sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            margin: 0;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
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
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Register</h2>

        <?php if ($message): ?>
            <div class="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <input type="number" name="age" class="form-control" placeholder="Age" required>
            <select name="gender" class="form-control" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>
