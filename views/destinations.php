<?php
// =============================================================================
// FRONTEND VIEW: DESTINATIONS (Explores Java)
// Connected with CMS Models: Destination, DestinationArea, DestinationTag, BlogPost
// =============================================================================

declare(strict_types=1);

// 1. Initial State & Params
$keyword        = trim($_GET['keyword'] ?? $_GET['q'] ?? '');
$selectedArea   = trim($_GET['area'] ?? '');
$tagParam       = $_GET['tag'] ?? $_GET['tags'] ?? '';
$selectedTags   = is_array($tagParam) ? $tagParam : ($tagParam !== '' ? explode(',', $tagParam) : []);
$selectedTags   = array_filter(array_map('trim', $selectedTags));
$currentPage    = max(1, (int) ($_GET['p'] ?? $_GET['page'] ?? 1));
$perPage        = 9;

$destinations      = [];
$allAreas          = [];
$allTags           = [];
$destMeta          = [];
$suggestedPosts    = [];
$totalDestinations = 0;
$totalPages        = 1;

// 2. Icon Map Helper for Activity Tags
function getTagIcon(string $slug, ?string $dbIcon = null): string {
    $map = [
        'culinary'    => '🍜',
        'culture'     => '🏛️',
        'hiking'      => '🥾',
        'photography' => '📷',
        'beach'       => '🏖️',
        'adventure'   => '⚡',
        'family'      => '👨‍👩‍👧',
        'religious'   => '⛩️',
        'nature'      => '🌿',
        'shopping'    => '🛍️',
        'waterfall'   => '🌊',
        'camping'     => '⛺',
        'snorkeling'  => '🤿',
        'cycling'     => '🚲',
    ];
    return $map[strtolower($slug)] ?? '🏷️';
}

// 3. URL Builder Helper for Filters & Pagination
function buildFilterUrl(array $overrides = []): string {
    $current = $_GET;
    unset($current['page']); // Clean router param if present
    
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '' || $v === []) {
            unset($current[$k]);
        } else {
            $current[$k] = $v;
        }
    }
    
    $query = http_build_query($current);
    return '/destinations' . ($query !== '' ? '?' . $query : '');
}

