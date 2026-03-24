<?php 
require_once 'db_config.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$uid = $_SESSION['user_id'];
$success_msg = "";

// HANDLE PROFILE UPDATES
if(isset($_POST['save'])) {
    $new_user = mysqli_real_escape_string($conn, $_POST['uname']);
    $age = (int)$_POST['age'];
    $weight = (float)$_POST['weight'];
    $height = (float)$_POST['height'];

    $sql = "UPDATE users SET username='$new_user', age='$age', weight='$weight', height='$height' WHERE id=$uid";
    if(mysqli_query($conn, $sql)) {
        $success_msg = "Profile updated successfully!";
        $_SESSION['username'] = $new_user; // Update session name in case it's used in navbar
    }
}

// FETCH USER DATA (including the age/height you added to the DB)
$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));

// Calculate Maintenance Calories (Mifflin-St Jeor Equation)
$maint = (10 * $u['weight']) + (6.25 * $u['height']) - (5 * $u['age']) + 5;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | FitLife Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Locked State - Inputs look like static text */
        .is-locked input {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid transparent !important;
            color: #aaa !important;
            cursor: not-allowed;
            box-shadow: none !important;
        }
        /* Editable State - Inputs glow maroon */
        .is-editable input {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid var(--primary) !important;
            color: white !important;
            cursor: text;
        }
        .hidden { display: none !important; }
        .stat-label { font-size: 0.75rem; color: var(--primary); font-weight: 700; margin-bottom: 5px; display: block; text-transform: uppercase; }
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


    <div class="container" style="margin-top: 50px;">
        <?php if($success_msg) echo "<p style='color:#2ecc71; text-align:center; margin-bottom:15px; font-weight:600;'>$success_msg</p>"; ?>
        
        <div class="card is-locked" id="profileCard">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                <div>
                    <h3 style="margin:0; border:none; font-size: 1.5rem;">Physique Profile</h3>
                    <small style="color:#888;">Starting Weight: <b style="color:var(--primary);"><?php echo $u['starting_weight']; ?> kg</b></small>
                </div>
                <button type="button" id="editBtn" onclick="toggleEdit()" style="background:var(--primary); color:white; border:none; padding:10px 20px; border-radius:10px; cursor:pointer; font-weight: 700; transition: 0.3s;">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </button>
            </div>
            
            <form method="POST">
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="field-container">
                        <span class="stat-label">Full Name</span>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="uname" id="f_name" value="<?php echo htmlspecialchars($u['username']); ?>" required disabled>
                        </div>
                    </div>
                    <div class="field-container">
                        <span class="stat-label">Account Email</span>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" value="<?php echo $u['email']; ?>" readonly style="opacity:0.5;">
                        </div>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div class="field-container">
                        <span class="stat-label">Age</span>
                        <div class="input-group">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="number" name="age" id="f_age" value="<?php echo $u['age']; ?>" required disabled oninput="liveCalc()">
                        </div>
                    </div>
                    <div class="field-container">
                        <span class="stat-label">Height (cm)</span>
                        <div class="input-group">
                            <i class="fas fa-ruler-vertical"></i>
                            <input type="number" name="height" id="f_height" value="<?php echo $u['height']; ?>" required disabled oninput="liveCalc()">
                        </div>
                    </div>
                    <div class="field-container">
                        <span class="stat-label">Weight (kg)</span>
                        <div class="input-group">
                            <i class="fas fa-weight"></i>
                            <input type="number" name="weight" id="f_weight" step="0.1" value="<?php echo $u['weight']; ?>" required disabled oninput="liveCalc()">
                        </div>
                    </div>
                </div>

                <button type="submit" name="save" id="saveBtn" class="hidden" style="background: #27ae60; width: 100%; color: white; border: none; padding: 15px; margin-top: 25px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 1rem; text-transform: uppercase; box-shadow: 0 10px 20px rgba(39, 174, 96, 0.2);">
                    Confirm & Save Changes
                </button>
            </form>

            <div style="margin-top: 40px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px;">
                <p style="color: #888; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem;">Maintenance Calorie Goal</p>
                <div style="display: inline-flex; align-items: baseline; gap: 10px;">
                    <span id="liveMaint" style="font-size: 3.5rem; color: white; font-weight: 900; line-height: 1;"><?php echo round($maint); ?></span>
                    <span style="color: var(--primary); font-weight: 800; font-size: 1.2rem;">KCAL / DAY</span>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleEdit() {
        const card = document.getElementById('profileCard');
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        
        // Target specific inputs
        const inputs = ['f_name', 'f_age', 'f_height', 'f_weight'];

        card.classList.remove('is-locked');
        card.classList.add('is-editable');
        
        inputs.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.disabled = false;
        });

        editBtn.classList.add('hidden');
        saveBtn.classList.remove('hidden');
    }

    function liveCalc() {
        const w = parseFloat(document.getElementById('f_weight').value) || 0;
        const h = parseFloat(document.getElementById('f_height').value) || 0;
        const a = parseInt(document.getElementById('f_age').value) || 0;
        
        if(w > 0 && h > 0 && a > 0) {
            // Formula: 10*weight + 6.25*height - 5*age + 5
            const calories = Math.round((10 * w) + (6.25 * h) - (5 * a) + 5);
            document.getElementById('liveMaint').innerText = calories;
        }
    }
    </script>
</body>
</html>