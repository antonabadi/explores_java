
<?php
// Load models dynamically if available
$destinations = [];
$tours = [];
$blogPosts = [];
$currentPage = isset($_GET['p']) ? (int) $_GET['p'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}
$perPage = 6;
$totalPages = 1;

try {
    if (file_exists(__DIR__ . '/../cms/models/Destination.php')) {
        require_once __DIR__ . '/../cms/models/Destination.php';
        $destinationModel = new Destination();
        $destinations = $destinationModel->all('name', 'ASC');
    }
    
    if (file_exists(__DIR__ . '/../cms/models/Tour.php')) {
        require_once __DIR__ . '/../cms/models/Tour.php';
        $tourModel = new Tour();
        $searchResult = $tourModel->search([], 1, 4);
        $tours = $searchResult['data'] ?? [];
    }

    if (file_exists(__DIR__ . '/../cms/models/BlogPost.php')) {
        require_once __DIR__ . '/../cms/models/BlogPost.php';
        $blogModel = new BlogPost();
        if (method_exists($blogModel, 'getPaginatedPublished')) {
            $paginated = $blogModel->getPaginatedPublished($currentPage, $perPage);
            $blogPosts = $paginated['data'] ?? [];
            $totalPages = $paginated['last_page'] ?? 1;
        } else {
            $blogPosts = $blogModel->getPublished(6);
        }
    }
} catch (Throwable $e) {
    // Fail-safe graceful fallback if database is not initialized
    $destinations = [];
    $tours = [];
    $blogPosts = [];
}

// Fallback dummy data if no posts found in database
if (empty($blogPosts)) {
    $allDummy = [
        [
            'title' => 'Best Time to Visit Mount Bromo and What to Expect',
            'featured_image' => 'assets/images/bromo.jpg',
            'published_at' => '2024-05-10',
            'category_name' => 'Tips',
        ],
        [
            'title' => 'Complete Travel Guide to Yogyakarta',
            'featured_image' => 'assets/images/temple.jpg',
            'published_at' => '2024-05-05',
            'category_name' => 'Guide',
        ],
        [
            'title' => 'Hidden Waterfalls in Java You Must Visit',
            'featured_image' => 'assets/images/waterfall.jpg',
            'published_at' => '2024-04-28',
            'category_name' => 'Nature',
        ],
        [
            'title' => 'Exploring the Tea Plantations of West Java',
            'featured_image' => 'assets/images/hills.jpg',
            'published_at' => '2024-04-20',
            'category_name' => 'Culture',
        ],
    ];

    $totalDummy = count($allDummy);
    $totalPages = (int) ceil($totalDummy / $perPage);
    $offset = ($currentPage - 1) * $perPage;
    $blogPosts = array_slice($allDummy, $offset, $perPage);
}
?>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="container hero-content">
    <p class="eyebrow">Travel Tips &amp; Stories</p>
    <h1>From Our <span class="accent">Blog</span></h1>
    <p class="lead">Inspiration, travel tips, and stories from our adventures across Java.</p>
    <div class="hero-btns">
    </div>
  </div>
</section>

<!-- ================= SEARCH BAR ================= -->
<div class="search-wrap">
  <form class="search-bar" id="searchForm" action="index.php" method="GET">
    <input type="hidden" name="page" value="packages">
    <div class="search-field grow">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
      <div class="sf"><label for="dest">Where to?</label><input id="dest" name="keyword" type="text" placeholder="Search destination"></div>
    </div>
    <div class="search-field">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
      <div class="sf"><label for="cin">Check In</label><input id="cin" name="checkin" type="date"></div>
    </div>
    <div class="search-field">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
      <div class="sf"><label for="cout">Check Out</label><input id="cout" name="checkout" type="date"></div>
    </div>
    <div class="search-field">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
      <div class="sf"><label for="pax">Travelers</label>
        <select id="pax" name="pax"><option>1 Traveler</option><option selected>2 Travelers</option><option>3 Travelers</option><option>4+ Travelers</option></select>
      </div>
    </div>
    <button class="btn btn-primary" type="submit">Search Now</button>
  </form>
</div>

<!-- ================= BLOG ================= -->
<section class="section" id="blog" style="padding-top:0">
  <div class="container">
    <div class="blog-head reveal">
    </div>
    <div class="blog-grid">
      <?php foreach ($blogPosts as $post): ?>
        <article class="blog-card reveal">
          <div class="blog-media" style="background-image:url('<?= htmlspecialchars($post['featured_image'] ?? 'assets/images/bromo.jpg') ?>')"></div>
          <div class="blog-body">
            <h3><a href="/blog-detail?slug=<?= htmlspecialchars($post['slug'] ?? '') ?>" style="color:inherit;text-decoration:none;"><?= htmlspecialchars($post['title']) ?></a></h3>
            <p class="blog-meta">
              <?= !empty($post['published_at']) ? date('M j, Y', strtotime($post['published_at'])) : date('M j, Y') ?>
              <span class="dot"></span>
              <?= htmlspecialchars($post['category_name'] ?? 'General') ?>
              <?php
                $readMins = (!empty($post['reading_time']) && (int)$post['reading_time'] > 0)
                  ? (int)$post['reading_time']
                  : (!empty($post['content']) ? max(1, (int)ceil(str_word_count(strip_tags($post['content'])) / 200)) : 0);
              ?>
              <?php if ($readMins > 0): ?>
                <span class="dot"></span>
                <span>⏱️ <?= $readMins ?> min read</span>
              <?php endif; ?>
            </p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- ================= PAGINATION ================= -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination reveal">
        <a href="?page=blog&p=<?= max(1, $currentPage - 1) ?>" class="pagination-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?page=blog&p=<?= $i ?>" class="pagination-item <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <a href="?page=blog&p=<?= min($totalPages, $currentPage + 1) ?>" class="pagination-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">Next &raquo;</a>
      </div>
    <?php endif; ?>
  </div>
</section>
