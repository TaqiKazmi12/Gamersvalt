<?php
http_response_code(404);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <style>
    /* 404 Page CSS Starting */
    :root {
        --primary-neon: #FF8C00; 
        --primary-neon-rgb: 255, 140, 0;
        --secondary-neon: #FFA500;
        --background-station: #0d0d10; 
        --text-primary: #e8e8e8; 
        --text-secondary: #a0a8b4;
        --border-color-faint: rgba(var(--primary-neon-rgb), 0.15);
        --font-primary: 'Inter', system-ui, sans-serif;
    }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes glitch {
      2%,64% { transform: translate(2px,0) skew(0deg); }
      4%,60% { transform: translate(-2px,0) skew(0deg); }
      62% { transform: translate(0,0) skew(5deg); }
    }
    @keyframes scanline {
        0% { transform: translateY(0); }
        100% { transform: translateY(100%); }
    }

    body {
        background-color: var(--background-station);
        font-family: var(--font-primary);
        color: var(--text-primary);
        margin: 0;
        overflow: hidden; 
    }

    .not-found-container {
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        height: 100vh;
        width: 100vw;
        position: relative;
        padding: 20px;
        box-sizing: border-box;
    }

    .background-effects {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        overflow: hidden;
        z-index: 1;
        transition: transform 0.2s ease-out;
    }
    .background-effects::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image:
            linear-gradient(rgba(var(--primary-neon-rgb), 0.08) 1px, transparent 1px),
            linear-gradient(90deg, rgba(var(--primary-neon-rgb), 0.08) 1px, transparent 1px);
        background-size: 40px 40px;
        opacity: 0.5;
    }
    .background-effects::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: var(--background-station);
        opacity: 0.9;
    }

    .scanline-effect {
        position: absolute;
        top: -100%; left: 0;
        width: 100%; height: 100%;
        background: linear-gradient(0deg, transparent, rgba(var(--primary-neon-rgb), 0.1), transparent);
        animation: scanline 10s linear infinite;
        opacity: 0.3;
    }
    
    .content-wrapper {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .error-code {
        font-size: clamp(8rem, 25vw, 15rem);
        font-weight: 800;
        line-height: 1;
        position: relative;
        color: var(--primary-neon);
        text-shadow: 0 0 10px rgba(var(--primary-neon-rgb), 0.5), 0 0 30px rgba(var(--primary-neon-rgb), 0.3);
        animation: fadeIn 1s ease-out;
    }

    .error-code-glitch {
        animation: glitch 1s linear infinite;
    }
    .error-code-glitch::before,
    .error-code-glitch::after {
        content: '404';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--background-station);
        overflow: hidden;
    }
    .error-code-glitch::before {
        left: 2px;
        text-shadow: -2px 0 #ff00c1;
        clip: rect(44px, 450px, 56px, 0);
        animation: glitch 3s infinite linear alternate-reverse;
    }
    .error-code-glitch::after {
        left: -2px;
        text-shadow: -2px 0 #00fff9, 2px 2px #ff00c1;
        clip: rect(85px, 450px, 90px, 0);
        animation: glitch 2s infinite linear alternate-reverse;
    }
    
    .error-title {
        font-size: clamp(1.5rem, 4vw, 2.5rem);
        font-weight: 700;
        margin: 0 0 15px;
        animation: fadeInUp 0.8s 0.2s ease-out backwards;
    }

    .error-description {
        font-size: clamp(1rem, 2.5vw, 1.2rem);
        color: var(--text-secondary);
        max-width: 600px;
        margin: 0 0 40px;
        line-height: 1.6;
        animation: fadeInUp 0.8s 0.4s ease-out backwards;
    }

    .action-buttons {
        display: flex;
        gap: 20px;
        animation: fadeInUp 0.8s 0.6s ease-out backwards;
    }

    .action-button {
        display: inline-block;
        background-color: transparent;
        color: var(--primary-neon);
        border: 2px solid var(--primary-neon);
        border-radius: 6px;
        padding: 12px 30px;
        font-size: 1rem;
        font-weight: bold;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }
    .action-button:hover {
        background-color: var(--primary-neon);
        color: var(--text-dark);
        box-shadow: 0 0 25px rgba(var(--primary-neon-rgb), 0.7);
        transform: translateY(-3px);
    }
    /* 404 Page CSS Ending */
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="not-found-container">
        <div class="background-effects" id="background-effects">
            <div class="scanline-effect"></div>
        </div>
        
        <div class="content-wrapper">
            <h1 class="error-code">
                <span class="error-code-glitch">404</span>
            </h1>
            <h2 class="error-title">// PAGE NOT FOUND</h2>
            <p class="error-description">
                Signal lost. The page you're looking for might have been moved to a different sector, deleted during a data purge, or perhaps never existed in this timeline.
            </p>
            <div class="action-buttons">
                <a href="home.php" class="action-button">Return to Home Base</a>
                <a href="explore.php" class="action-button">Explore The Valt</a>
            </div>
        </div>
    </main>

    <script>
    // 404 Page JS Starting
    document.addEventListener('DOMContentLoaded', function() {
        const background = document.getElementById('background-effects');
        if (background) {
            document.addEventListener('mousemove', function(e) {
                const x = (e.clientX - window.innerWidth / 2) / 50;
                const y = (e.clientY - window.innerHeight / 2) / 50;
                background.style.transform = `translateX(${-x}px) translateY(${-y}px)`;
            });
        }
    });
    // 404 Page JS Ending
    </script>
</body>
</html>