// 4. Load Models & Fetch Data
try {
    $pdo = null;
    if (file_exists(__DIR__ . '/../cms/config/Database.php')) {
        require_once __DIR__ . '/../cms/config/Database.php';
        $pdo = Database::getConnection();
    }

    if ($pdo) {
        // Load Areas with Destination Count
        $areaSql = "SELECT da.id, da.name, da.slug, COUNT(dam.destination_id) AS dest_count
                    FROM destination_areas da
                    LEFT JOIN destination_area_map dam ON dam.area_id = da.id
                    GROUP BY da.id
                    ORDER BY da.name ASC";
        $allAreas = $pdo->query($areaSql)->fetchAll();

        // Load Activity Tags with Destination Count
        $tagSql = "SELECT dt.id, dt.name, dt.slug, dt.icon, COUNT(dtm.destination_id) AS dest_count
                   FROM destination_tags dt
                   LEFT JOIN destination_tag_map dtm ON dtm.tag_id = dt.id
                   GROUP BY dt.id
                   ORDER BY dt.name ASC";
        $allTags = $pdo->query($tagSql)->fetchAll();

        // Load Destinations via Model
        if (file_exists(__DIR__ . '/../cms/models/Destination.php')) {
            require_once __DIR__ . '/../cms/models/Destination.php';
            $destinationModel = new Destination();

            $filters = [];
            if ($keyword !== '') {
                $filters['keyword'] = $keyword;
            }
            if ($selectedArea !== '') {
                $filters['area'] = $selectedArea;
            }
            if (!empty($selectedTags)) {
                $filters['tag'] = $selectedTags;
            }

            $searchResult      = $destinationModel->search($filters, $currentPage, $perPage);
            $destinations      = $searchResult['data'] ?? [];
            $totalDestinations = $searchResult['total'] ?? 0;
            $totalPages        = $searchResult['last_page'] ?? 1;

            // Batch fetch Areas & Tags for the current results
            if (!empty($destinations)) {
                $destIds = array_column($destinations, 'id');
                $idList  = implode(',', array_map('intval', $destIds));

                $areaRows = $pdo->query(
                    "SELECT dam.destination_id, da.name, da.slug
                     FROM destination_area_map dam
                     JOIN destination_areas da ON da.id = dam.area_id
                     WHERE dam.destination_id IN ({$idList})
                     ORDER BY da.name ASC"
                )->fetchAll();

                $tagRows = $pdo->query(
                    "SELECT dtm.destination_id, dt.name, dt.slug, dt.icon
                     FROM destination_tag_map dtm
                     JOIN destination_tags dt ON dt.id = dtm.tag_id
                     WHERE dtm.destination_id IN ({$idList})
                     ORDER BY dt.name ASC"
                )->fetchAll();

                foreach ($areaRows as $ar) {
                    $destMeta[$ar['destination_id']]['areas'][] = $ar;
                }
                foreach ($tagRows as $tr) {
                    $destMeta[$tr['destination_id']]['tags'][] = $tr;
                }

                // Fetch Blog Suggestions related to the displayed destinations
                $firstDestId = (int) $destinations[0]['id'];
                $suggestedPosts = $destinationModel->getBlogSuggestions($firstDestId, 4);
            }
        }

        // Fallback for Blog Suggestions if none found
        if (empty($suggestedPosts) && file_exists(__DIR__ . '/../cms/models/BlogPost.php')) {
            require_once __DIR__ . '/../cms/models/BlogPost.php';
            $blogModel = new BlogPost();
            $suggestedPosts = $blogModel->getPublished(4);
        }
    }
} catch (Throwable $e) {
    // Fail-safe graceful fallback if database or tables are not initialized
    $destinations      = [];
    $allAreas          = [];
    $allTags           = [];
    $suggestedPosts    = [];
    $totalDestinations = 0;
}

