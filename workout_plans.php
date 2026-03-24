<?php
require_once 'db_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['user_id'];

// 1. SAVE WORKOUT LOG
if (isset($_POST['log_workout'])) {
    $ex = mysqli_real_escape_string($conn, $_POST['exercise']);
    $sets = (int) $_POST['sets'];
    $reps = (int) $_POST['reps'];
    $weight = (float) $_POST['weight'];

    $sql = "INSERT INTO workout_logs (user_id, exercise_name, sets, reps, weight_kg) 
            VALUES ($uid, '$ex', $sets, $reps, $weight)";
    mysqli_query($conn, $sql);
    header("Location: workout_plans.php");
    exit();
}

// 2. FETCH DATA
$logs_res = mysqli_query($conn, "SELECT * FROM workout_logs WHERE user_id=$uid ORDER BY date_logged DESC LIMIT 5");
$u_res = mysqli_query($conn, "SELECT goal, weight FROM users WHERE id=$uid");
$u = mysqli_fetch_assoc($u_res);
$goal = strtolower($u['goal'] ?? 'cut');
$user_weight = $u['weight'] ?? 70; // Fallback weight
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Workouts & Activity | FitLife Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #800000;
        }

        .burn-card {
            background: rgba(128, 0, 0, 0.1);
            border: 1px solid var(--primary);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }

        .stat-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            margin-top: 5px;
        }

        .step-progress {
            height: 12px;
            background: #222;
            border-radius: 10px;
            margin: 15px 0;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .step-bar {
            height: 100%;
            background: linear-gradient(90deg, #800000, #ff4d4d);
            width: 0%;
            transition: 0.5s ease;
        }

        table th {
            color: var(--primary);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .nav-item.active {
            border-bottom: 2px solid var(--primary);
            color: white !important;
        }
    </style>
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

    <div class="container" style="margin-top: 30px;">

        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
            <div class="card">
                <h3><i class="fas fa-shoe-prints" style="color: var(--primary);"></i> Step Tracker</h3>
                <input type="number" id="stepInput" placeholder="Enter steps today..." oninput="updateSteps()"
                    class="stat-input">
                <div class="step-progress">
                    <div id="stepBar" class="step-bar"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #888;">
                    <span>Progress: <b id="stepPercent" style="color:white;">0%</b></span>
                    <span>Goal: 10,000</span>
                </div>
            </div>

            <div class="burn-card">
                <h3><i class="fas fa-fire" style="color: #ff4d4d;"></i> Calorie Burner</h3>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <div style="flex: 1;">
                        <small style="color:#aaa;">Duration (Mins)</small>
                        <input type="number" id="duration" value="30" oninput="calcBurn()" class="stat-input">
                    </div>
                    <div style="flex: 1;">
                        <small style="color:#aaa;">Intensity</small>
                        <select id="intensity" onchange="calcBurn()" class="stat-input">
                            <option value="3.5">Light Walking</option>
                            <option value="7.0">Moderate Jog</option>
                            <option value="10.5">HIIT / Strength</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <span id="caloriesBurned" style="font-size: 2.2rem; font-weight: 900; color: white;">0</span>
                    <span style="color: var(--primary); font-weight: bold; font-size: 1.2rem;"> kcal 🔥</span>
                </div>
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

        <div class="card" style="border-left: 5px solid var(--primary); margin-bottom: 25px;">
            <h3>Log Strength Session</h3>
            <form method="POST"
                style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 15px; margin-top: 15px; align-items: end;">
                <div><small>Exercise</small><input type="text" name="exercise" placeholder="Bench Press" required
                        class="stat-input"></div>
                <div><small>Sets</small><input type="number" name="sets" placeholder="0" required class="stat-input">
                </div>
                <div><small>Reps</small><input type="number" name="reps" placeholder="0" required class="stat-input">
                </div>
                <div><small>Weight (kg)</small><input type="number" step="0.1" name="weight" placeholder="0" required
                        class="stat-input"></div>
                <button type="submit" name="log_workout" class="btn"
                    style="background:var(--primary); color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">SAVE</button>
            </form>
        </div>

        <div class="grid">
            <div class="card">
                <h3 style="color: var(--primary);"><i class="fas fa-lightbulb"></i> Today's Plan
                    (<?php echo strtoupper($goal); ?>)</h3>
                <ul style="list-style: none; padding: 0; margin-top: 15px; line-height: 2;">
                    <?php if ($goal == 'bulk'): ?>
                        <li><i class="fas fa-check-circle" style="color: var(--primary);"></i> Back Squats: 4 x 8</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary);"></i> Incline Bench: 4 x 10</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary);"></i> Weighted Pullups: 3 x Max
                        </li>
                    <?php else: ?>
                        <li><i class="fas fa-check-circle" style="color: var(--primary);"></i> Explosive Pushups: 3 x 20
                        </li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary);"></i> Jump Squats: 4 x 15</li>
                        <li><i class="fas fa-check-circle" style="color: var(--primary);"></i> 15 Min Tabata Sprint</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="card">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr style="text-align: left; border-bottom: 2px solid var(--primary);">
                        <th style="padding-bottom: 10px;">Exercise</th>
                        <th>Format</th>
                        <th>Weight</th>
                    </tr>
                    <?php while ($row = mysqli_fetch_assoc($logs_res)): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 12px 0; font-weight: 600;"><?php echo $row['exercise_name']; ?></td>
                            <td style="color: #aaa;"><?php echo $row['sets']; ?> x <?php echo $row['reps']; ?></td>
                            <td style="color: var(--primary); font-weight: bold;"><?php echo $row['weight_kg']; ?> kg</td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        function updateSteps() {
            const val = document.getElementById('stepInput').value || 0;
            const bar = document.getElementById('stepBar');
            const pctText = document.getElementById('stepPercent');
            const goal = 10000;

            let percent = (val / goal) * 100;
            if (percent > 100) percent = 100;

            bar.style.width = percent + '%';
            pctText.innerText = Math.round(percent) + '%';
        }

        function calcBurn() {
            const met = parseFloat(document.getElementById('intensity').value);
            const mins = parseFloat(document.getElementById('duration').value) || 0;
            const weight = <?php echo $user_weight; ?>;

            // Formula: (MET * 3.5 * weight / 200) * minutes
            const burned = Math.round((met * 3.5 * weight / 200) * mins);
            document.getElementById('caloriesBurned').innerText = burned;
        }

        window.onload = calcBurn;
    </script>
</body>

</html>