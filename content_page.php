<?php
// PHP Backend Starting
session_start();
require_once 'connection.php';


$page_data = [
    'title' => 'Page Not Found',
    'content' => '<h2>Error 404</h2><p>The content you are looking for could not be found. Please check the URL slug and ensure the page exists in the admin panel.</p>',
    'updated_at' => date("Y-m-d H:i:s")
];
$page_slug = $_GET['slug'] ?? '';

if (!empty($page_slug)) {
     $stmt = $conn->prepare("SELECT title, content, updated_at FROM content_pages WHERE slug = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $page_slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $page_data = $result->fetch_assoc();
        } else {
            $page_data['title'] = 'Page Not Found';
            $page_data['content'] = '<h2>Error 404</h2><p>The content for the page with the identifier \''.htmlspecialchars($page_slug).'\' could not be found. Please return to the homepage.</p>';
        }
        $stmt->close();
    }
} else {
   
    header("Location: home.php");
    exit();
}

$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_data['title']); ?> - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <style>
    /* Content Page CSS Starting */
    :root {
        --primary-neon: #FF8C00; 
        --primary-neon-rgb: 255, 140, 0;
        --secondary-neon: #FFA500;
        --background-station: #0d0d10; 
        --background-panel: #1a1a1e;
        --text-primary: #e8e8e8; 
        --text-secondary: #a0a8b4;
        --border-color-faint: rgba(var(--primary-neon-rgb), 0.15);
        --font-primary: 'Inter', system-ui, sans-serif;
    }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    html { scroll-behavior: smooth; }
    body { background-color: var(--background-station); font-family: var(--font-primary); color: var(--text-primary); margin: 0; }
    
    .page-header {
        padding-top: calc(var(--navbar-height, 70px) + 60px);
        padding-bottom: 60px;
        padding-left: 40px;
        padding-right: 40px;
        border-bottom: 1px solid var(--border-color-faint);
        background: linear-gradient(180deg, var(--background-panel) 0%, var(--background-station) 100%);
    }
    .page-header-content {
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
        animation: fadeInUp 0.8s ease-out backwards;
    }
    .page-header-title {
        font-size: clamp(2.8rem, 6vw, 4rem);
        font-weight: 800;
        line-height: 1.1;
        margin: 0 0 10px;
        color: var(--text-primary);
    }
    .page-header-subtitle {
        font-size: 1.1rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .content-layout {
        display: grid;
        grid-template-columns: 1fr 280px;
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 40px;
        gap: 60px;
        align-items: flex-start;
    }
    
    .main-content-article {
        min-width: 0; 
        animation: fadeInUp 0.8s 0.2s ease-out backwards;
    }

    
    .article-body {
        line-height: 1.8;
        font-size: 1.1rem;
        color: var(--text-secondary);
    }
    .article-body > *:first-child { margin-top: 0; }
    .article-body h1, .article-body h2, .article-body h3 {
        color: var(--text-primary);
        font-weight: 700;
        margin: 2.5em 0 1.2em;
        line-height: 1.3;
        scroll-margin-top: calc(var(--navbar-height, 70px) + 20px);
    }
    .article-body h2 { font-size: 1.8rem; border-bottom: 1px solid var(--border-color-faint); padding-bottom: 0.5em; }
    .article-body h3 { font-size: 1.5rem; color: var(--text-secondary); }
    .article-body a { color: var(--primary-neon); text-decoration: none; font-weight: 600; }
    .article-body a:hover { text-decoration: underline; }
    .article-body ul { list-style-type: disc; }
    .article-body ol { list-style-type: decimal; }
    .article-body ul, .article-body ol { padding-left: 25px; }
    .article-body li { margin-bottom: 12px; }
    .article-body blockquote {
        border-left: 4px solid var(--primary-neon);
        padding: 15px 25px;
        margin: 2em 0;
        font-style: italic;
        color: var(--text-primary);
        background: var(--background-panel);
        border-radius: 0 8px 8px 0;
    }
    
    .side-navigation {
        position: sticky;
        top: calc(var(--navbar-height, 70px) + 40px);
    }
    .side-navigation h4 {
        margin: 0 0 15px 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--primary-neon);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    #page-toc ul {
        list-style: none;
        padding: 0;
        margin: 0;
        border-left: 2px solid var(--border-color-faint);
    }
    #page-toc a {
        display: block;
        color: var(--text-secondary);
        text-decoration: none;
        padding: 8px 15px;
        font-size: 0.95rem;
        font-weight: 500;
        border-left: 2px solid transparent;
        margin-left: -2px;
        transition: all 0.2s ease;
    }
    #page-toc a:hover {
        color: var(--text-primary);
        background: rgba(var(--primary-neon-rgb), 0.1);
    }
    #page-toc a.active {
        color: var(--primary-neon);
        font-weight: 700;
        border-left-color: var(--primary-neon);
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .content-layout { grid-template-columns: 1fr; }
        .side-navigation { display: none; } 
    }
    @media (max-width: 768px) {
        .page-header { padding: calc(var(--navbar-height, 70px) + 40px) 20px 40px; }
        .content-layout { margin-top: 40px; padding: 0 20px; }
    }
    /* Content Page CSS Ending */
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Page Hero Starting -->
    <header class="page-header">
        <div class="page-header-content">
            <h1 class="page-header-title"><?php echo htmlspecialchars($page_data['title']); ?></h1>
            <p class="page-header-subtitle">Last Updated: <?php echo date('F jS, Y', strtotime($page_data['updated_at'])); ?></p>
        </div>
    </header>
    <!-- Page Hero Ending -->

    <!-- Main Content Layout Starting -->
    <div class="content-layout">
        <main class="main-content-article">
            <article id="page-article-content" class="article-body">
                <?php echo $page_data['content']; ?>
            </article>
        </main>
        <aside class="side-navigation">
            <h4>On This Page</h4>
            <nav id="page-toc">
            
            </nav>
        </aside>
    </div>
    <!-- Main Content Layout Ending -->

    <?php include 'footer.php'; ?>
    
    <script>
    // Content Page JS Starting
    document.addEventListener('DOMContentLoaded', function() {
        const tocContainer = document.getElementById('page-toc');
        const articleContent = document.getElementById('page-article-content');
 
        if (tocContainer && articleContent) {
            const headings = articleContent.querySelectorAll('h2'); 
            
          if (headings.length > 0) {
                const tocList = document.createElement('ul');
                headings.forEach((heading, index) => {
                 
                    const slug = heading.textContent.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
                    heading.id = slug || `section-${index}`;
                    const listItem = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = `#${heading.id}`;
                    link.textContent = heading.textContent;
                    
                    listItem.appendChild(link);
                    tocList.appendChild(listItem);
                });
                tocContainer.appendChild(tocList);
            } else {
                tocContainer.parentElement.style.display = 'none'; 
            }

            const tocLinks = tocContainer.querySelectorAll('a');
            const observerOptions = {
                rootMargin: `-${(document.getElementById('main-navbar')?.offsetHeight || 70) + 20}px 0px -60% 0px`,
                threshold: 0
            };
            
            const observer = new IntersectionObserver(entries => {
                let activeId = '';
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        activeId = entry.target.id;
                    }
                });

                tocLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${activeId}`) {
                        link.classList.add('active');
                    }
                });
            }, observerOptions);

            headings.forEach(heading => {
                observer.observe(heading);
            });
        }
    });
    // Content Page JS Ending
    </script>
</body>
</html>