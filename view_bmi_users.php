<?php
session_start();
include 'db_connect.php';

// Initialize the $message variable to avoid undefined warnings
$message = "";

// Check if the user is logged in
if (!isset($_SESSION['AppUserID'])) {
    header("Location: login.php");
    exit();
}

// Handle form submissions for adding records and deleting users
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_record'])) {
        $height_ft = $_POST['height_ft'];
        $height_in = $_POST['height_in'];
        $weight = $_POST['weight'];
        $bmiUserID = $_POST['bmi_user_id'];

        // Convert height to inches first and then to meters for BMI calculation
        $total_height_in_inches = ($height_ft * 12) + $height_in;
        $height_in_meters = $total_height_in_inches * 0.0254;
        $bmi = $weight / ($height_in_meters ** 2);

        $stmt = $conn->prepare("INSERT INTO BMIRecords (BMIUserID, Height, Weight, BMI) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iddd", $bmiUserID, $total_height_in_inches, $weight, $bmi);
        $message = $stmt->execute() ? "BMI Record added successfully" : "Error: " . $stmt->error;
    } elseif (isset($_POST['delete_user'])) {
        $bmiUserID = $_POST['bmi_user_id'];

        // Delete user's records first, then the user
        $stmtDeleteRecords = $conn->prepare("DELETE FROM BMIRecords WHERE BMIUserID = ?");
        $stmtDeleteRecords->bind_param("i", $bmiUserID);
        $stmtDeleteRecords->execute();

        $stmtDeleteUser = $conn->prepare("DELETE FROM BMIUsers WHERE BMIUserID = ?");
        $stmtDeleteUser->bind_param("i", $bmiUserID);
        $message = $stmtDeleteUser->execute() ? "User deleted successfully" : "Error: " . $stmtDeleteUser->error;
    }
}

// Get all BMI Users from the database
$resultUsers = $conn->query("SELECT BMIUserID, Name, Age, Gender FROM BMIUsers");

if (!$resultUsers) {
    die("Error fetching BMI users: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-top: 15px;
        }
        h2 {
            font-weight: bold;
            color: #5A5A5A;
        }
        .btn-custom {
            background-color: #28a745;
            color: white;
            border-radius: 25px;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
        .btn-danger-custom {
            background-color: #dc3545;
            color: white;
            border-radius: 25px;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn-danger-custom:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">BMI Management</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- View BMI Users -->
    <h4>BMI Users</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>BMI User ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultUsers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['BMIUserID']; ?></td>
                <td><?php echo htmlspecialchars($row['Name']); ?></td>
                <td><?php echo $row['Age']; ?></td>
                <td><?php echo $row['Gender']; ?></td>
                <td>
                    <button class="btn btn-custom" data-toggle="modal" data-target="#addRecordModal" data-id="<?php echo $row['BMIUserID']; ?>">Add Record</button>
                    <a href="view_bmi_records.php?bmi_user_id=<?php echo $row['BMIUserID']; ?>" class="btn btn-custom">View Records</a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="bmi_user_id" value="<?php echo $row['BMIUserID']; ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger-custom">Delete User</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p><a href="index.php" class="btn btn-light">Back to Dashboard</a></p>
</div>

<!-- Modal for Adding BMI Record -->
<div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRecordModalLabel">Add BMI Record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="bmi_user_id" id="bmi_user_id">
                    <div class="form-group">
                        <label for="height_ft">Height (feet)</label>
                        <input type="number" name="height_ft" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="height_in">Height (inches)</label>
                        <input type="number" name="height_in" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" name="weight" class="form-control" required>
                    </div>
                    <button type="submit" name="add_record" class="btn btn-primary">Add Record</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $('#addRecordModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('id');
        var modal = $(this);
        modal.find('#bmi_user_id').val(userId);
    });
</script>

</body>
</html>
