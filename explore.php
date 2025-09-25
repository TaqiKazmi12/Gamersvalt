<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';
$featured_games_result = $conn->query("SELECT g.id, g.title, g.thumbnail, d_user.name as developer_name FROM games g LEFT JOIN developers d ON g.developer_id = d.id LEFT JOIN users d_user ON d.user_id = d_user.id WHERE g.status = 'published' ORDER BY g.created_at DESC LIMIT 5");
$featured_games = $featured_games_result->fetch_all(MYSQLI_ASSOC);
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
$games_per_page = 12;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $games_per_page;

$sql_select = "SELECT g.id, g.title, g.description, g.price, g.thumbnail, c.name as category_name, d_user.name as developer_name";
$sql_from = " FROM games g LEFT JOIN categories c ON g.category_id = c.id LEFT JOIN developers d ON g.developer_id = d.id LEFT JOIN users d_user ON d.user_id = d_user.id";
$sql_where = " WHERE g.status = 'published'";
$params = [];
$param_types = '';

if (!empty($_GET['search'])) {
    $sql_where .= " AND g.title LIKE ?";
    $search_term = '%' . $_GET['search'] . '%';
    $params[] = &$search_term;
    $param_types .= 's';
}
if (!empty($_GET['category']) && is_numeric($_GET['category'])) {
    $sql_where .= " AND g.category_id = ?";
    $params[] = &$_GET['category'];
    $param_types .= 'i';
}
if (!empty($_GET['price'])) {
    if ($_GET['price'] === 'free') { $sql_where .= " AND g.price = 0"; } 
    elseif ($_GET['price'] === 'under10') { $sql_where .= " AND g.price > 0 AND g.price < 10"; } 
    elseif ($_GET['price'] === 'under25') { $sql_where .= " AND g.price >= 10 AND g.price < 25"; }
}

$sql_count = "SELECT COUNT(*) as total" . $sql_from . $sql_where;
$stmt_count = $conn->prepare($sql_count);
if ($params) { $stmt_count->bind_param($param_types, ...$params); }
$stmt_count->execute();
$total_games = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_games / $games_per_page);
$stmt_count->close();

$sql_games = $sql_select . $sql_from . $sql_where . " ORDER BY g.created_at DESC LIMIT ? OFFSET ?";
$param_types .= 'ii';
$params[] = &$games_per_page;
$params[] = &$offset;

$stmt_games = $conn->prepare($sql_games);
if ($params) { $stmt_games->bind_param($param_types, ...$params); }
$stmt_games->execute();
$games = $stmt_games->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_games->close();