// 5. Fallback Dummy Data if Database is Empty
if (empty($destinations) && empty($keyword) && empty($selectedArea) && empty($selectedTags)) {
    $destinations = [
        [
            'id'              => 1,
            'name'            => 'Mount Bromo & Tengger Caldera',
            'slug'            => 'mount-bromo',
            'description'     => 'Witness the otherworldly sunrise over Mount Bromo with sweeping views across the vast sea of sand and active volcanic crater.',
            'image_thumbnail' => 'assets/images/bromo.jpg',
            'location_map'    => 'https://maps.google.com/?q=Mount+Bromo',
            'tour_count'      => 6,
        ],
        [
            'id'              => 2,
            'name'            => 'Kawah Ijen Volcano & Blue Fire',
            'slug'            => 'kawah-ijen',
            'description'     => 'Hike under the stars to discover the rare electric-blue flames and magnificent turquoise acidic crater lake of Mount Ijen.',
            'image_thumbnail' => 'assets/images/ijen.jpg',
            'location_map'    => 'https://maps.google.com/?q=Kawah+Ijen',
            'tour_count'      => 4,
        ],
        [
            'id'              => 3,
            'name'            => 'Yogyakarta & Ancient Temples',
            'slug'            => 'yogyakarta-cultural-heart',
            'description'     => 'Immerse in Javanese royalty, vibrant artisan batik streets, and the UNESCO wonders of Borobudur and Prambanan temples.',
            'image_thumbnail' => 'assets/images/temple.jpg',
            'location_map'    => 'https://maps.google.com/?q=Borobudur',
            'tour_count'      => 5,
        ],
        [
            'id'              => 4,
            'name'            => 'Tumpak Sewu & Coban Sewu Waterfalls',
            'slug'            => 'tumpak-sewu-waterfall',
            'description'     => 'Experience Java’s Niagara — a breathtaking thousand-stream curtain waterfall cascading into a lush tropical canyon.',
            'image_thumbnail' => 'assets/images/waterfall.jpg',
            'location_map'    => 'https://maps.google.com/?q=Tumpak+Sewu',
            'tour_count'      => 3,
        ],
        [
            'id'              => 5,
            'name'            => 'Bandung Tea Plantations & Highland',
            'slug'            => 'bandung-highlands',
            'description'     => 'Cool misty mountain air, rolling green tea estates, volcanic springs, and famous Sundanese culinary delights.',
            'image_thumbnail' => 'assets/images/hills.jpg',
            'location_map'    => 'https://maps.google.com/?q=Lembang+Bandung',
            'tour_count'      => 4,
        ],
        [
            'id'              => 6,
            'name'            => 'Karimunjawa Marine National Park',
            'slug'            => 'karimunjawa-islands',
            'description'     => 'Crystal clear waters, pristine white sandy beaches, vibrant coral reefs, and tranquil tropical island life.',
            'image_thumbnail' => 'assets/images/hero.jpg',
            'location_map'    => 'https://maps.google.com/?q=Karimunjawa',
            'tour_count'      => 2,
        ],
    ];
    $totalDestinations = count($destinations);

    $destMeta = [
        1 => ['areas' => [['name' => 'Bromo', 'slug' => 'bromo']], 'tags' => [['name' => 'Hiking', 'slug' => 'hiking'], ['name' => 'Photography', 'slug' => 'photography']]],
        2 => ['areas' => [['name' => 'Banyuwangi', 'slug' => 'banyuwangi']], 'tags' => [['name' => 'Adventure', 'slug' => 'adventure'], ['name' => 'Hiking', 'slug' => 'hiking']]],
        3 => ['areas' => [['name' => 'Yogyakarta', 'slug' => 'yogyakarta']], 'tags' => [['name' => 'Culture', 'slug' => 'culture'], ['name' => 'Culinary', 'slug' => 'culinary']]],
        4 => ['areas' => [['name' => 'Malang', 'slug' => 'malang']], 'tags' => [['name' => 'Waterfall', 'slug' => 'waterfall'], ['name' => 'Nature', 'slug' => 'nature']]],
        5 => ['areas' => [['name' => 'Bandung', 'slug' => 'bandung']], 'tags' => [['name' => 'Nature', 'slug' => 'nature'], ['name' => 'Culinary', 'slug' => 'culinary']]],
        6 => ['areas' => [['name' => 'Jepara', 'slug' => 'jepara']], 'tags' => [['name' => 'Beach', 'slug' => 'beach'], ['name' => 'Snorkeling', 'slug' => 'snorkeling']]],
    ];
}

if (empty($allAreas)) {
    $allAreas = [
        ['name' => 'Banyuwangi',  'slug' => 'banyuwangi',  'dest_count' => 3],
        ['name' => 'Bromo',       'slug' => 'bromo',       'dest_count' => 4],
        ['name' => 'Malang',      'slug' => 'malang',      'dest_count' => 5],
        ['name' => 'Surabaya',    'slug' => 'surabaya',    'dest_count' => 2],
        ['name' => 'Yogyakarta',  'slug' => 'yogyakarta',  'dest_count' => 6],
    ];
}

if (empty($allTags)) {
    $allTags = [
        ['name' => 'Hiking',      'slug' => 'hiking',      'dest_count' => 5],
        ['name' => 'Photography', 'slug' => 'photography', 'dest_count' => 6],
        ['name' => 'Culture',     'slug' => 'culture',     'dest_count' => 4],
        ['name' => 'Culinary',    'slug' => 'culinary',    'dest_count' => 4],
        ['name' => 'Waterfall',   'slug' => 'waterfall',   'dest_count' => 3],
        ['name' => 'Nature',      'slug' => 'nature',      'dest_count' => 7],
        ['name' => 'Beach',       'slug' => 'beach',       'dest_count' => 3],
        ['name' => 'Adventure',   'slug' => 'adventure',   'dest_count' => 4],
    ];
}

