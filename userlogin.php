<?php
session_start();
// PHP Backend Starting
require_once 'connection.php'; 
if (isset($_SESSION['user_id'])) { header("Location: home.php"); exit(); }
$login_error = ''; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']); 
    $password = $_POST['password'];
    if (empty($identifier) || empty($password)) { $login_error = "Please enter both your email/username and password."; } 
    else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? OR name = ?");  
        if ($stmt) {
            $stmt->bind_param("ss", $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if ($user['role'] === 'developer' || $user['role'] === 'admin') {
                    $_SESSION['message'] = "Please use the dedicated Developer/Admin portal to log in.";
                    $_SESSION['message_type'] = "error";
                  
                    header("Location: devlogin.php");
                    exit();
                }

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    header("Location: home.php");
                    exit();
                } else { $login_error = "Invalid credentials. Please check your password."; }
            } else { $login_error = "Invalid credentials. User not found."; }
            $stmt->close();
        } else { $login_error = "Database query failed: " . $conn->error; }
    }
}
$conn->close();
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message']); unset($_SESSION['message_type']);

$featured_games = [
    ['title' => 'Cyber Ronin', 'tagline' => 'New Season Available', 'image' => 'https://picsum.photos/seed/cyberronin/1920/1080'],
    ['title' => 'Starfire Protocol', 'tagline' => 'Featured Game', 'image' => 'https://picsum.photos/seed/starfire/1920/1080'],
    ['title' => 'Dungeon Depths', 'tagline' => 'Now On Sale', 'image' => 'https://picsum.photos/seed/dungeondepths/1920/1080']
];
// PHP Backend Ending
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gamer's Valt - User Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    :root { --primary-neon: #FF8C00; --primary-neon-rgb: 255, 140, 0; --background-dark: #000000; --panel-bg: #1a1a1e; --input-bg: #2a2a2a; --input-text: #e8e8e8; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --border-color: rgba(var(--primary-neon-rgb), 0.3); --error-color: #FF4500; --success-color: #32CD32; --font-primary: 'Inter', system-ui, sans-serif; }
    @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }
    @keyframes slideUpFadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes ken-burns { 0% { transform: scale(1.1) translate(0, 0); } 100% { transform: scale(1.2) translate(-5%, 5%); } }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: var(--font-primary); background-color: var(--background-dark); margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow: hidden; }
    .auth-container { display: flex; width: 100vw; height: 100vh; background-color: #111; }
    .auth-showcase { flex: 1; position: relative; overflow: hidden; display: none; }
    @media (min-width: 992px) { .auth-showcase { display: block; } }
    .showcase-slider { list-style: none; padding: 0; margin: 0; width: 100%; height: 100%; }
    .showcase-slide { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; transition: opacity 1.5s cubic-bezier(0.4, 0, 0.2, 1); }
    .showcase-slide.is-active { opacity: 1; }
    .showcase-slide img { width: 100%; height: 100%; object-fit: cover; filter: brightness(0.6); animation: ken-burns 20s ease-in-out infinite alternate; }
    .showcase-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 50%); }
    .showcase-content { position: absolute; bottom: 50px; left: 50px; color: white; }
    .showcase-content > * { opacity: 0; transform: translateY(20px); transition: opacity 0.8s, transform 0.8s; transition-timing-function: cubic-bezier(0.25, 1, 0.5, 1); }
    .showcase-slide.is-active .showcase-content > * { opacity: 1; transform: translateY(0); }
    .showcase-slide.is-active .tagline { transition-delay: 0.4s; }
    .showcase-slide.is-active .title { transition-delay: 0.6s; }
    .showcase-content .tagline { background-color: var(--primary-neon); color: var(--background-dark); padding: 5px 12px; font-size: 0.9rem; font-weight: 700; border-radius: 4px; display: inline-block; }
    .showcase-content .title { font-size: 3rem; font-weight: 800; margin: 10px 0; text-shadow: 0 2px 10px #000; }
    .showcase-dots { position: absolute; bottom: 20px; left: 50px; display: flex; gap: 10px; z-index: 10; }
    .showcase-dots .dot { width: 10px; height: 10px; background-color: rgba(255,255,255,0.3); border-radius: 50%; transition: all 0.3s; cursor: pointer; }
    .showcase-dots .dot:hover { background-color: rgba(255,255,255,0.6); }
    .showcase-dots .dot.is-active { background-color: white; transform: scale(1.2); }
    .auth-form-panel { flex-basis: 500px; display: flex; justify-content: center; align-items: center; padding: 40px; background-color: var(--panel-bg); animation: slideInRight 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    .form-content-wrapper { width: 100%; max-width: 400px; }
    .logo-header { margin-bottom: 30px; text-align: center; }
    .logo-header img { max-height: 80px; filter: drop-shadow(0 0 10px var(--primary-neon)); }
    .form-content-wrapper h2 { color: var(--text-primary); margin: 0 0 30px; font-size: 2.5rem; font-weight: 800; text-align: center; }
    .animated-element { opacity: 0; animation: slideUpFadeIn 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    .logo-header { animation-delay: 0.4s; }
    .form-content-wrapper h2 { animation-delay: 0.5s; }
    #loginForm { animation-delay: 0.6s; }
    .links-container { animation-delay: 0.7s; }
    .form-group { position: relative; margin-bottom: 25px; text-align: left; }
    .form-group input { width: 100%; padding: 14px; background-color: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--input-text); font-size: 1rem; outline: none; transition: all 0.2s ease; }
    .form-group label { position: absolute; top: 14px; left: 14px; color: var(--text-secondary); pointer-events: none; transition: all 0.2s ease; }
    .form-group input:focus + label, .form-group input:not(:placeholder-shown) + label { top: -10px; left: 10px; font-size: 0.8rem; background-color: var(--panel-bg); padding: 0 5px; color: var(--primary-neon); }
    .form-group input:focus { border-color: var(--primary-neon); box-shadow: 0 0 10px rgba(var(--primary-neon-rgb), 0.4); }
    .login-button { background: var(--primary-neon); color: var(--text-dark); padding: 16px; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; width: 100%; margin-top: 15px; }
    .login-button:hover { background-color: var(--secondary-neon); transform: translateY(-2px); box-shadow: 0 5px 20px rgba(var(--primary-neon-rgb), 0.3); }
    .links-container { margin-top: 30px; text-align: center; }
    .links-container a { color: var(--text-secondary); text-decoration: none; display: block; margin: 10px 0; font-weight: 500; transition: color 0.2s; }
    .links-container a:hover { color: var(--primary-neon); }
    .alert-messages { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: bold; }
    .error-alert { background-color: rgba(255, 69, 0, 0.2); color: var(--error-color); border: 1px solid var(--error-color); }
    .success-alert { background-color: rgba(50, 205, 50, 0.2); color: var(--success-color); border: 1px solid var(--success-color); }
    @media (max-width: 992px) { .auth-form-panel { flex-basis: 100%; transform: translateX(0); } }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- User Login Page Starting -->
    <div class="auth-container">
        <div class="auth-showcase">
            <ul class="showcase-slider">
                <?php foreach($featured_games as $index => $game): ?>
                <li class="showcase-slide <?php if($index === 0) echo 'is-active'; ?>">
                    <img src="<?php echo $game['image']; ?>" alt="">
                    <div class="showcase-overlay"></div>
                    <div class="showcase-content">
                        <span class="tagline"><?php echo $game['tagline']; ?></span>
                        <h2 class="title"><?php echo $game['title']; ?></h2>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <ul class="showcase-dots">
                <?php foreach($featured_games as $index => $game): ?>
                <li class="dot <?php if($index === 0) echo 'is-active'; ?>" data-index="<?php echo $index; ?>"></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="auth-form-panel">
            <div class="form-content-wrapper">
                <div class="logo-header animated-element">
                    <img src="logo.png" alt="Gamer's Valt Logo">
                </div>
                <h2 class="animated-element">User Login</h2>
                <?php if (!empty($message)): ?><div class="alert-messages <?php echo htmlspecialchars($message_type); ?>-alert animated-element"><p><?php echo htmlspecialchars($message); ?></p></div><?php endif; ?>
                <?php if (!empty($login_error)): ?><div class="alert-messages error-alert animated-element"><p><?php echo htmlspecialchars($login_error); ?></p></div><?php endif; ?>
                
                <form id="loginForm" action="userlogin.php" method="POST" class="animated-element">
                    <div class="form-group">
                        <input type="text" id="identifier" name="identifier" required autocomplete="username" placeholder=" ">
                        <label for="identifier">Email or Username</label>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder=" ">
                        <label for="password">Password</label>
                    </div>
                    <button type="submit" class="login-button">Login</button>
                </form>

                <div class="links-container animated-element">
                    <a href="usersignup.php">Don't have an account? Sign Up here</a>
                    <a href="#">Forgot Password?</a>
                </div>
            </div>
        </div>
    </div>
    <!-- User Login Page Ending -->
    <script>
    // User Login Script Starting
    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.querySelector('.showcase-slider');
        if (slider) {
            const slides = slider.querySelectorAll('.showcase-slide');
            const dots = document.querySelectorAll('.showcase-dots .dot');
            let currentSlide = 0;
            const slideInterval = 7000;

            function goToSlide(slideIndex) {
                if (slides[currentSlide]) slides[currentSlide].classList.remove('is-active');
                if (dots[currentSlide]) dots[currentSlide].classList.remove('is-active');
                currentSlide = (slideIndex + slides.length) % slides.length;
                if (slides[currentSlide]) slides[currentSlide].classList.add('is-active');
                if (dots[currentSlide]) dots[currentSlide].classList.add('is-active');
            }

            let autoSlide = setInterval(() => { goToSlide(currentSlide + 1); }, slideInterval);

            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    goToSlide(parseInt(dot.dataset.index));
                    clearInterval(autoSlide);
                    autoSlide = setInterval(() => goToSlide(currentSlide + 1), slideInterval);
                });
            });
        }
    });
    // User Login Script Starting
    </script>
  
</body>
</html>