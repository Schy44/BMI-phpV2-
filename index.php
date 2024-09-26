<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['AppUserID'])) {
    header("Location: login.php");
    exit();
}

// Define default values to prevent undefined variable warnings
$username = htmlspecialchars($_SESSION['Username']);
$greeting = '';  // Initialize the $greeting variable
$bmi = null;     // Initialize the $bmi variable
$message = '';
$imgSrc = '';
$calorieIntake = 0;
$carbs = $protein = $fat = $fiber = $sugar = 0; // Initialize all macronutrients

// Set greeting based on the time of day
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning, $username!";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon, $username!";
} else {
    $greeting = "Good Evening, $username!";
}

// Health tips array
$healthTips = [
    "Stay hydrated and eat balanced meals!",
    "Regular exercise helps maintain a healthy BMI.",
    "Consistency is key—track your progress!",
    "Sleep well for better overall health.",
    "Small steps today lead to big changes tomorrow.",
];
$randomTip = $healthTips[array_rand($healthTips)];

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $weight = isset($_POST['weight']) ? (float)$_POST['weight'] : '';
    $height = isset($_POST['height']) ? (float)$_POST['height'] : '';
    $activityLevel = isset($_POST['activity_level']) ? (float)$_POST['activity_level'] : 1;

    if ($weight <= 0 || $height <= 0) {
        $message = "Please enter a valid weight and height";
    } else {
        $totalInches = $height;
        $heightInMeters = $totalInches * 0.0254;

        // Calculate BMI
        $bmi = $weight / ($heightInMeters ** 2);
        $bmi = round($bmi, 1);

        // Set message and image based on BMI category
        if ($bmi < 18.5) {
            $message = 'You are underweight';
            $imgSrc = 'images/underweight.png';
        } elseif ($bmi < 25) {
            $message = 'You are healthy';
            $imgSrc = 'images/healthy.png';
        } elseif ($bmi < 30) {
            $message = 'You are overweight';
            $imgSrc = 'images/overweight.png';
        } else {
            $message = 'You are obese';
            $imgSrc = 'images/obese.png';
        }

        // Calculate BMR and Calorie Intake
        $age = 25; // Default age, modify if needed
        $bmr = (10 * $weight) + (6.25 * ($height * 2.54)) - (5 * $age);
        $calorieIntake = $bmr * $activityLevel;

        // Calculate macronutrient distribution
        $carbs = $calorieIntake * 0.50 / 4; // 50% carbs
        $protein = $calorieIntake * 0.20 / 4; // 20% protein
        $fat = $calorieIntake * 0.25 / 9; // 25% fat
        $fiber = $calorieIntake * 0.05 / 4; // 5% fiber
        $sugar = $calorieIntake * 0.10 / 4; // 10% sugar
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #f8fafc, #e2e8f0);
        }
        .navbar {
            background: linear-gradient(90deg, #4b79a1, #283e51);
        }

        .navbar .btn-view-bmi-users {
            background: linear-gradient(45deg, #ffdd57, #ff9f43);
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            border: none;
            font-size: 16px;
            transition: background 0.4s ease-in-out, transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            text-transform: uppercase;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.3);
        }

        .navbar .btn-view-bmi-users:hover {
            background: linear-gradient(45deg, #ffc107, #ff6f00);
            color: #fff;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.5);
            text-decoration: none;
        }

        .dashboard-card {
            border: none;
            border-radius: 15px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #56ab2f, #a8e063);
            border: none;
        }

        .result-section {
            background-color: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 0px;
        }

        .macro-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .macro-line {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 10px;
        }

        /* Custom styling for side-by-side layout */
        .row {
            display: flex;
            justify-content: space-between;
        }

        .col-lg-6 {
            flex: 1;
            padding: 15px;
        }
        .macro-line-container {
    position: relative;
    width: 100%;
    height: 30px;
    background-color: #e9ecef;
    border-radius: 15px;
    overflow: hidden;
}

.macro-bar {
    display: flex;
    height: 100%;
    border-radius: 15px;
    overflow: hidden;
}

.carbs-bar {
    background-color: #FFB347; /* Orange color for carbs */
    transition: width 1s ease;
}

.protein-bar {
    background-color: #85C1E9; /* Light blue for protein */
    transition: width 1s ease;
}

.fat-bar {
    background-color: #F1948A; /* Light red for fat */
    transition: width 1s ease;
}

.fiber-bar {
    background-color: #7DCEA0; /* Green for fiber */
    transition: width 1s ease;
}

.sugar-bar {
    background-color: #F7DC6F; /* Yellow for sugar */
    transition: width 1s ease;
}

.macro-legend {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}

.macro-item {
    display: flex;
    align-items: center;
}

.macro-item span {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 10px;
}

.carbs-color {
    background-color: #FFB347;
}

.protein-color {
    background-color: #85C1E9;
}

.fat-color {
    background-color: #F1948A;
}

.fiber-color {
    background-color: #7DCEA0;
}

.sugar-color {
    background-color: #F7DC6F;
}


        @media(max-width: 767px) {
            .col-lg-6 {
                flex: 100%;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link btn-view-bmi-users mr-3" href="view_bmi_users.php">View BMI Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container my-5">
    <h2 class="text-center display-4"><?php echo $greeting; ?></h2>

    <div class="row">
        <!-- BMI Calculator Section -->
        <div class="col-lg-6 mb-4">
            <div class="card p-4 shadow">
                <h3 class="text-center mb-4">BMI Calculator</h3>
                <form method="POST" action="" class="form-group">
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" class="form-control" value="<?php echo htmlspecialchars($weight ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="height">Height</label>
                        <div class="slider-container">
                            <h5 id="height-display">5'7"</h5>
                            <input type="range" min="48" max="84" value="67" class="slider" id="height-slider" name="height" step="1">
                            <small class="form-text text-muted">Use the slider to adjust your height (in feet and inches).</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="activity_level">Activity Level</label>
                        <select id="activity_level" name="activity_level" class="form-control">
                            <option value="1.2">Sedentary (little or no exercise)</option>
                            <option value="1.375">Lightly active (light exercise/sports)</option>
                            <option value="1.55">Moderately active (moderate exercise/sports)</option>
                            <option value="1.725">Very active (hard exercise/sports)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Calculate BMI</button>
                </form>
            </div>
        </div>

     <!-- BMI Results Section -->
     <div class="col-lg-6">
    <div class="result-section">
        <?php if ($bmi): ?>
            <!-- Display results if BMI is calculated -->
            <h4 class="text-center">Your BMI is: <?php echo $bmi; ?></h4>
            <p class="text-center"><?php echo $message; ?></p>
            <img src="<?php echo $imgSrc; ?>" alt="BMI result" class="img-fluid d-block mx-auto mb-4" style="max-height: 150px;">

            <h5 class="text-center">Daily Calorie Intake: <?php echo round($calorieIntake); ?> calories</h5>

          <!-- Updated Macronutrient Bar -->
<div class="macro-line-container mb-4">
    <div class="macro-bar">
        <!-- Percentage for each macro is based on its caloric contribution -->
        <div class="carbs-bar" style="width: <?php echo ($carbs * 4 / $calorieIntake) * 100; ?>%;"></div>
        <div class="protein-bar" style="width: <?php echo ($protein * 4 / $calorieIntake) * 100; ?>%;"></div>
        <div class="fat-bar" style="width: <?php echo ($fat * 9 / $calorieIntake) * 100; ?>%;"></div>
        <div class="fiber-bar" style="width: <?php echo ($fiber * 4 / $calorieIntake) * 100; ?>%;"></div>
        <div class="sugar-bar" style="width: <?php echo ($sugar * 4 / $calorieIntake) * 100; ?>%;"></div>
    </div>
</div>


            <!-- Nutrient names and amounts -->
            <div class="macro-legend">
                <div class="macro-item"><span class="carbs-color"></span> Carbs: <?php echo round($carbs); ?>g</div>
                <div class="macro-item"><span class="protein-color"></span> Protein: <?php echo round($protein); ?>g</div>
                <div class="macro-item"><span class="fat-color"></span> Fat: <?php echo round($fat); ?>g</div>
                <div class="macro-item"><span class="fiber-color"></span> Fiber: <?php echo round($fiber); ?>g</div>
                <div class="macro-item"><span class="sugar-color"></span> Sugar: <?php echo round($sugar); ?>g</div>
            </div>
        <?php else: ?>
            <!-- Display this placeholder if BMI is not yet calculated -->
            <h4 class="text-center">Calculate BMI to see the result</h4>
        <?php endif; ?>
    </div>
</div>




<script>
    
    const heightSlider = document.getElementById("height-slider");
    const heightDisplay = document.getElementById("height-display");

    heightSlider.oninput = function() {
        let feet = Math.floor(this.value / 12);
        let inches = this.value % 12;
        heightDisplay.innerHTML = feet + "'" + inches + "\"";
    };
</script>
</body>
</html>