if (empty($suggestedPosts)) {
    $suggestedPosts = [
        [
            'title'          => 'Best Time to Visit Mount Bromo and What to Expect',
            'slug'           => 'best-time-to-visit-mount-bromo-and-what-to-expect',
            'featured_image' => 'assets/images/bromo.jpg',
            'published_at'   => '2024-05-10',
            'category_name'  => 'Tips',
            'reading_time'   => 5,
        ],
        [
            'title'          => 'Complete Travel Guide to Yogyakarta: Temples, Food & Culture',
            'slug'           => 'complete-travel-guide-to-yogyakarta',
            'featured_image' => 'assets/images/temple.jpg',
            'published_at'   => '2024-05-05',
            'category_name'  => 'Guide',
            'reading_time'   => 7,
        ],
        [
            'title'          => 'Hidden Waterfalls in East Java You Must Visit Once in Your Life',
            'slug'           => 'hidden-waterfalls-in-java-you-must-visit',
            'featured_image' => 'assets/images/waterfall.jpg',
            'published_at'   => '2024-04-28',
            'category_name'  => 'Nature',
            'reading_time'   => 4,
        ],
        [
            'title'          => 'Exploring the Misty Tea Plantations of Java',
            'slug'           => 'exploring-the-tea-plantations-of-west-java',
            'featured_image' => 'assets/images/hills.jpg',
            'published_at'   => '2024-04-20',
            'category_name'  => 'Culture',
            'reading_time'   => 6,
        ],
    ];
}

// Current active area object
$activeAreaObj = null;
if ($selectedArea !== '') {
    foreach ($allAreas as $a) {
        if ($a['slug'] === $selectedArea) {
            $activeAreaObj = $a;
            break;
        }
    }
}
?>

<link rel="stylesheet" href="assets/css/destinations.css">

<!-- ================= HERO ================= -->
<section class="dest-hero">
  <div class="dest-hero-overlay"></div>
  <div class="container dest-hero-content">
    <p class="eyebrow" style="color:var(--orange);">Discover Beautiful Java</p>
    <h1>Destinations in <span class="accent" style="color:#6ee7b7;">Java</span></h1>
    <p class="lead">
      Explore the enchanting diversity of Java Island. From sunrise volcanic summits and hidden waterfall canyons to heritage royal cultures and rich culinary scenes.
    </p>
    <div class="dest-stats-strip">
      <span><strong><?= count($allAreas) ?></strong> Regions &amp; Areas</span>
      <span>•</span>
      <span><strong><?= count($allTags) ?></strong> Activity Types</span>
      <span>•</span>
      <span><strong><?= $totalDestinations ?></strong> Destinations Listed</span>
    </div>
  </div>
</section>

