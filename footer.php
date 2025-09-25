<?php
// PHP Backend Starting (for footer)
// Re-checking session variables to ensure they are available
$footer_is_logged_in = isset($_SESSION['user_id']);
$footer_user_role = $footer_is_logged_in ? $_SESSION['user_role'] : 'guest';
// PHP Backend Ending
?>
<!-- Footer CSS Starting -->
<style>
    :root {
        /* Re-declaring for component isolation, matches your theme */
        --primary-neon: #FF8C00; --primary-neon-rgb: 255, 140, 0;
        --background-station: #0d0d10; --background-panel: #1a1a1e;
        --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --text-dark: #020205;
        --border-color-faint: rgba(var(--primary-neon-rgb), 0.15);
        --font-primary: 'Inter', system-ui, sans-serif;
        --transition-fast: 0.2s cubic-bezier(0.25, 1, 0.5, 1);
        --transition-medium: 0.4s cubic-bezier(0.25, 1, 0.5, 1);
    }

    .site-footer {
        background-color: var(--background-panel);
        color: var(--text-secondary);
        padding: 60px 40px 30px 40px;
        border-top: 2px solid var(--border-color-faint);
        position: relative;
        overflow: hidden;
    }
    
    /* Animation: This will be triggered by JS */
    .site-footer {
        opacity: 0;
        transform: translateY(50px);
        transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }
    .site-footer.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .footer-main {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        margin-bottom: 50px;
        padding-bottom: 50px;
        border-bottom: 1px solid var(--border-color-faint);
        margin-top:100px;
    }

    
    /* Staggered animation for columns */
    .footer-column, .footer-subscribe {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
    .site-footer.is-visible .footer-column,
    .site-footer.is-visible .footer-subscribe {
        opacity: 1;
        transform: translateY(0);
    }
    .site-footer.is-visible .footer-column:nth-child(2) { transition-delay: 0.1s; }
    .site-footer.is-visible .footer-column:nth-child(3) { transition-delay: 0.2s; }
    .site-footer.is-visible .footer-subscribe { transition-delay: 0.3s; }


    .footer-column h4 {
        color: var(--primary-neon);
        font-size: 1.2rem;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: var(--text-secondary);
        text-decoration: none;
        transition: all var(--transition-fast);
    }

    .footer-links a:hover {
        color: var(--primary-neon);
        padding-left: 5px;
    }

    .footer-subscribe p {
        margin-top: 0;
        line-height: 1.6;
    }

    .subscribe-form {
        display: flex;
        margin-top: 20px;
    }

    .subscribe-form input {
        flex-grow: 1;
        padding: 12px 15px;
        border: 1px solid #444;
        background: var(--background-station);
        color: var(--text-primary);
        border-radius: 6px 0 0 6px;
        outline: none;
        transition: all var(--transition-fast);
    }

    .subscribe-form input:focus {
        border-color: var(--primary-neon);
        box-shadow: 0 0 10px rgba(var(--primary-neon-rgb), 0.3);
    }

    .subscribe-form button {
        padding: 12px 20px;
        background: var(--primary-neon);
        color: var(--text-dark);
        border: none;
        border-radius: 0 6px 6px 0;
        font-weight: 700;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    .subscribe-form button:hover {
        background-color: var(--secondary-neon);
    }

    .footer-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-copyright {
        font-size: 0.9rem;
    }

    .footer-socials {
        display: flex;
        gap: 15px;
    }

    .footer-socials a {
        color: var(--text-secondary);
        transition: all var(--transition-fast);
    }

    .footer-socials a:hover {
        color: var(--primary-neon);
        transform: translateY(-3px);
        filter: drop-shadow(0 0 8px rgba(var(--primary-neon-rgb), 0.7));
    }

    .footer-socials svg {
        width: 24px;
        height: 24px;
    }
    
    @media (max-width: 768px) {
        .footer-main {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
        .footer-bottom {
            flex-direction: column-reverse;
            text-align: center;
        }
    }
</style>
<!-- Footer CSS Ending -->

<!-- Footer Starting -->
<footer class="site-footer" id="site-footer">
    <div class="footer-container">
        <div class="footer-main">
            <div class="footer-column">
                <h4>Explore</h4>
                <ul class="footer-links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="explore.php">Explore Games</a></li>
                    <li><a href="#">Featured Content</a></li>
                    <li><a href="#">News & Updates</a></li>
                </ul>
            </div>
            
            <?php if ($footer_user_role === 'developer'): ?>
            <div class="footer-column">
                <h4>Developer Hub</h4>
                <ul class="footer-links">
                    <li><a href="dev_dashboard.php">Your Dashboard</a></li>
                    <li><a href="upload_game.php">Upload a Game</a></li>
                 
                </ul>
            </div>
            <?php elseif ($footer_user_role === 'admin'): ?>
            <div class="footer-column">
               
                </ul>
            </div>
            <?php else:  ?>
            <div class="footer-column">
                <h4>For Players</h4>
                <ul class="footer-links">
                    <li><a href="my_games.php">My Game Library</a></li>
                    <li><a href="wishlist.php">Wishlist</a></li>
                    <li><a href="friends.php">Friends & Community</a></li>
                
                </ul>
            </div>
            <?php endif; ?>

            <div class="footer-column">
                <h4>About Gamer's Valt</h4>
                <ul class="footer-links">
               <li><a href="content_page.php?slug=about-us">About Us</a></li>
<li><a href="content_page.php?slug=privacy-policy">Privacy Policy</a></li>
<li><a href="content_page.php?slug=terms-of-service">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-subscribe">
                <h4>Stay Updated</h4>
                <p>Get the latest news, feature updates, and exclusive deals straight to your inbox.</p>
                <form class="subscribe-form" action="#" method="post">
                    <input type="email" name="email" placeholder="Your email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="footer-copyright">© <?php echo date('Y'); ?> Gamer's Valt. All Rights Reserved.</p>
            <div class="footer-socials">
                <a href="#" aria-label="Twitch">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2H3v16h5v4l4-4h5l4-4V2zm-10 9V7m5 4V7"/></svg>
                </a>
                <a href="#" aria-label="Twitter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
                </a>
                <a href="#" aria-label="Discord">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.3698c-1.325-1.0125-2.846-1.8375-4.449-2.4375-0.342-0.126-0.716-0.039-0.963 0.225-0.84 0.9-1.518 1.95-2.076 3.15-2.088-0.21-4.224-0.21-6.312 0-0.567-1.2-1.245-2.25-2.076-3.15-0.247-0.264-0.621-0.351-0.963-0.225-1.603 0.6-3.124 1.425-4.449 2.4375-0.165 0.126-0.279 0.315-0.315 0.522-0.639 4.077-0.423 7.992 0.81 11.529 0.144 0.405 0.495 0.702 0.918 0.702 1.341 0.684 2.736 1.179 4.167 1.485 0.18 0.036 0.369-0.036 0.495-0.162 0.738-0.756 1.341-1.638 1.836-2.628-0.378-0.099-0.756-0.216-1.125-0.342-0.288-0.099-0.342-0.45-0.126-0.648 0.216-0.198 0.522-0.162 0.756 0.045 2.502 0.945 5.112 0.945 7.614 0 0.234-0.207 0.54-0.243 0.756-0.045 0.216 0.198 0.162 0.549-0.126 0.648-0.369 0.126-0.747 0.243-1.125 0.342 0.495 0.99 1.098 1.872 1.836 2.628 0.126 0.126 0.315 0.198 0.495 0.162 1.431-0.306 2.826-0.792 4.167-1.485 0.423 0 0.774-0.297 0.918-0.702 1.242-3.537 1.458-7.452 0.81-11.529-0.036-0.207-0.15-0.396-0.315-0.522zM8.0298 15.3098c-0.84 0-1.521-0.81-1.521-1.809s0.681-1.809 1.521-1.809c0.849 0 1.521 0.81 1.521 1.809 0 0.999-0.672 1.809-1.521 1.809zM15.9708 15.3098c-0.84 0-1.521-0.81-1.521-1.809s0.681-1.809 1.521-1.809c0.849 0 1.521 0.81 1.521 1.809 0.009 0.999-0.672 1.809-1.521 1.809z"/></svg>
                </a>
                <a href="#" aria-label="YouTube">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2A29 29 0 0 0 23 11.75a29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>
<!-- Footer Ending -->

<!-- Footer Script Starting -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- SCROLL ANIMATION OBSERVER FOR FOOTER ---
        const footer = document.getElementById('site-footer');
        if (footer) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    // When the footer is at least 10% visible
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        // Stop observing once the animation is triggered
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            observer.observe(footer);
        }
    });
</script>
<!-- Footer Script Ending -->