<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'developer') { header("Location: dev_dashboard.php"); exit(); } 
    else { header("Location: home.php"); exit(); }
}
$signup_errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $bio = trim($_POST['bio']);
    $portfolio_link = trim($_POST['portfolio_link']);

    if (empty($username) || strlen($username) < 3) $signup_errors[] = "Studio Name must be at least 3 characters.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $signup_errors[] = "A valid email is required.";
    if (empty($password) || strlen($password) < 8) $signup_errors[] = "Password must be at least 8 characters long.";
    if ($password !== $confirm_password) $signup_errors[] = "Passwords do not match.";
    if (empty($bio)) $signup_errors[] = "A short bio is required.";
    if (!empty($portfolio_link) && !filter_var($portfolio_link, FILTER_VALIDATE_URL)) $signup_errors[] = "Portfolio link must be a valid URL.";

    if (empty($signup_errors)) {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE name = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) { $signup_errors[] = "Username or Email is already taken."; }
        $stmt_check->close();
    }
    if (empty($signup_errors)) {
        $conn->begin_transaction();
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'developer';
            $stmt_user = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt_user->bind_param("ssss", $username, $email, $hashed_password, $role);
            $stmt_user->execute();
            $user_id = $conn->insert_id;
            $stmt_user->close();
            $stmt_dev = $conn->prepare("INSERT INTO developers (user_id, bio, portfolio_link) VALUES (?, ?, ?)");
            $stmt_dev->bind_param("iss", $user_id, $bio, $portfolio_link);
            $stmt_dev->execute();
            $stmt_dev->close();
            $conn->commit();
            $_SESSION['message'] = "Developer account created! Please log in.";
            $_SESSION['message_type'] = "success";
            header("Location: devlogin.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $signup_errors[] = "Registration failed due to a system error.";
        }
    }
}
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gamer's Valt - Developer Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>

    :root { --primary-neon: #FF8C00; --primary-neon-rgb: 255, 140, 0; --background-dark: #000000; --panel-bg: #1a1a1e; --input-bg: #2a2a2a; --input-text: #e8e8e8; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --border-color: rgba(var(--primary-neon-rgb), 0.3); --error-color: #FF4500; --font-primary: 'Inter', system-ui, sans-serif; }
    @keyframes slideUpIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: var(--font-primary); background-color: var(--background-dark); margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow: hidden; }
    .video-background { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; overflow: hidden; }
    .video-background video { min-width: 100%; min-height: 100%; width: auto; height: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); object-fit: cover; filter: brightness(0.4); }
    .signup-container { width: 100%; max-width: 950px; margin: 40px 20px; background-color: var(--panel-bg); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 0 50px rgba(var(--primary-neon-rgb), 0.15); display: flex; overflow: hidden; animation: slideUpIn 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; opacity: 0; }
    .welcome-panel { flex: 1; padding: 50px; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://picsum.photos/seed/devsignup/800/1200'); background-size: cover; background-position: center; display: flex; flex-direction: column; justify-content: center; }
    .welcome-panel h2 { color: #FF8C00; font-size: 2.5rem; font-weight: 800; line-height: 1.2; margin: 0 0 20px; }
    .welcome-panel p { color: #202020ff;  font-size: 1.1rem;  line-height: 1.6; margin: 0 0 30px; }
    .feature-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px; }
    .feature-list li { display: flex; align-items: center; gap: 10px; font-weight: 500; }
    .feature-list li::before { content: '✔'; color: var(--primary-neon); font-weight: bold; }
    .form-panel { flex: 1.2; padding: 50px; overflow-y: auto; max-height: 90vh; }
    .form-panel h3 { font-size: 2rem; font-weight: 700; margin: 0 0 30px; text-align: center; color: #FF8C00; }
    .form-group { position: relative; margin-bottom: 20px; }
    .form-group input, .form-group textarea { width: 100%; padding: 14px; background-color: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--input-text); font-size: 1rem; outline: none; transition: all 0.2s ease; }
    .form-group label {  position: absolute; top: 14px; left: 14px; color: var(--text-secondary); pointer-events: none; transition: all 0.2s ease; }
    .form-group input:focus + label, .form-group input:not(:placeholder-shown) + label, .form-group textarea:focus + label, .form-group textarea:not(:placeholder-shown) + label { top: -10px; left: 10px; font-size: 0.8rem; background-color: var(--panel-bg); padding: 0 5px; color: var(--primary-neon); }
    .form-group input:focus, .form-group textarea:focus { border-color: var(--primary-neon); }
    .signup-button { background: var(--primary-neon); color: var(--text-dark); padding: 16px; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; width: 100%; margin-top: 15px; }
    .signup-button:hover { background-color: var(--secondary-neon); transform: translateY(-2px); box-shadow: 0 5px 20px rgba(var(--primary-neon-rgb), 0.3); }
    .links-container { margin-top: 25px; text-align: center; font-size: 0.9rem; }
    .links-container a { color: var(--primary-neon); text-decoration: none; font-weight: 500; }
    .alert-messages { padding: 12px; margin-bottom: 20px; border-radius: 8px; text-align: left; font-weight: 500; font-size: 0.9rem; }
    .error-alert { background-color: rgba(255, 69, 0, 0.2); color: var(--error-color); border: 1px solid var(--error-color); }
    @media (max-width: 992px) { .signup-container { flex-direction: column; max-width: 500px; max-height: 90vh; } .welcome-panel { display: none; } .form-panel { max-height: unset; } }
  
    </style>
</head>
<body>
    <div class="video-background">
       <video autoplay loop muted playsinline>
            <source src="devbackgroundvideo.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <!-- Developer Signup Page Starting -->
    <div class="signup-container">
        <div class="welcome-panel">
            <h2>Join the Creators</h2>
            <p>Bring your vision to life on Gamer's Valt. We provide the tools and the audience; you provide the creativity.</p>
            <ul class="feature-list">
                <li>Publish your games to a global audience</li>
                <li>Fair revenue sharing</li>
                <li>Direct community engagement</li>
                <li>Powerful and simple upload tools</li>
            </ul>
        </div>
        <div class="form-panel">
            <h3>Developer Registration</h3>
            <?php if (!empty($signup_errors)): ?>
                <div class="alert-messages error-alert">
                    <?php foreach ($signup_errors as $error): ?><p style="margin: 5px 0;"><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form action="devsignup.php" method="POST">
                <div class="form-group">
                    <input type="text" id="username" name="username" required placeholder=" " value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <label for="username">Developer / Studio Name</label>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" required placeholder=" " value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <label for="email">Email</label>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password">Password</label>
                </div>
                <div class="form-group">
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                    <label for="confirm_password">Confirm Password</label>
                </div>
                <div class="form-group">
                    <textarea id="bio" name="bio" rows="4" required placeholder=" "><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                    <label for="bio">Short Bio / Description</label>
                </div>
                <div class="form-group">
                    <input type="url" id="portfolio_link" name="portfolio_link" placeholder=" " value="<?php echo htmlspecialchars($_POST['portfolio_link'] ?? ''); ?>">
                    <label for="portfolio_link">Portfolio Link (Optional)</label>
                </div>
                <button type="submit" class="signup-button">Create Developer Account</button>
            </form>
            <div class="links-container">
                <a href="devlogin.php">Already a Developer? Log In</a> | 
                <a href="userlogin.php">Are you a Gamer?</a>
            </div>
        </div>
    </div>
    <!-- Developer Signup Page Ending -->
  
</body>
</html>