<!-- ================= QUICK FILTER PILLS (Activity Tags) ================= -->
<div class="quick-tags-bar">
  <div class="quick-tags-inner">
    <span class="quick-tags-label">Activities:</span>
    <a href="<?= htmlspecialchars(buildFilterUrl(['tag' => null, 'tags' => null, 'p' => null])) ?>" 
       class="quick-pill <?= empty($selectedTags) ? 'active' : '' ?>">
       ✨ All Activities
    </a>
    <?php foreach ($allTags as $tag): ?>
      <?php 
        $isActive = in_array($tag['slug'], $selectedTags, true);
        $icon     = getTagIcon($tag['slug'], $tag['icon'] ?? null);
        $url      = buildFilterUrl(['tag' => $isActive ? null : $tag['slug'], 'tags' => null, 'p' => null]);
      ?>
      <a href="<?= htmlspecialchars($url) ?>" class="quick-pill <?= $isActive ? 'active' : '' ?>">
        <span><?= $icon ?></span>
        <span><?= htmlspecialchars($tag['name']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- ================= MAIN CONTENT ================= -->
<section class="destinations-section" id="destinationsList">
  <div class="container">
    <div class="main-wrapper">

      <!-- ── SIDEBAR FILTER ── -->
      <aside class="filter-section">
        <form method="GET" action="/destinations" id="destFilterForm">
          <div class="filter-header">
            <h3>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
              </svg>
              Filter Destinations
            </h3>
            <?php if (!empty($keyword) || !empty($selectedArea) || !empty($selectedTags)): ?>
              <a href="/destinations" class="btn-reset-text">Reset</a>
            <?php endif; ?>
          </div>

          <!-- Keyword Search in Sidebar -->
          <div class="filter-block">
            <div class="filter-search-box">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" name="keyword" placeholder="Search name or keywords..." value="<?= htmlspecialchars($keyword) ?>">
            </div>
          </div>

          <!-- Region / Area Filter -->
          <div class="filter-block">
            <h4 class="filter-block-title">
              <span>Region / Area</span>
              <small class="text-muted"><?= count($allAreas) ?></small>
            </h4>
            <div class="filter-list">
              <label class="filter-option">
                <span>
                  <input type="radio" name="area" value="" <?= empty($selectedArea) ? 'checked' : '' ?> onchange="this.form.submit()">
                  All Regions
                </span>
              </label>
              <?php foreach ($allAreas as $area): ?>
                <label class="filter-option">
                  <span>
                    <input type="radio" name="area" value="<?= htmlspecialchars($area['slug']) ?>" 
                           <?= $selectedArea === $area['slug'] ? 'checked' : '' ?> onchange="this.form.submit()">
                    <?= htmlspecialchars($area['name']) ?>
                  </span>
                  <?php if (!empty($area['dest_count'])): ?>
                    <span class="filter-count"><?= (int) $area['dest_count'] ?></span>
                  <?php endif; ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Activity Tags Filter (Checkboxes) -->
          <div class="filter-block">
            <h4 class="filter-block-title">
              <span>Activity &amp; Vibe</span>
              <small class="text-muted"><?= count($allTags) ?></small>
            </h4>
            <div class="filter-list">
              <?php foreach ($allTags as $tag): ?>
                <?php $checked = in_array($tag['slug'], $selectedTags, true); ?>
                <label class="filter-option">
                  <span>
                    <input type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag['slug']) ?>" 
                           <?= $checked ? 'checked' : '' ?>>
                    <?= getTagIcon($tag['slug']) ?> <?= htmlspecialchars($tag['name']) ?>
                  </span>
                  <?php if (!empty($tag['dest_count'])): ?>
                    <span class="filter-count"><?= (int) $tag['dest_count'] ?></span>
                  <?php endif; ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-sm btn-filter-submit">
            Apply Filters
          </button>
        </form>

        <!-- CTA Box inside Sidebar -->
        <div class="sidebar-cta-box">
          <h4>Need a Custom Trip?</h4>
          <p>Tell us your preferred activities and dates. Our Java travel designers will craft a private itinerary for you.</p>
          <a href="/packages" class="btn btn-primary btn-sm" style="width:100%;font-size:12px;">Plan Your Custom Tour</a>
        </div>
      </aside>

      <!-- ── DESTINATIONS CONTENT ── -->
      <div class="destinations-content">

        <!-- Top Results & Active Filter Tags Bar -->
        <div class="results-bar">
          <div class="results-count">
            Showing <strong><?= count($destinations) ?></strong> of <strong><?= $totalDestinations ?></strong> destinations
            <?php if ($activeAreaObj): ?>
              <span>in <strong><?= htmlspecialchars($activeAreaObj['name']) ?></strong></span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Active Filter Chips Bar -->
        <?php if (!empty($keyword) || !empty($selectedArea) || !empty($selectedTags)): ?>
          <div class="active-filters">
            <span style="font-size:12px;color:var(--muted);font-weight:600;">Active Filters:</span>
            
            <?php if (!empty($keyword)): ?>
              <a href="<?= htmlspecialchars(buildFilterUrl(['keyword' => null, 'p' => null])) ?>" class="active-badge" title="Remove keyword">
                Keyword: "<?= htmlspecialchars($keyword) ?>"
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            <?php endif; ?>

            <?php if (!empty($selectedArea)): ?>
              <a href="<?= htmlspecialchars(buildFilterUrl(['area' => null, 'p' => null])) ?>" class="active-badge" title="Remove area">
                Area: <?= htmlspecialchars($activeAreaObj['name'] ?? $selectedArea) ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            <?php endif; ?>

            <?php foreach ($selectedTags as $st): ?>
              <?php 
                $remTags = array_values(array_filter($selectedTags, fn($t) => $t !== $st));
                $tagObjName = $st;
                foreach ($allTags as $t) {
                    if ($t['slug'] === $st) { $tagObjName = $t['name']; break; }
                }
              ?>
              <a href="<?= htmlspecialchars(buildFilterUrl(['tags' => $remTags, 'tag' => null, 'p' => null])) ?>" class="active-badge" title="Remove tag">
                <?= getTagIcon($st) ?> <?= htmlspecialchars($tagObjName) ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            <?php endforeach; ?>

            <a href="/destinations" class="active-clear-all">Clear All</a>
          </div>
        <?php endif; ?>

        <!-- Cards Grid -->
        <?php if (!empty($destinations)): ?>
          <div class="destinations-grid">
            <?php foreach ($destinations as $dest): ?>
              <?php
                $did       = (int) $dest['id'];
                $meta      = $destMeta[$did] ?? ['areas' => [], 'tags' => []];
                $destAreas = $meta['areas'] ?? [];
                $destTags  = $meta['tags'] ?? [];

                // Image normalization
                $img = !empty($dest['image_thumbnail']) ? $dest['image_thumbnail'] : 'assets/images/bromo.jpg';
                if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, 'assets/') && !str_starts_with($img, '/')) {
                    $img = 'assets/images/' . ltrim($img, '/');
                }
              ?>
              <article class="dest-card-item">
                <div class="dest-thumb-wrap">
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($dest['name']) ?>" class="dest-thumb-img" loading="lazy">
                  
                  <?php if (!empty($destAreas)): ?>
                    <span class="dest-area-badge-top">
                      📍 <?= htmlspecialchars($destAreas[0]['name']) ?>
                    </span>
                  <?php endif; ?>

                  <?php if (!empty($dest['location_map'])): ?>
                    <a href="<?= htmlspecialchars($dest['location_map']) ?>" target="_blank" rel="noopener noreferrer" class="dest-map-link-top" title="View location on map">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                        <line x1="8" y1="2" x2="8" y2="18"/>
                        <line x1="16" y1="6" x2="16" y2="22"/>
                      </svg>
                    </a>
                  <?php endif; ?>
                </div>

                <div class="dest-card-body">
                  <?php if (!empty($destTags)): ?>
                    <div class="dest-tags-strip">
                      <?php foreach (array_slice($destTags, 0, 3) as $tag): ?>
                        <a href="<?= htmlspecialchars(buildFilterUrl(['tag' => $tag['slug'], 'tags' => null, 'p' => null])) ?>" class="dest-tag-chip">
                          <?= getTagIcon($tag['slug'], $tag['icon'] ?? null) ?>
                          <?= htmlspecialchars($tag['name']) ?>
                        </a>
                      <?php endforeach; ?>
                      <?php if (count($destTags) > 3): ?>
                        <span class="dest-tag-chip" style="background:#f9f9f9;color:var(--muted);">+<?= count($destTags) - 3 ?></span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <h3 class="dest-title"><?= htmlspecialchars($dest['name']) ?></h3>
                  
                  <?php if (!empty($dest['description'])): ?>
                    <p class="dest-desc"><?= htmlspecialchars($dest['description']) ?></p>
                  <?php endif; ?>

                  <div class="dest-card-footer">
                    <span class="dest-tours-count">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                      <?= (int) ($dest['tour_count'] ?? 0) ?> Tour<?= ((int) ($dest['tour_count'] ?? 0)) !== 1 ? 's' : '' ?> Available
                    </span>

                    <a href="/packages?keyword=<?= urlencode($dest['name']) ?>" class="btn-dest-explore">
                      Explore Tours &rarr;
                    </a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="pagination">
              <a href="<?= htmlspecialchars(buildFilterUrl(['p' => max(1, $currentPage - 1)])) ?>" 
                 class="pagination-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= htmlspecialchars(buildFilterUrl(['p' => $i])) ?>" 
                   class="pagination-item <?= $i === $currentPage ? 'active' : '' ?>">
                  <?= $i ?>
                </a>
              <?php endfor; ?>

              <a href="<?= htmlspecialchars(buildFilterUrl(['p' => min($totalPages, $currentPage + 1)])) ?>" 
                 class="pagination-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">Next &raquo;</a>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <!-- Empty State -->
          <div class="dest-empty-state">
            <div class="dest-empty-icon">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <h3>No destinations found</h3>
            <p>We couldn't find any destinations matching your current filters. Try searching with different keywords or reset your filters.</p>
            <a href="/destinations" class="btn btn-primary btn-sm">Clear All Filters</a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>

