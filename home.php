<?php
// PHP Backend Starting
session_start();
require_once 'connection.php';
$new_releases_result = $conn->query("
    SELECT g.id, g.title, g.thumbnail, g.price, d_user.name as developer_name 
    FROM games g
    LEFT JOIN developers d ON g.developer_id = d.id
    LEFT JOIN users d_user ON d.user_id = d_user.id
    WHERE g.status = 'published'
    ORDER BY g.created_at DESC
    LIMIT 8
");
$new_releases = $new_releases_result->fetch_all(MYSQLI_ASSOC);
$top_sellers_result = $conn->query("
    SELECT g.id, g.title, g.thumbnail, g.price, d_user.name as developer_name, COUNT(p.id) as sales
    FROM games g
    LEFT JOIN purchases p ON g.id = p.game_id
    LEFT JOIN developers d ON g.developer_id = d.id
    LEFT JOIN users d_user ON d.user_id = d_user.id
    WHERE g.status = 'published'
    GROUP BY g.id
    ORDER BY sales DESC
    LIMIT 4
");
$top_sellers = $top_sellers_result->fetch_all(MYSQLI_ASSOC);
$indie_spotlight_result = $conn->query("
    SELECT g.id, g.title, g.description, g.thumbnail, d_user.name as developer_name
    FROM games g
    LEFT JOIN developers d ON g.developer_id = d.id
    LEFT JOIN users d_user ON d.user_id = d_user.id
    WHERE g.status = 'published'
    ORDER BY RAND()
    LIMIT 1
");
$indie_spotlight = $indie_spotlight_result->fetch_assoc();

$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gamer's Valt - Your Universe of Games</title>
    <link rel="stylesheet" href="navbar.css">
    <style>
    /* Home Page CSS Starting */
    :root {
        --primary-neon: #FF8C00; --primary-neon-rgb: 255, 140, 0;
        --secondary-neon: #FFA500; --background-deep-space: #020205;
        --background-station: #0d0d10; --background-panel: #1a1a1e;
        --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --text-dark: #020205;
        --border-color-faint: rgba(var(--primary-neon-rgb), 0.15);
        --border-color-strong: rgba(var(--primary-neon-rgb), 0.4);
        --font-primary: 'Inter', system-ui, sans-serif;
        --shadow-glow-medium: 0 0 25px rgba(var(--primary-neon-rgb), 0.25);
        --shadow-glow-strong: 0 0 40px rgba(var(--primary-neon-rgb), 0.4);
        --transition-fast: 0.2s cubic-bezier(0.25, 1, 0.5, 1);
        --transition-medium: 0.4s cubic-bezier(0.25, 1, 0.5, 1);
    }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
    
    body { background-color: var(--background-station); color: var(--text-primary); font-family: var(--font-primary); margin: 0; }
    
    /* --- General Section Styling --- */
    .section-container { max-width: 1400px; margin: 0 auto; padding: clamp(60px, 10vw, 120px) 40px; }
    .section-header { text-align: center; margin-bottom: 60px; }
    .section-header .section-title { font-size: clamp(2.2rem, 5vw, 3.5rem); font-weight: 800; margin: 0 0 15px; text-shadow: 0 0 15px rgba(var(--primary-neon-rgb), 0.5); }
    .section-header .section-subtitle { font-size: clamp(1rem, 2.5vw, 1.2rem); max-width: 700px; margin: 0 auto; color: var(--text-secondary); line-height: 1.6; }
    .animated-element { opacity: 0; transition: opacity 0.8s ease-out, transform 0.8s ease-out; }
    .animated-element.is-visible { opacity: 1; transform: translateY(0) !important; }
    
    /* --- Hero Section --- */
    .hero-section { height: 100vh; display: flex; justify-content: center; align-items: center; position: relative; text-align: center; overflow: hidden; }
    .video-background { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
    .video-background video { width: 100%; height: 100%; object-fit: cover; }
    .hero-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(13, 13, 16, 0.9) 0%, rgba(13, 13, 16, 0.4) 50%, rgba(13, 13, 16, 0.9) 100%); z-index: 2; }
    .hero-content { position: relative; z-index: 3; padding: 20px; max-width: 900px; }
    .hero-title { font-size: clamp(2.8rem, 8vw, 5.5rem); font-weight: 800; margin: 0 0 20px 0; text-shadow: 0 0 20px rgba(var(--primary-neon-rgb), 0.7); animation: fadeInUp 1s 0.3s ease-out forwards; opacity: 0; }
    .hero-subtitle { font-size: clamp(1.1rem, 2.5vw, 1.4rem); max-width: 700px; margin: 0 auto 40px auto; line-height: 1.7; color: var(--text-primary); animation: fadeInUp 1s 0.6s ease-out forwards; opacity: 0; }
    .hero-button { display: inline-block; background-color: var(--primary-neon); color: var(--text-dark); border: 2px solid var(--primary-neon); border-radius: 6px; padding: 15px 40px; font-size: 1.2rem; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: all var(--transition-medium); animation: fadeInUp 1s 0.9s ease-out forwards; opacity: 0; }
    .hero-button:hover { background-color: var(--secondary-neon); transform: translateY(-5px); box-shadow: var(--shadow-glow-strong); }

    /* --- Features Section --- */
    .features-section { background-color: var(--background-station); border-top: 1px solid var(--border-color-faint); border-bottom: 1px solid var(--border-color-faint); }
    .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; }
    .feature-card { background-color: var(--background-panel); padding: 40px; border: 1px solid transparent; border-radius: 12px; text-align: center; transition: all var(--transition-medium); transform: translateY(40px); }
    .feature-card:hover { transform: translateY(-10px) !important; border-color: var(--border-color-strong); box-shadow: var(--shadow-glow-medium); }
    .feature-icon { margin-bottom: 25px; color: var(--primary-neon); filter: drop-shadow(0 0 8px rgba(var(--primary-neon-rgb), 0.7)); }
    .feature-card h3 { font-size: 1.8rem; margin: 0 0 15px; }
    .feature-card p { font-size: 1rem; color: var(--text-secondary); line-height: 1.7; margin: 0; }
    
    /* --- Game Card & Horizontal Scroll --- */
    .game-showcase-section { background: var(--background-deep-space); }
    .horizontal-scroll-container { position: relative; }
    .game-scroller { display: flex; gap: 30px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 10px 40px 40px 40px; margin: 0 -40px; -ms-overflow-style: none; scrollbar-width: none; }
    .game-scroller::-webkit-scrollbar { display: none; }
    .game-card { background-color: var(--background-panel); border-radius: 8px; text-decoration: none; display: flex; flex-direction: column; overflow: hidden; transition: all var(--transition-medium); border: 1px solid var(--border-color-faint); position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.4); flex: 0 0 280px; scroll-snap-align: start; transform: translateY(40px); }
    .game-card:hover { transform: translateY(-10px) !important; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border-color: var(--border-color-strong); }
    .card-image-wrapper { display: block; position: relative; aspect-ratio: 4 / 3; overflow: hidden; }
    .card-image-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform var(--transition-medium); }
    .game-card:hover .card-image-wrapper img { transform: scale(1.1); }
    .card-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
    .card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .card-title { font-size: 1.2rem; margin: 0; color: var(--text-primary); font-weight: 600; line-height: 1.3; }
    .card-price { font-size: 1.1rem; font-weight: 700; color: var(--primary-neon); white-space: nowrap; text-shadow: 0 0 5px rgba(var(--primary-neon-rgb), 0.5); }
    .card-developer { color: var(--text-secondary); font-size: 0.9rem; margin: 5px 0 auto 0; padding-bottom: 10px; }

    /* --- Top Sellers Section --- */
    .top-sellers-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; }

    /* --- Indie Spotlight Section --- */
    .indie-spotlight-section { background-image: linear-gradient(rgba(13, 13, 16, 0.95), rgba(13, 13, 16, 0.95)), var(--bg-image); background-size: cover; background-position: center; background-attachment: fixed; }
    .spotlight-container { display: grid; grid-template-columns: 1fr 1.5fr; align-items: center; gap: 60px; }
    .spotlight-image-wrapper { border-radius: 12px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.6); transform: translateY(40px); }
    .spotlight-image-wrapper img { width: 100%; height: auto; display: block; }
    .spotlight-content { transform: translateY(40px); }
    .spotlight-content h2 { font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 800; }
    .spotlight-content .developer-name { color: var(--primary-neon); font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; display: block; }
    .spotlight-content p { font-size: 1.1rem; color: var(--text-secondary); line-height: 1.8; margin-bottom: 40px; }
    
    /* --- CTA Section --- */
    .cta-section { background: var(--background-panel); text-align: center; }
    
    @media(max-width: 992px) {
        .spotlight-container { grid-template-columns: 1fr; }
        .spotlight-image-wrapper { max-width: 500px; margin: 0 auto; }
        .spotlight-content { text-align: center; }
    }
    @media(max-width: 768px) {
        .section-container { padding: 60px 20px; }
    }
    /* Home Page CSS Ending */
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Header Section Starting -->
    <header class="hero-section">
        <div class="video-background">
            <video autoplay loop muted playsinline poster="path/to/fallback-image.jpg">
                <source src="HomeHeaderVideo.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Your Universe of Games Awaits.</h1>
            <p class="hero-subtitle">Discover thousands of titles from indie developers and major studios, or upload your own creation to the world's next great gaming platform.</p>
            <a href="explore.php" class="hero-button">Explore The Valt</a>
        </div>
    </header>
    <!-- Header Section Ending -->

    <!-- Features Section Starting -->
    <section class="features-section">
        <div class="section-container">
            <div class="section-header animated-element">
                <h2 class="section-title">A New Era for Gamers & Creators</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card animated-element">
                    <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg></div>
                    <h3>Explore a Universe</h3>
                    <p>Dive into a massive, ever-expanding library of games. From chart-topping AAA hits to groundbreaking indie gems, your next favorite game is waiting.</p>
                </div>
                <div class="feature-card animated-element" style="transition-delay: 0.2s;">
                    <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg></div>
                    <h3>Empower Your Creativity</h3>
                    <p>For developers, Gamer's Valt is your launchpad. Upload your creations, reach a global audience, and build a community around your vision with our powerful tools.</p>
                </div>
                <div class="feature-card animated-element" style="transition-delay: 0.4s;">
                    <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
                    <h3>Unlock Exclusive Content</h3>
                    <p>Gain access to unbeatable deals, seasonal sales, and exclusive content you won't find anywhere else. Your loyalty is rewarded in the Valt.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Features Section Ending -->

    <!-- New Releases Section Starting -->
    <?php if (!empty($new_releases)): ?>
    <section class="game-showcase-section">
        <div class="section-container">
            <div class="section-header animated-element">
                <h2 class="section-title">New Releases</h2>
                <p class="section-subtitle">Fresh from the developers' studios. Be the first to play the latest and greatest games to hit Gamer's Valt.</p>
            </div>
            <div class="horizontal-scroll-container">
                <div class="game-scroller">
                    <?php foreach ($new_releases as $index => $game): ?>
                        <a href="innergamepage.php?id=<?php echo $game['id']; ?>" class="game-card animated-element" style="transition-delay: <?php echo $index * 0.1; ?>s">
                            <div class="card-image-wrapper"><img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt=""></div>
                            <div class="card-content">
                                <div class="card-header">
                                    <h4 class="card-title"><?php echo htmlspecialchars($game['title']); ?></h4>
                                    <p class="card-price"><?php echo $game['price'] > 0 ? '$' . number_format($game['price'], 2) : 'FREE'; ?></p>
                                </div>
                                <p class="card-developer">by <?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Dev'); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <!-- New Releases Section Ending -->

    <!-- Top Sellers Section Starting -->
    <?php if (!empty($top_sellers)): ?>
    <section class="top-sellers-section">
        <div class="section-container">
            <div class="section-header animated-element">
                <h2 class="section-title">Top Sellers</h2>
                <p class="section-subtitle">These are the chart-toppers and fan-favorites that players can't get enough of. See what's trending in the Valt.</p>
            </div>
            <div class="top-sellers-grid">
                <?php foreach ($top_sellers as $index => $game): ?>
                    <a href="innergamepage.php?id=<?php echo $game['id']; ?>" class="game-card animated-element" style="transition-delay: <?php echo $index * 0.15; ?>s">
                        <div class="card-image-wrapper"><img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt=""></div>
                        <div class="card-content">
                            <div class="card-header">
                                <h4 class="card-title"><?php echo htmlspecialchars($game['title']); ?></h4>
                                <p class="card-price"><?php echo $game['price'] > 0 ? '$' . number_format($game['price'], 2) : 'FREE'; ?></p>
                            </div>
                            <p class="card-developer">by <?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Dev'); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <!-- Top Sellers Section Ending -->

    <!-- Indie Spotlight Section Starting -->
    <?php if ($indie_spotlight): ?>
    <section class="indie-spotlight-section" style="--bg-image: url('<?php echo htmlspecialchars($indie_spotlight['thumbnail']); ?>')">
        <div class="section-container">
            <div class="spotlight-container">
                <div class="spotlight-image-wrapper animated-element">
                    <img src="<?php echo htmlspecialchars($indie_spotlight['thumbnail']); ?>" alt="<?php echo htmlspecialchars($indie_spotlight['title']); ?>">
                </div>
                <div class="spotlight-content animated-element" style="transition-delay: 0.2s;">
                    <h2 class="section-title" style="text-align: left; margin: 0;"><?php echo htmlspecialchars($indie_spotlight['title']); ?></h2>
                    <span class="developer-name">by <?php echo htmlspecialchars($indie_spotlight['developer_name'] ?? 'A Visionary Creator'); ?></span>
                    <p><?php echo htmlspecialchars(substr($indie_spotlight['description'], 0, 250)) . '...'; ?></p>
                    <a href="innergamepage.php?id=<?php echo $indie_spotlight['id']; ?>" class="hero-button">Discover This Gem</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <!-- Indie Spotlight Section Ending -->

    <!-- Call to Action Section Starting -->
    <section class="cta-section">
        <div class="section-container">
            <div class="section-header animated-element">
                <h2 class="section-title">Join The Valt</h2>
                <p class="section-subtitle">Create your account to start your collection, wishlist your favorite titles, and connect with a universe of gamers and creators. Your next adventure is just a click away.</p>
                <a href="usersignup.php" class="hero-button" style="margin-top: 20px; animation: none; opacity: 1;">Create Your Account</a>
            </div>
        </div>
    </section>
    <!-- Call to Action Section Ending -->

    <script>
    // Home Page Script Starting
    document.addEventListener('DOMContentLoaded', function() {
        const animatedElements = document.querySelectorAll('.animated-element');
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target); 
                }
            });
        }, { root: null, threshold: 0.1 });

        animatedElements.forEach(el => observer.observe(el));
    });
    // Home Page Script Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>