$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Games - Gamer's Valt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Roboto+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="explore.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Explore Page Starting -->
    <section class="featured-carousel scroll-animated-element">
        <div class="carousel-track-container">
            <ul class="carousel-track">
                <?php foreach ($featured_games as $index => $game): ?>
                <li class="carousel-slide <?php if ($index === 0) echo 'current-slide'; ?>">
                    <img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="slide-background">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <span class="slide-developer">From <?php echo htmlspecialchars($game['developer_name']); ?></span>
                        <h2 class="slide-title"><?php echo htmlspecialchars($game['title']); ?></h2>
                        <a href="innergamepage.php?id=<?php echo $game['id']; ?>" class="slide-button">Check it Out</a>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <button class="carousel-button prev">❮</button>
        <button class="carousel-button next">❯</button>
        <div class="carousel-nav">
            <?php foreach ($featured_games as $index => $game): ?>
            <button class="carousel-indicator <?php if ($index === 0) echo 'current-slide'; ?>"></button>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="explore-page-container">
        <aside class="filter-sidebar scroll-animated-element">
            <form action="explore.php" method="GET" id="filter-form">
                <h3>Filters</h3>
                <div class="filter-group">
                    <label for="search">Search by Name</label>
                    <input type="text" id="search" name="search" placeholder="e.g., Cyber Ronin" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php if (isset($_GET['category']) && $_GET['category'] == $category['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Price</label>
                    <div class="radio-group">
                        <label><input type="radio" name="price" value="all" <?php if (empty($_GET['price']) || $_GET['price'] == 'all') echo 'checked'; ?>><span>All</span></label>
                        <label><input type="radio" name="price" value="free" <?php if (isset($_GET['price']) && $_GET['price'] == 'free') echo 'checked'; ?>><span>Free to Play</span></label>
                        <label><input type="radio" name="price" value="under10" <?php if (isset($_GET['price']) && $_GET['price'] == 'under10') echo 'checked'; ?>><span>Under $10</span></label>
                        <label><input type="radio" name="price" value="under25" <?php if (isset($_GET['price']) && $_GET['price'] == 'under25') echo 'checked'; ?>><span>Under $25</span></label>
                    </div>
                </div>
                <button type="submit" class="filter-button">Apply Filters</button>
            </form>
        </aside>

        <main class="games-content">
            <div class="games-grid scroll-animated-element">
                <?php if (!empty($games)): ?>
                    <?php foreach ($games as $game): ?>
                        <a href="innergamepage.php?id=<?php echo $game['id']; ?>" class="game-card">
                            <div class="card-image-wrapper">
                                <img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                                <div class="card-overlay">
                                    <span class="card-overlay-button">View Details</span>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-header">
                                    <h4 class="card-title"><?php echo htmlspecialchars($game['title']); ?></h4>
                                    <span class="card-price">
                                        <?php echo $game['price'] > 0 ? '$' . number_format($game['price'], 2) : 'FREE'; ?>
                                    </span>
                                </div>
                                <p class="card-developer">by <?php echo htmlspecialchars($game['developer_name'] ?? 'Unknown Dev'); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-games-found">
                        <h3>No Games Found</h3>
                        <p>Your search returned no results. Try adjusting the filters or searching for something else.</p>
                    </div>
                <?php endif; ?>
            </div>

            <nav class="pagination scroll-animated-element">
                <?php if ($total_pages > 1): ?>
                    <?php
                        $query_params = $_GET;
                        if ($current_page > 1) {
                            $query_params['page'] = $current_page - 1;
                            echo '<a href="?' . http_build_query($query_params) . '">« Prev</a>';
                        }
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $query_params['page'] = $i;
                            $active_class = ($i == $current_page) ? 'active' : '';
                            echo '<a href="?' . http_build_query($query_params) . '" class="' . $active_class . '">' . $i . '</a>';
                        }
                        if ($current_page < $total_pages) {
                            $query_params['page'] = $current_page + 1;
                            echo '<a href="?' . http_build_query($query_params) . '">Next »</a>';
                        }
                    ?>
                <?php endif; ?>
            </nav>
        </main>
    </div>

    <script>
        // Explore Page Starting
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filter-form');
            if(form){
                form.addEventListener('change', function(e) {
                    if(e.target.tagName === 'SELECT' || e.target.type === 'radio') {
                        form.submit();
                    }
                });
            }

            const animatedElements = document.querySelectorAll('.scroll-animated-element');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            animatedElements.forEach(el => {
                if(el) observer.observe(el);
            });

            const track = document.querySelector('.carousel-track');
            if(track){
                const slides = Array.from(track.children);
                const nextButton = document.querySelector('.carousel-button.next');
                const prevButton = document.querySelector('.carousel-button.prev');
                const dotsNav = document.querySelector('.carousel-nav');
                const dots = Array.from(dotsNav.children);
                const slideWidth = slides[0].getBoundingClientRect().width;

                const setSlidePosition = (slide, index) => {
                    slide.style.left = slideWidth * index + 'px';
                };
                slides.forEach(setSlidePosition);

                const moveToSlide = (currentSlide, targetSlide) => {
                    track.style.transform = 'translateX(-' + targetSlide.style.left + ')';
                    currentSlide.classList.remove('current-slide');
                    targetSlide.classList.add('current-slide');
                };

                const updateDots = (currentDot, targetDot) => {
                    currentDot.classList.remove('current-slide');
                    targetDot.classList.add('current-slide');
                };

                const autoPlay = () => {
                    const currentSlide = track.querySelector('.current-slide');
                    const nextSlide = currentSlide.nextElementSibling || slides[0];
                    const currentDot = dotsNav.querySelector('.current-slide');
                    const nextDot = currentDot.nextElementSibling || dots[0];
                    moveToSlide(currentSlide, nextSlide);
                    updateDots(currentDot, nextDot);
                };
                
                let slideInterval = setInterval(autoPlay, 5000);

                const resetInterval = () => {
                    clearInterval(slideInterval);
                    slideInterval = setInterval(autoPlay, 5000);
                };
                
                prevButton.addEventListener('click', e => {
                    const currentSlide = track.querySelector('.current-slide');
                    const prevSlide = currentSlide.previousElementSibling || slides[slides.length - 1];
                    const currentDot = dotsNav.querySelector('.current-slide');
                    const prevDot = currentDot.previousElementSibling || dots[dots.length - 1];
                    moveToSlide(currentSlide, prevSlide);
                    updateDots(currentDot, prevDot);
                    resetInterval();
                });

                nextButton.addEventListener('click', e => {
                    const currentSlide = track.querySelector('.current-slide');
                    const nextSlide = currentSlide.nextElementSibling || slides[0];
                    const currentDot = dotsNav.querySelector('.current-slide');
                    const nextDot = currentDot.nextElementSibling || dots[0];
                    moveToSlide(currentSlide, nextSlide);
                    updateDots(currentDot, nextDot);
                    resetInterval();
                });

                dotsNav.addEventListener('click', e => {
                    const targetDot = e.target.closest('button.carousel-indicator');
                    if (!targetDot) return;
                    
                    const currentSlide = track.querySelector('.current-slide');
                    const currentDot = dotsNav.querySelector('.current-slide');
                    const targetIndex = dots.findIndex(dot => dot === targetDot);
                    const targetSlide = slides[targetIndex];
                    
                    moveToSlide(currentSlide, targetSlide);
                    updateDots(currentDot, targetDot);
                    resetInterval();
                });
            }
        });
        // Explore Page Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>