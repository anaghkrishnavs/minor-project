<?php
include_once 'db_config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$uid = $_SESSION['user_id'];
$selected_date = isset($_GET['view_date']) ? $_GET['view_date'] : date('Y-m-d');

// FETCH USER DATA
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$uid");
$u = mysqli_fetch_assoc($result);

// FETCH WEIGHT HISTORY
$chart_query = mysqli_query($conn, "SELECT log_date, weight FROM weight_log WHERE user_id=$uid ORDER BY log_date ASC LIMIT 10");
$dates = []; $weights = [];
while ($row = mysqli_fetch_assoc($chart_query)) {
    $dates[] = date('M d', strtotime($row['log_date']));
    $weights[] = $row['weight'];
}

// PROGRESS FEEDBACK LOGIC
$starting_w = (float)$u['starting_weight'];
$current_w = (float)$u['weight'];
$diff = round($current_w - $starting_w, 2);
$goal = strtolower($u['goal']); 

$status_class = ""; $message = "";

// NEW: Check for zero change (First login or stable weight)
if ($diff == 0) {
    $status_class = "neutral-glow"; 
    $message = "Welcome to your journey! Update your weight daily to see your progress.";
} else {
    if ($goal == 'cut') {
        if ($diff < 0) {
            $status_class = "success-glow"; 
            $message = "🎉 Great job! You've lost " . abs($diff) . "kg since you started.";
        } else {
            $status_class = "warning-glow"; 
            $message = "⚠️ Weight up by " . $diff . "kg. Watch your deficit!";
        }
    } else { // Goal is 'bulk'
        if ($diff > 0) {
            $status_class = "success-glow"; 
            $message = "💪 Bulk successful! Gained " . $diff . "kg so far.";
        } else {
            $status_class = "warning-glow"; 
            $message = "⚠️ Weight down by " . abs($diff) . "kg. Need more calories!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard | FitLife Pro</title>
    <style>
        .success-glow { border: 1px solid #2ecc71 !important; box-shadow: 0 0 15px rgba(46, 204, 113, 0.2); }
        .warning-glow { border: 1px solid #e74c3c !important; box-shadow: 0 0 15px rgba(231, 76, 60, 0.2); }
        /* Style for first login / no change */
        .neutral-glow { border: 1px solid var(--glass-border) !important; opacity: 0.9; }
        .feedback-msg { font-size: 1.1rem; margin-top: 10px; font-weight: 500; }
        .logout-btn { background: rgba(255,255,255,0.1); padding: 8px 15px; border-radius: 8px; color: white; text-decoration: none; border: 1px solid var(--glass-border); transition: 0.3s; }
        .logout-btn:hover { background: var(--primary); }
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="font-weight: 800; margin: 0;">Welcome, <?php echo htmlspecialchars($u['username']); ?></h2>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="card <?php echo $status_class; ?>" style="text-align: center; margin-bottom: 30px; padding: 30px; border-radius: 20px;">
            <h3 style="border: none; margin: 0; color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Current Goal: <?php echo strtoupper($u['goal']); ?></h3>
            <p class="feedback-msg" style="color: white;"><?php echo $message; ?></p>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
            <div class="card">
                <h3 style="margin-bottom: 20px;">Physique Stats</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border);">
                        <small style="color: #888; display: block; margin-bottom: 5px;">Current Weight</small>
                        <i class="fas fa-weight" style="color: var(--primary); margin-right: 10px;"></i> 
                        <strong style="font-size: 1.2rem;"><?php echo $current_w; ?> kg</strong>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border);">
                        <small style="color: #888; display: block; margin-bottom: 5px;">Starting Point</small>
                        <i class="fas fa-history" style="color: var(--primary); margin-right: 10px;"></i> 
                        <strong style="font-size: 1.2rem;"><?php echo $starting_w; ?> kg</strong>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h3>Weight Journey</h3>
                <div style="height: 250px; margin-top: 15px;">
                    <canvas id="weightChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('weightChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Weight (kg)',
                    data: <?php echo json_encode($weights); ?>,
                    borderColor: '#800000',
                    backgroundColor: 'rgba(128, 0, 0, 0.1)',
                    borderWidth: 3, 
                    pointBackgroundColor: '#800000',
                    pointRadius: 4,
                    tension: 0.4, 
                    fill: true
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#888' } },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        });
    </script>
</body>
</html>