<!-- ================= RELATED BLOG SUGGESTIONS ================= -->
<?php if (!empty($suggestedPosts)): ?>
<section class="dest-blog-section">
  <div class="container">
    <div class="section-head reveal visible" style="margin-bottom:36px;">
      <p class="eyebrow" style="color:var(--green-700);">Traveler Inspiration &amp; Insights</p>
      <h2 class="section-title">Related Stories &amp; Destination Guides</h2>
      <p class="section-sub">Helpful advice, sunrise photography guides, and authentic cultural tips curated for these destinations.</p>
    </div>

    <div class="blog-grid">
      <?php foreach ($suggestedPosts as $post): ?>
        <?php
          $pImg = !empty($post['featured_image']) ? $post['featured_image'] : 'assets/images/bromo.jpg';
          if (!str_starts_with($pImg, 'http://') && !str_starts_with($pImg, 'https://') && !str_starts_with($pImg, 'assets/') && !str_starts_with($pImg, '/')) {
              $pImg = 'assets/images/' . ltrim($pImg, '/');
          }
        ?>
        <article class="blog-card">
          <div class="blog-media" style="background-image:url('<?= htmlspecialchars($pImg) ?>')"></div>
          <div class="blog-body">
            <h3>
              <a href="/blog-detail?slug=<?= htmlspecialchars($post['slug'] ?? '') ?>" style="color:inherit;text-decoration:none;">
                <?= htmlspecialchars($post['title']) ?>
              </a>
            </h3>
            <p class="blog-meta">
              <?= !empty($post['published_at']) ? date('M j, Y', strtotime($post['published_at'])) : date('M j, Y') ?>
              <span class="dot"></span>
              <?= htmlspecialchars($post['category_name'] ?? 'Tips') ?>
              <?php if (!empty($post['reading_time'])): ?>
                <span class="dot"></span>
                <span>⏱️ <?= (int) $post['reading_time'] ?> min</span>
              <?php endif; ?>
            </p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= WHY US ================= -->
