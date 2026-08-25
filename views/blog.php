
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
