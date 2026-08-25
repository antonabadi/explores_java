<?php
// Load dynamic blog post model if available
$post = null;
$recentPosts = [];
$slug = $_GET['slug'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    if (file_exists(__DIR__ . '/../cms/models/BlogPost.php')) {
        require_once __DIR__ . '/../cms/models/BlogPost.php';
        $blogModel = new BlogPost();

        if ($slug !== '') {
            $post = $blogModel->findBySlug($slug);
        } elseif ($id > 0) {
            $post = $blogModel->find($id);
        }

        $recentPosts = $blogModel->getPublished(4);
    }
} catch (Throwable $e) {
    $post = null;
    $recentPosts = [];
}

// Fallback dummy post if database post is not found
if (!$post) {
    $post = [
        'id' => 1,
        'title' => 'Best Time to Visit Mount Bromo and What to Expect',
        'slug' => 'best-time-to-visit-mount-bromo-and-what-to-expect',
        'category_name' => 'Tips',
        'author_name' => 'Admin Explorer',
        'author_avatar' => null,
        'published_at' => '2024-05-10',
        'featured_image' => 'assets/images/bromo.jpg',
        'excerpt' => 'A complete travel guide to planning your adventure to Mount Bromo, from weather forecasts to sunrise view points.',
        'content' => '
            <p>Mount Bromo is one of the most iconic landscapes in Indonesia. Standing tall in the middle of a vast plain called the "Sea of Sand", this active volcano offers an unearthly sunrise experience that attracts travelers from all across the globe.</p>

            <h3>1. The Dry Season (April – October)</h3>
            <p>The dry season is widely regarded as the best time to visit Mount Bromo. During these months, skies are clearer and sunrise views are crisp with minimal cloud disruption. However, keep in mind that night temperatures can drop sharply, so thermal jackets and gloves are essential.</p>

            <h3>2. What to Pack</h3>
            <ul>
                <li>Warm layers and windbreaker jacket</li>
                <li>Comfortable hiking shoes or sneakers</li>
                <li>Dust mask or bandana for the Sea of Sand</li>
                <li>Flashlight or headlamp for early morning walks</li>
            </ul>

            <h3>3. Getting to Bromo</h3>
            <p>Most travelers initiate their trip from Surabaya or Malang. From there, 4WD Jeeps transport visitors up to the Penanjakan sunrise view points before descending to the crater base.</p>
        ',
    ];
}

// Fallback dummy recent posts if empty
if (empty($recentPosts)) {
    $recentPosts = [
        [
            'title' => 'Complete Travel Guide to Yogyakarta',
            'slug' => 'complete-travel-guide-to-yogyakarta',
            'featured_image' => 'assets/images/temple.jpg',
            'published_at' => '2024-05-05',
            'category_name' => 'Guide',
        ],
        [
            'title' => 'Hidden Waterfalls in Java You Must Visit',
            'slug' => 'hidden-waterfalls-in-java-you-must-visit',
            'featured_image' => 'assets/images/waterfall.jpg',
            'published_at' => '2024-04-28',
            'category_name' => 'Nature',
        ],
        [
            'title' => 'Exploring the Tea Plantations of West Java',
            'slug' => 'exploring-the-tea-plantations-of-west-java',
            'featured_image' => 'assets/images/hills.jpg',
            'published_at' => '2024-04-20',
            'category_name' => 'Culture',
        ],
    ];
}
?>

<!-- ================= BLOG DETAIL HERO ================= -->
<section class="hero" style="padding: 100px 0 60px;">
  <div class="container hero-content">
    <p class="eyebrow"><?= htmlspecialchars($post['category_name'] ?? 'Travel Stories') ?></p>
    <h1 style="font-size: 2.5rem; max-width: 850px; margin: 0 auto 20px;"><?= htmlspecialchars($post['title']) ?></h1>
    <p class="blog-meta" style="justify-content: center; font-size: 0.95rem; opacity: 0.9;">
      <span>By <?= htmlspecialchars($post['author_name'] ?? 'Explores Java Team') ?></span>
      <span class="dot" style="display:inline-block; width:4px; height:4px; background:currentColor; border-radius:50%; margin:0 8px;"></span>
      <span><?= !empty($post['published_at']) ? date('F j, Y', strtotime($post['published_at'])) : date('F j, Y') ?></span>
    </p>
  </div>
</section>

<!-- ================= BLOG CONTENT SECTION ================= -->
<section class="section" style="padding-top: 20px;">
  <div class="container" style="max-width: 1100px;">
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 40px; align-items: start;">
      
      <!-- Main Post Content -->
      <article class="glass-card" style="padding: 30px; border-radius: 16px; background: rgba(255,255,255,0.03);">
        <?php if (!empty($post['featured_image'])): ?>
          <div style="margin-bottom: 30px; border-radius: 12px; overflow: hidden; max-height: 450px;">
            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
          </div>
        <?php endif; ?>

        <?php if (!empty($post['excerpt'])): ?>
          <p style="font-size: 1.15rem; font-style: italic; opacity: 0.9; margin-bottom: 25px; border-left: 3px solid var(--accent, #e67e22); padding-left: 15px;">
            <?= htmlspecialchars($post['excerpt']) ?>
          </p>
        <?php endif; ?>

        <div class="blog-article-body" style="line-height: 1.8; font-size: 1.05rem;">
          <?= $post['content'] ?>
        </div>
      </article>

      <!-- Sidebar -->
      <aside>
        <div class="glass-card" style="padding: 24px; border-radius: 16px; margin-bottom: 30px; background: rgba(255,255,255,0.03);">
          <h3 style="font-size: 1.2rem; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">Recent Articles</h3>
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($recentPosts as $recent): ?>
              <?php if (isset($recent['slug']) && $recent['slug'] === $post['slug']) continue; ?>
              <div style="display: flex; gap: 12px; align-items: center;">
                <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #333;">
                  <img src="<?= htmlspecialchars($recent['featured_image'] ?? 'assets/images/bromo.jpg') ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div>
                  <h4 style="font-size: 0.9rem; font-weight: 600; line-height: 1.3; margin: 0 0 4px;">
                    <a href="/blog-detail?slug=<?= htmlspecialchars($recent['slug'] ?? '') ?>" style="color: inherit; text-decoration: none;">
                      <?= htmlspecialchars($recent['title']) ?>
                    </a>
                  </h4>
                  <small style="opacity: 0.7; font-size: 0.8rem;">
                    <?= !empty($recent['published_at']) ? date('M j, Y', strtotime($recent['published_at'])) : date('M j, Y') ?>
                  </small>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>

    </div>
  </div>
</section>