<section class="why" id="why">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:32px;">
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4.5 5v6c0 5 3.2 8.7 7.5 10 4.3-1.3 7.5-5 7.5-10V5Z"/><path d="m9 11.5 2.2 2.2 4.3-4.2"/></svg>
        <div><h4>Local &amp; Trusted</h4><p>We are based in Java and know its mountains, culture, and trails best.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M18.5 15l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8Z"/></svg>
        <div><h4>Handpicked Experiences</h4><p>Carefully selected destinations and tours you'll truly fall in love with.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M14.8 9.2c-.6-.9-1.6-1.4-2.8-1.4-1.7 0-3 .9-3 2.4 0 3 6 1.8 6 4.6 0 1.5-1.3 2.4-3 2.4-1.2 0-2.2-.5-2.8-1.4"/></svg>
        <div><h4>Best Price Guarantee</h4><p>Quality trips and transparent pricing without hidden tourist fees.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z" /><circle cx="16" cy="16" r="4.5" /><path d="M14.5 16h1.5v1.5" /><path d="M17.5 14.5l-1.5 1.5" /></svg>
        <div><h4>Flexible Itineraries</h4><p>Easily customize your route and pace to fit your travel schedule.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="6" rx="2"/><rect x="17" y="13" width="4" height="6" rx="2"/></svg>
        <div><h4>24/7 Local Support</h4><p>Our concierge and travel support are available around the clock.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <div><h4>Sustainable Tourism</h4><p>Travel responsibly and support local village guides and conservation.</p></div>
      </div>
    </div>
  </div>
</section>
