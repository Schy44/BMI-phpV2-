<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['AppUserID']) || !isset($_GET['bmi_user_id'])) {
    header("Location: login.php");
    exit();
}

$bmiUserID = $_GET['bmi_user_id'];
$stmt = $conn->prepare("SELECT RecordID, Height, Weight, BMI, RecordedAt FROM BMIRecords WHERE BMIUserID = ?");
$stmt->bind_param("i", $bmiUserID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user's name for display
$userStmt = $conn->prepare("SELECT Name FROM BMIUsers WHERE BMIUserID = ?");
$userStmt->bind_param("i", $bmiUserID);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userRow = $userResult->fetch_assoc();
$username = htmlspecialchars($userRow['Name']);

// Calculate health tips based on the last record
$healthTips = "";
if ($row = $result->fetch_assoc()) {
    $bmi = $row['BMI'];
    if ($bmi < 18.5) {
        $healthTips = "Your BMI indicates that you are underweight. Consider consulting a healthcare provider for dietary recommendations.";
    } elseif ($bmi >= 18.5 && $bmi < 24.9) {
        $healthTips = "Great job! Your BMI is within a healthy range. Maintain your healthy lifestyle.";
    } elseif ($bmi >= 25 && $bmi < 29.9) {
        $healthTips = "You are in the overweight category. Incorporate regular physical activity and a balanced diet.";
    } else {
        $healthTips = "Your BMI indicates obesity. It's advisable to seek guidance from a healthcare provider for personalized advice on weight management.";
    }

    // Move the pointer back to the first record
    $result->data_seek(0);
} else {
    $healthTips = "No BMI records found for this user.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View BMI Records for <?php echo $username; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">BMI Records for <?php echo $username; ?></h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Record ID</th>
                    <th>Height (ft & in)</th>
                    <th>Weight (kg)</th>
                    <th>BMI</th>
                    <th>Recorded At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['RecordID']; ?></td>
                    <td><?php echo floor($row['Height'] / 12) . " ft " . ($row['Height'] % 12) . " in"; ?></td>
                    <td><?php echo $row['Weight']; ?></td>
                    <td><?php echo number_format($row['BMI'], 2); ?></td>
                    <td><?php echo $row['RecordedAt']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h5>Health Tips:</h5>
        <p><?php echo $healthTips; ?></p>
        <p><a href="view_bmi_users.php" class="btn btn-info"><i class="fas fa-arrow-left"></i> Back to BMI Users</a></p>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
