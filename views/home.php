
<?php
// Load models dynamically if available
$destinations = [];
$tours = [];

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
} catch (Throwable $e) {
    // Fail-safe graceful fallback if database is not initialized
    $destinations = [];
    $tours = [];
}
?>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="container hero-content">
    <p class="eyebrow">Explore more. Experience Java.</p>
    <h1>Discover the Beauty<br>of <span class="accent">Java Island</span></h1>
    <p class="lead">From majestic mountains to cultural wonders, we craft unforgettable journeys across Java.</p>
    <div class="hero-btns">
      <a href="?page=destinations" class="btn btn-primary">Explore Destinations</a>
      <button class="btn btn-light" id="watchVideo">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M10 9l5 3-5 3z" fill="currentColor" stroke="none"/></svg>
        Watch Video
      </button>
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

<!-- ================= DESTINATIONS ================= -->
<section class="section" id="destinations">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">Popular Destinations</p>
      <h2 class="section-title">Explore the <span class="accent">Best of Java</span></h2>
      <p class="section-sub">Natural beauty, rich culture, and warm local hospitality await you in every corner of Java.</p>
    </div>
    <div class="carousel reveal">
      <button class="car-btn prev" id="carPrev" aria-label="Geser kiri"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m14 6-6 6 6 6"/></svg></button>
      <div class="cards-track" id="destTrack">
        <?php if (!empty($destinations)): ?>
          <?php foreach ($destinations as $dest): ?>
            <?php 
              $bgImage = !empty($dest['image_thumbnail']) ? $dest['image_thumbnail'] : 'assets/images/bromo.jpg';
              if (!str_starts_with($bgImage, 'http') && !str_starts_with($bgImage, 'assets/')) {
                  $bgImage = 'assets/images/' . ltrim($bgImage, '/');
              }
            ?>
            <article class="dest-card" style="background-image:url('<?= htmlspecialchars($bgImage) ?>')">
              <div class="shade"></div>
              <div class="dest-info">
                <h3><?= htmlspecialchars($dest['name']) ?></h3>
                <p><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>Java</p>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <article class="dest-card" style="background-image:url('assets/images/bromo.jpg')">
            <div class="shade"></div>
            <div class="dest-info"><h3>Bromo Tengger</h3><p><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>East Java</p></div>
          </article>
          <article class="dest-card" style="background-image:url('assets/images/temple.jpg')">
            <div class="shade"></div>
            <div class="dest-info"><h3>Yogyakarta</h3><p><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>Central Java</p></div>
          </article>
          <article class="dest-card" style="background-image:url('assets/images/hills.jpg')">
            <div class="shade"></div>
            <div class="dest-info"><h3>Bandung</h3><p><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>West Java</p></div>
          </article>
          <article class="dest-card" style="background-image:url('assets/images/ijen.jpg')">
            <div class="shade"></div>
            <div class="dest-info"><h3>Kawah Ijen</h3><p><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>East Java</p></div>
          </article>
        <?php endif; ?>
      </div>
      <button class="car-btn next" id="carNext" aria-label="Geser kanan"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10 6 6 6-6 6"/></svg></button>
    </div>
  </div>
</section>

<!-- ================= WHY US ================= -->
<section class="why" id="why">
  <div class="container why-grid">
    <div class="why-text reveal">
      <p class="eyebrow">Why travel with us?</p>
      <h2>Local Expertise,<br>Unforgettable Journeys</h2>
      <p>We are a local team passionate about showcasing the real Java through authentic experiences and responsible travel.</p>
      <a class="btn btn-primary" href="#cta">Learn More About Us</a>
    </div>
    <div class="why-features reveal">
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4.5 5v6c0 5 3.2 8.7 7.5 10 4.3-1.3 7.5-5 7.5-10V5Z"/><path d="m9 11.5 2.2 2.2 4.3-4.2"/></svg>
        <div><h4>Local &amp; Trusted</h4><p>We are based in Java and know it best.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M18.5 15l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8Z"/></svg>
        <div><h4>Handpicked Experiences</h4><p>Carefully selected tours you'll love.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M14.8 9.2c-.6-.9-1.6-1.4-2.8-1.4-1.7 0-3 .9-3 2.4 0 3 6 1.8 6 4.6 0 1.5-1.3 2.4-3 2.4-1.2 0-2.2-.5-2.8-1.4"/></svg>
        <div><h4>Best Price Guarantee</h4><p>Quality trips at competitive prices.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="6" rx="2"/><rect x="17" y="13" width="4" height="6" rx="2"/></svg>
        <div><h4>24/7 Support</h4><p>We're here to help anytime you need.</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ================= TOUR PACKAGES ================= -->
