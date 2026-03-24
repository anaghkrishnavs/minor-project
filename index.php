<?php
require_once 'db_config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$error = ""; $success = "";

// NEW: FORGOT PASSWORD / RESET LOGIC
if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['new_password'];

    $res = mysqli_query($conn, "SELECT password, old_password FROM users WHERE email='$email'");
    if ($user_data = mysqli_fetch_assoc($res)) {
        
        // VALIDATION: Check if new password matches current OR the previous one
        $is_current = password_verify($new_pass, $user_data['password']);
        $is_old = ($user_data['old_password'] && password_verify($new_pass, $user_data['old_password']));

        if ($is_current || $is_old) {
            $error = "❌ Security Alert: You cannot reuse your current or previous password.";
        } else {
            $current_hash = $user_data['password'];
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            
            // Move current to old_password and set new password
            mysqli_query($conn, "UPDATE users SET old_password='$current_hash', password='$new_hash' WHERE email='$email'");
            $success = "✅ Password updated! You can now login.";
        }
    } else {
        $error = "Email not found in our system.";
    }
}

// REGISTRATION LOGIC
if (isset($_POST['register_user'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $goal = mysqli_real_escape_string($conn, $_POST['goal']);
    $weight = (float)$_POST['current_weight'];
    $age = (int)$_POST['age'];
    $height = (float)$_POST['height'];

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        $query = "INSERT INTO users (username, email, password, goal, weight, starting_weight, age, height) 
                  VALUES ('$user', '$email', '$pass', '$goal', '$weight', '$weight', '$age', '$height')";
        if (mysqli_query($conn, $query)) { $success = "Account Created! Please Login."; }
    }
}

// LOGIN LOGIC
if (isset($_POST['login_user'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];
    $login_w = (float)$_POST['login_weight'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user_data = mysqli_fetch_assoc($res);

    if ($user_data && password_verify($pass, $user_data['password'])) {
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['username'] = $user_data['username'];
        $uid = $user_data['id'];
        mysqli_query($conn, "UPDATE users SET weight = '$login_w' WHERE id = $uid");
        mysqli_query($conn, "INSERT INTO weight_log (user_id, weight, log_date) VALUES ($uid, '$login_w', CURDATE())");
        header("Location: dashboard.php");
        exit();
    } else { $error = "Invalid email or password."; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FitLife Pro | Access</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #800000; --dark-bg: #0f0f1a; --glass-bg: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.1); }
        body { background: radial-gradient(circle at top right, #2a0000, #0f0f1a); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; font-family: 'Segoe UI', sans-serif; color: white; }
        .card { background: var(--glass-bg); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; width: 420px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5); }
        .tab-btn { flex: 1; padding: 12px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; color: white; transition: 0.4s; }
        .auth-submit-btn { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 10px; font-weight: 800; cursor: pointer; margin-top: 10px; }
        .input-group { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 10px; padding: 10px; margin-bottom: 15px; display: flex; align-items: center; }
        .input-group i { margin-right: 10px; color: var(--primary); width: 20px; text-align: center; }
        .input-group input, .input-group select { background: transparent; border: none; color: white; outline: none; width: 100%; }
        .forgot-link { display: block; text-align: right; font-size: 0.8rem; color: #888; text-decoration: none; margin: -10px 0 15px; transition: 0.3s; }
        .forgot-link:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="card">
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-dumbbell" style="font-size: 3.5rem; color: var(--primary);"></i>
            <h1 style="margin: 10px 0 0;">FitLife<span style="color: var(--primary);">Pro</span></h1>
        </div>

        <div id="tabContainer" style="display: flex; gap: 12px; margin-bottom: 30px; background: rgba(0,0,0,0.2); padding: 6px; border-radius: 15px;">
            <button onclick="showTab('login')" id="loginBtn" class="tab-btn">LOG IN</button>
            <button onclick="showTab('signup')" id="signupBtn" class="tab-btn">REGISTER</button>
        </div>

        <?php if($error) echo "<div style='color:#ff4d4d; margin-bottom:15px; text-align:center; font-size:0.9rem;'>$error</div>"; ?>
        <?php if($success) echo "<div style='color:#2ecc71; margin-bottom:15px; text-align:center; font-size:0.9rem;'>$success</div>"; ?>

        <div id="loginSection" style="display:none;">
            <form method="POST">
                <div class="input-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Email Address" required></div>
                <div class="input-group"><i class="fas fa-lock"></i><input type="password" name="password" placeholder="Password" required></div>
                <a href="javascript:void(0)" onclick="showTab('forgot')" class="forgot-link">Forgot Password?</a>
                <div class="input-group"><i class="fas fa-weight"></i><input type="number" name="login_weight" step="0.1" placeholder="Today's Weight (kg)" required></div>
                <button type="submit" name="login_user" class="auth-submit-btn">Login</button>
            </form>
        </div>

        <div id="forgotSection" style="display:none;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="margin:0;">Reset Password</h3>
                <p style="font-size: 0.75rem; color: #888;">Choose a password you haven't used before.</p>
            </div>
            <form method="POST">
                <div class="input-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Verify Email" required></div>
                <div class="input-group"><i class="fas fa-key"></i><input type="password" name="new_password" placeholder="New Password" required></div>
                <button type="submit" name="reset_password" class="auth-submit-btn">Update Password</button>
                <button type="button" onclick="showTab('login')" style="background:none; border:none; color:#888; width:100%; margin-top:10px; cursor:pointer; font-size:0.8rem;">Back to Login</button>
            </form>
        </div>

        <div id="signupSection">
            <form method="POST">
                <div class="input-group"><i class="fas fa-user"></i><input type="text" name="username" placeholder="Full Name" required></div>
                <div class="input-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Email Address" required></div>
                <div class="input-group"><i class="fas fa-lock"></i><input type="password" name="password" placeholder="Create Password" required></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="input-group"><i class="fas fa-calendar-alt"></i><input type="number" name="age" placeholder="Age" required></div>
                    <div class="input-group"><i class="fas fa-ruler-vertical"></i><input type="number" name="height" placeholder="Height (cm)" required></div>
                </div>
                <div class="input-group">
                    <i class="fas fa-crosshairs"></i>
                    <select name="goal" required style="color:white;">
                        <option value="" disabled selected>Fitness Goal</option>
                        <option value="cut" style="color:black;">Weight Loss / Cutting</option>
                        <option value="bulk" style="color:black;">Muscle Gain / Bulking</option>
                    </select>
                </div>
                <div class="input-group"><i class="fas fa-weight"></i><input type="number" name="current_weight" step="0.1" placeholder="Initial Weight (kg)" required></div>
                <button type="submit" name="register_user" class="auth-submit-btn">Create Account</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(type) {
            document.getElementById('loginSection').style.display = type === 'login' ? 'block' : 'none';
            document.getElementById('signupSection').style.display = type === 'signup' ? 'block' : 'none';
            document.getElementById('forgotSection').style.display = type === 'forgot' ? 'block' : 'none';
            
            // Handle Tab Highlight
            const lBtn = document.getElementById('loginBtn');
            const sBtn = document.getElementById('signupBtn');
            const container = document.getElementById('tabContainer');

            if(type === 'forgot') {
                container.style.display = 'none';
            } else {
                container.style.display = 'flex';
                lBtn.style.background = type === 'login' ? '#800000' : 'transparent';
                sBtn.style.background = type === 'signup' ? '#800000' : 'transparent';
            }
        }
        showTab('signup'); // Default
    </script>
</body>
</html>