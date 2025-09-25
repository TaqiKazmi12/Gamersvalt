<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer') { header("Location: dev_dashboard.php"); exit(); } 
    else { header("Location: home.php"); exit(); }
}
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];
    if (empty($identifier) || empty($password)) { $login_error = "All fields are required."; } 
    else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE (email = ? OR name = ?) AND role = 'developer'");
        if ($stmt) {
            $stmt->bind_param("ss", $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $stmt_dev_id = $conn->prepare("SELECT id FROM developers WHERE user_id = ?");
                    $stmt_dev_id->bind_param("i", $user['id']);
                    $stmt_dev_id->execute();
                    $dev_result = $stmt_dev_id->get_result();
                    if ($dev_result->num_rows === 1) {
                        $developer_data = $dev_result->fetch_assoc();
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['developer_id'] = $developer_data['id'];
                        $_SESSION['username'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];
                        header("Location: dev_dashboard.php");
                        exit();
                    } else { $login_error = "Developer data integrity error. Please contact support."; }
                    $stmt_dev_id->close();
                } else { $login_error = "Invalid credentials. Please try again."; }
            } else { $login_error = "Invalid credentials or not a developer account."; }
            $stmt->close();
        } else { $login_error = "Database query failed."; }
    }
}
$conn->close();
$success_message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); unset($_SESSION['message_type']);
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gamer's Valt - Developer Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
   
    :root { --primary-neon: #FF8C00; --primary-neon-rgb: 255, 140, 0; --background-dark: #000000; --panel-bg: #1a1a1e; --input-bg: #2a2a2a; --input-text: #e8e8e8; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --border-color: rgba(var(--primary-neon-rgb), 0.3); --error-color: #FF4500; --success-color: #4CAF50; --font-primary: 'Inter', system-ui, sans-serif; }
    @keyframes slideUpIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: var(--font-primary); background-color: var(--background-dark); margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow: hidden; }
    .video-background { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; overflow: hidden; }
    .video-background video { min-width: 100%; min-height: 100%; width: auto; height: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); object-fit: cover; filter: brightness(0.4); }
    .login-container { width: 100%; max-width: 950px; margin: 40px 20px; background-color: var(--panel-bg); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 0 50px rgba(var(--primary-neon-rgb), 0.15); display: flex; overflow: hidden; animation: slideUpIn 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; opacity: 0; }
    .welcome-panel { flex: 1; padding: 50px; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://picsum.photos/seed/devlogin/800/1200'); background-size: cover; background-position: center; display: flex; flex-direction: column; justify-content: center; }
 .welcome-panel .logo-img {
    max-height: 270px;
    margin-bottom: 30px;
    filter: drop-shadow(0 0 10px var(--primary-neon));
} .welcome-panel h2 { color:#FF8C00;  font-size: 2.5rem; font-weight: 800; line-height: 1.2; margin: 0 0 20px; }
    .welcome-panel p { font-size: 1.1rem; color: var(--text-secondary); line-height: 1.6; margin: 0; }
    .form-panel { flex: 1.2; padding: 50px; }
    .form-panel h3 { color:#FF8C00;  font-size: 2rem; font-weight: 700; margin: 0 0 30px; text-align: center; }
    .form-group { position: relative; margin-bottom: 25px; }
    .form-group input { width: 100%; padding: 14px; background-color: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--input-text); font-size: 1rem; outline: none; transition: all 0.2s ease; }
    .form-group label { position: absolute; top: 14px; left: 14px; color: var(--text-secondary); pointer-events: none; transition: all 0.2s ease; }
    .form-group input:focus + label, .form-group input:not(:placeholder-shown) + label { top: -10px; left: 10px; font-size: 0.8rem; background-color: var(--panel-bg); padding: 0 5px; color: var(--primary-neon); }
    .form-group input:focus { border-color: var(--primary-neon); }
    .login-button { background: var(--primary-neon); color: var(--text-dark); padding: 16px; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; width: 100%; margin-top: 15px; }
    .login-button:hover { background-color: var(--secondary-neon); transform: translateY(-2px); box-shadow: 0 5px 20px rgba(var(--primary-neon-rgb), 0.3); }
    .links-container { margin-top: 25px; text-align: center; font-size: 0.9rem; }
    .links-container a { color: var(--primary-neon); text-decoration: none; font-weight: 500; }
    .alert-messages { padding: 12px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: 500; font-size: 0.9rem; }
    .error-alert { background-color: rgba(255, 69, 0, 0.2); color: var(--error-color); border: 1px solid var(--error-color); }
    .success-alert { background-color: rgba(76, 175, 80, 0.2); color: var(--success-color); border: 1px solid var(--success-color); }
    @media (max-width: 992px) { .login-container { flex-direction: column; max-width: 500px; max-height: 90vh; } .welcome-panel { display: none; } .form-panel { max-height: unset; } }
  
    </style>
</head>
<body>
    <div class="video-background">
       <video autoplay loop muted playsinline>
            <source src="devbackgroundvideo.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <!-- Developer Login Page Starting -->
    <div class="login-container">
        <div class="welcome-panel">
            <img src="logo.png" alt="Gamer's Valt Logo" class="logo-img">
            <h2>Developer Portal</h2>
            <p>Welcome back, creator. Access your dashboard, manage your games, and view your earnings.</p>
        </div>
        <div class="form-panel">
            <h3>Portal Access</h3>
            <?php if (!empty($login_error) || !empty($success_message)): ?>
                <div class="alert-messages <?php echo !empty($login_error) ? 'error-alert' : 'success-alert'; ?>">
                    <p><?php echo htmlspecialchars($login_error . $success_message); ?></p>
                </div>
            <?php else: ?>
                <div class="alert-messages" style="background-color: rgba(76, 175, 80, 0.1); border: 1px solid #4CAF50;">
                    <p style="color: #4CAF50;">Please enter your credentials to proceed.</p>
                </div>
            <?php endif; ?>

            <form action="devlogin.php" method="POST">
                <div class="form-group">
                    <input type="text" id="identifier" name="identifier" required placeholder=" ">
                    <label for="identifier">Email or Studio Name</label>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password">Password</label>
                </div>
                <button type="submit" class="login-button">Access Portal</button>
            </form>
            <div class="links-container">
                <a href="devsignup.php">Create a new Developer Account</a>
            </div>
        </div>
    </div>
    <!-- Developer Login Page Ending -->
    
</body>
</html>