<section class="section" id="packages">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">Featured Tours</p>
      <h2 class="section-title">Handpicked <span class="accent">Tour Packages</span></h2>
      <p class="section-sub">From adventure to cultural immersion, find the perfect package that suits your travel style.</p>
    </div>
    <div class="tours-grid">
      <?php if (!empty($tours)): ?>
        <?php foreach ($tours as $tour): ?>
          <?php
            $duration = ($tour['duration_days'] ?? 0) . 'D ' . ($tour['duration_nights'] ?? 0) . 'N';
            $priceFormatted = number_format((float)($tour['price'] ?? 0), 0);
          ?>
          <article class="tour-card reveal">
            <div class="tour-media" style="background-image:url('assets/images/bromo.jpg')"><span class="badge"><?= htmlspecialchars($duration) ?></span></div>
            <div class="tour-body">
              <h3 class="tour-title"><?= htmlspecialchars($tour['title']) ?></h3>
              <div class="price-row">
                <p class="price">from <b>$<?= htmlspecialchars($priceFormatted) ?></b> /person</p>
                <p class="rating"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9Z"/></svg>4.8</p>
              </div>
              <p class="tour-places"><?= htmlspecialchars($tour['destination_name'] ?? 'Java') ?></p>
              <a href="?page=detail&slug=<?= urlencode($tour['slug'] ?? '') ?>" class="tour-link">View Details</a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <article class="tour-card reveal">
          <div class="tour-media" style="background-image:url('assets/images/bromo.jpg')"><span class="badge">3D 2N</span></div>
          <div class="tour-body">
            <h3 class="tour-title">Bromo Sunrise Tour</h3>
            <div class="price-row">
              <p class="price">from <b>$125</b> /person</p>
              <p class="rating"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9Z"/></svg>4.8 <small>(120)</small></p>
            </div>
            <p class="tour-places">Mount Bromo, Madakaripura Waterfall</p>
            <a href="?page=detail" class="tour-link">View Details</a>
          </div>
        </article>
        <article class="tour-card reveal">
          <div class="tour-media" style="background-image:url('assets/images/temple.jpg')"><span class="badge">4D 3N</span></div>
          <div class="tour-body">
            <h3 class="tour-title">Jogja Cultural Escape</h3>
            <div class="price-row">
              <p class="price">from <b>$199</b> /person</p>
              <p class="rating"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9Z"/></svg>4.9 <small>(86)</small></p>
            </div>
            <p class="tour-places">Borobudur, Prambanan, Malioboro</p>
            <a href="?page=detail" class="tour-link">View Details</a>
          </div>
        </article>
        <article class="tour-card reveal">
          <div class="tour-media" style="background-image:url('assets/images/waterfall.jpg')"><span class="badge">5D 4N</span></div>
          <div class="tour-body">
            <h3 class="tour-title">Java Overland Adventure</h3>
            <div class="price-row">
              <p class="price">from <b>$349</b> /person</p>
              <p class="rating"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9Z"/></svg>4.8 <small>(76)</small></p>
            </div>
            <p class="tour-places">Bromo, Ijen, Baluran, Borobudur</p>
            <a href="?page=detail" class="tour-link">View Details</a>
          </div>
        </article>
        <article class="tour-card reveal">
          <div class="tour-media" style="background-image:url('assets/images/hills.jpg')"><span class="badge">Customize</span></div>
          <div class="tour-body">
            <h3 class="tour-title">Private &amp; Custom Trip</h3>
            <div class="price-row">
              <p class="price">Tailor-made just for you</p>
              <p class="rating"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9Z"/></svg>5.0 <small>(45)</small></p>
            </div>
            <p class="tour-places">Flexible itinerary &amp; destinations</p>
            <a href="?page=detail" class="tour-link">Request a Quote</a>
          </div>
        </article>
      <?php endif; ?>
    </div>
    <div class="center-cta"><a class="btn btn-primary" href="?page=packages">View All Tours</a></div>
  </div>
</section>

<!-- ================= BLOG ================= -->
<section class="section" id="blog" style="padding-top:0">
  <div class="container">
    <div class="blog-head reveal">
      <div>
        <p class="eyebrow">Travel Tips &amp; Stories</p>
        <h2 class="section-title">From Our <span class="accent">Blog</span></h2>
        <p class="section-sub">Inspiration, travel tips, and stories from our adventures across Java.</p>
      </div>
      <a class="btn btn-outline" href="#">Visit Our Blog</a>
    </div>
    <div class="blog-grid">
      <article class="blog-card reveal">
        <div class="blog-media" style="background-image:url('assets/images/bromo.jpg')"></div>
        <div class="blog-body">
          <h3>Best Time to Visit Mount Bromo and What to Expect</h3>
          <p class="blog-meta">May 10, 2024 <span class="dot"></span> Tips</p>
        </div>
      </article>
      <article class="blog-card reveal">
        <div class="blog-media" style="background-image:url('assets/images/temple.jpg')"></div>
        <div class="blog-body">
          <h3>Complete Travel Guide to Yogyakarta</h3>
          <p class="blog-meta">May 5, 2024 <span class="dot"></span> Guide</p>
        </div>
      </article>
      <article class="blog-card reveal">
        <div class="blog-media" style="background-image:url('assets/images/waterfall.jpg')"></div>
        <div class="blog-body">
          <h3>Hidden Waterfalls in Java You Must Visit</h3>
          <p class="blog-meta">Apr 28, 2024 <span class="dot"></span> Nature</p>
        </div>
      </article>
      <article class="blog-card reveal">
        <div class="blog-media" style="background-image:url('assets/images/hills.jpg')"></div>
        <div class="blog-body">
          <h3>Exploring the Tea Plantations of West Java</h3>
          <p class="blog-meta">Apr 20, 2024 <span class="dot"></span> Culture</p>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ================= CTA ================= -->
<section class="cta" id="cta">
  <div class="container cta-inner reveal">
    <span class="cta-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg></span>
    <h2>Ready for Your Next Adventure?</h2>
    <p>Let us help you plan a journey you'll never forget.</p>
    <a class="btn btn-light" href="#contact">Plan Your Trip Now</a>
  </div>
</section>

<script src="assets/js/home.js"></script>

