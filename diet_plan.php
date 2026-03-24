<?php
require_once 'db_config.php';
checkLogin();

$uid = $_SESSION['user_id'];

// 1. DATE SELECTOR
$selected_date = isset($_GET['view_date']) ? $_GET['view_date'] : date('Y-m-d');

// 2. FETCH USER DATA
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id=$uid");
$u = mysqli_fetch_assoc($user_query);

// Fallback values if DB is empty to prevent crashes
$u_weight = $u['weight'] ?? 70;
$u_height = $u['height'] ?? 170;
$u_age = $u['age'] ?? 25;
$goal = strtolower($u['goal'] ?? 'cut');

// 3. MAINTENANCE CALCULATION
$maint = (10 * $u_weight) + (6.25 * $u_height) - (5 * $u_age) + 5;
$target = ($goal == 'bulk') ? round($maint + 400) : round($maint - 500);

// 4. HANDLE ADDING FOOD TO LOG
if (isset($_POST['calculate'])) {
    $grams = (float) $_POST['weight_grams'];
    $food = mysqli_real_escape_string($conn, $_POST['food']);
    $log_date = $_POST['log_date'];

    // Update the query to use your specific column names
    $res = mysqli_query($conn, "SELECT * FROM food_data WHERE item_name='$food'");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $ratio = $grams / 100;

        // Use the exact field names from your food_data table
        $cal = round($row['cal_per_100g'] * $ratio);
        $pro = round($row['pro_per_100g'] * $ratio, 1);
        $carb = round($row['carb_per_100g'] * $ratio, 1);

        // Save to your diet_logs table
        mysqli_query($conn, "INSERT INTO diet_logs (user_id, food_name, calories, protein, carbs, entry_date) 
                            VALUES ($uid, '$food', $cal, $pro, $carb, '$log_date')");

        header("Location: diet_plan.php?view_date=" . $log_date);
        exit();
    }
}

// 5. FETCH DAILY LOGS
$logs_res = mysqli_query($conn, "SELECT * FROM diet_logs WHERE user_id=$uid AND entry_date='$selected_date'");
$daily_total = 0;
?>

<!DOCTYPE html>
<html data-theme="dark">

<head>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Diet Plan | FitLife</title>
</head>

<body>
    <div class="navbar">
        <a href="dashboard.php" class="logo">FitLife<span>Pro</span></a>

        <div class="nav-links">
            <?php
            // Detects the current file name to hide its link
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>

            <?php if ($current_page != 'dashboard.php'): ?>
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i> Home
                </a>
            <?php endif; ?>

            <?php if ($current_page != 'diet_plan.php'): ?>
                <a href="diet_plan.php" class="nav-item">
                    <i class="fas fa-utensils"></i> Diet Plan
                </a>
            <?php endif; ?>

            <?php if ($current_page != 'workout_plans.php'): ?>
                <a href="workout_plans.php" class="nav-item">
                    <i class="fas fa-dumbbell"></i> Workout
                </a>
            <?php endif; ?>

            <?php if ($current_page != 'profile.php'): ?>
                <a href="profile.php" class="nav-item">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="card"
            style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #800000;">
            <h2 style="margin:0;">Diet Log</h2>
            <input type="date" value="<?php echo $selected_date; ?>"
                onchange="window.location.href='diet_plan.php?view_date='+this.value"
                style="background:#1a1a2e; color:white; border:1px solid #800000; padding:8px; border-radius:5px;">
        </div>

        <div class="grid">
            <div class="card">
                <h3 style="color:#800000;">Personalized Plan</h3>
                <p>Goal: <strong><?php echo strtoupper($goal); ?></strong></p>
                <p>Target: <strong><?php echo $target; ?> kcal</strong></p>
                <div style="background: rgba(128,0,0,0.1); padding: 10px; border-radius: 5px; margin-top: 10px;">
                    <strong>Recommended:</strong><br>
                    <?php if ($goal == 'bulk'): ?>
                        High protein meat, Rice, and Whole Eggs.
                    <?php else: ?>
                        Grilled Fish, Leafy Greens, and Boiled Eggs.
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h3 style="color:#800000;">Log a Meal</h3>
                <form method="POST">
                    <input type="hidden" name="log_date" value="<?php echo $selected_date; ?>">
                    <label>Select Food</label>
                    <select name="food" style="width:100%; margin-bottom:10px;">
                        <?php
                        $foods = mysqli_query($conn, "SELECT item_name FROM food_data ORDER BY item_name ASC");
                        while ($f = mysqli_fetch_assoc($foods)) {
                            echo "<option value='" . $f['item_name'] . "'>" . $f['item_name'] . "</option>";
                        }
                        ?>
                        <option value="Chicken Breast">Chicken Breast</option>
                        <option value="Rice">Rice</option>
                        <option value="Egg">Egg</option>
                        <option value="Oats">Oats</option>
                        <option value="Paneer">Paneer</option>

                        <option value="Soya Chunks">Soya Chunks (Highest Protein)</option>
                        <option value="Greek Yogurt">Greek Yogurt</option>
                        <option value="Peanut Butter">Peanut Butter</option>
                        <option value="Whey Protein">Whey Protein Scoop</option>
                        <option value="Chickpeas (Chana)">Chickpeas (Chana)</option>
                        <option value="Tuna Fish">Tuna Fish</option>
                        <option value="Sweet Potato">Sweet Potato (Good Carbs)</option>
                    </select>
                    <label>Weight (Grams)</label>
                    <input type="number" name="weight_grams" placeholder="e.g. 200" required
                        style="width:100%; margin-bottom:10px;">
                    <button type="submit" name="calculate" class="btn" style="width:100%;">Add to Log</button>
                </form>
            </div>
        </div>
        <style>
            /* Targets the text inside the closed select box */
            select {
                color: #ffffff !important;
                padding: 8px;
                border: 1px solid #800000;
                cursor: pointer;
            }

            /* Targets the text inside the dropdown list when clicked */
            select option {
                color: #ffffff !important;
                background-color: #1a1a2e;
                /* Matches your existing card/input background */
            }

            /* Ensures the text is visible even when the element is focused */
            select:focus {
                color: #ffffff !important;
                outline: none;
            }
        </style>

        <div class="card" style="margin-top: 20px;">
            <h3>Daily Summary for <?php echo date('M d', strtotime($selected_date)); ?></h3>
            <table style="width:100%; border-collapse: collapse; margin-top:10px;">
                <tr style="text-align: left; border-bottom: 2px solid #800000;">
                    <th style="padding:10px;">Food</th>
                    <th>Calories</th>
                    <th>Protein</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($logs_res)):
                    $daily_total += $row['calories']; ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td style="padding:10px;"><?php echo $row['food_name']; ?></td>
                        <td><?php echo $row['calories']; ?> kcal</td>
                        <td><?php echo $row['protein']; ?>g</td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <div style="text-align: right; margin-top: 15px;">
                <h3 style="color:#800000;">Total: <?php echo $daily_total; ?> / <?php echo $target; ?> kcal</h3>
            </div>
        </div>
    </div>
</body>

</html>