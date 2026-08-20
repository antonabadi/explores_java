<link rel="stylesheet" href="assets/css/destinations.css">

<!-- ================= HERO ================= -->
<section class="hero" style="display:block; text-align:start;">
  <div class="container hero-content">
    <h1>Destinations in <span class="accent">Java</span></h1>
    <p class="lead">Explore the diverse beauty of Java Island. From majestic mountains to cultural wonders, we craft unforgettable journeys across Java.</p>
  </div>
</section>

<!-- ================= SEARCH BAR ================= -->
<div class="search-wrap">
  <form class="search-bar" id="searchForm">
    <div class="search-field grow">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
      <div class="sf"><label for="dest">Where to?</label><input id="dest" type="text" placeholder="Search destination"></div>
    </div>
    <div class="search-field">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
      <div class="sf"><label for="cin">Check In</label><input id="cin" type="date"></div>
    </div>
    <div class="search-field">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
      <div class="sf"><label for="cout">Check Out</label><input id="cout" type="date"></div>
    </div>
    <div class="search-field">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
      <div class="sf"><label for="pax">Travelers</label>
        <select id="pax"><option>1 Traveler</option><option selected>2 Travelers</option><option>3 Travelers</option><option>4+ Travelers</option></select>
      </div>
    </div>
    <button class="btn btn-primary" type="submit">Search Now</button>
  </form>
</div>

<!-- Main Content -->
<section class="destinations" id="destinations">
  <div class="container">
<div class="main-wrapper">
    <aside class="filter-section">
        <h3>Filter Destinations</h3>
        <div class="filter-group">
            <label><input type="checkbox" checked> All Regions</label>
            <label><input type="checkbox"> East Java</label>
            <label><input type="checkbox"> Central Java</label>
        </div>
        <div class="cta-box">
            <p>Not sure where to go?</p>
            <button>Plan Your Trip</button>
        </div>
    </aside>

    <section class="destinations-grid">
        <!-- Card 1 -->
        <div class="card">
            <img src="https://via.placeholder.com/300x200" alt="Mount Bromo">
            <div class="card-body">
                <small>East Java</small>
                <h4>Mount Bromo</h4>
                <p>Witness the breathtaking sunrise...</p>
                <a href="#">Explore Now &rarr;</a>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="card">
            <img src="https://via.placeholder.com/300x200" alt="Kawah Ijen">
            <div class="card-body">
                <small>East Java</small>
                <h4>Kawah Ijen</h4>
                <p>Amazing blue fire and turquoise crater...</p>
                <a href="#">Explore Now &rarr;</a>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="card">
            <img src="https://via.placeholder.com/300x200" alt="Yogyakarta">
            <div class="card-body">
                <small>Yogyakarta</small>
                <h4>Yogyakarta</h4>
                <p>The heart of Javanese culture...</p>
                <a href="#">Explore Now &rarr;</a>
            </div>
        </div>
        <!-- Card 4, 5, 6 sama caranya -->
    </section>
</div>
</div>
</section>

<!-- ================= WHY US ================= -->
<section class="why" id="why">
  <div class="container">
    <div class="reveal" style="display:grid;grid-template-columns:repeat(6, 1fr);gap:32px;">
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z" /><circle cx="16" cy="16" r="4.5" /><path d="M14.5 16h1.5v1.5" /><path d="M17.5 14.5l-1.5 1.5" /></svg>
        <div><h4>Flexible Itineraries</h4><p>Customize your trip to fit your schedule.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="6" rx="2"/><rect x="17" y="13" width="4" height="6" rx="2"/></svg>
        <div><h4>24/7 Support</h4><p>We're here to help anytime you need.</p></div>
      </div>
      <div class="feature">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M 50 70
                       C 50 60, 48 50, 40 45
                       C 30 38, 25 45, 30 55
                       C 35 63, 45 68, 50 70 Z
                       M 50 65
                       C 52 50, 60 35, 75 30
                       C 85 25, 82 40, 72 50
                       C 64 58, 56 62, 50 65 Z
                       M 50 55
                       C 52 45, 48 38, 52 30
                       C 55 22, 65 24, 62 35
                       C 60 42, 55 50, 50 55 Z" />
            <path d="M 48 70
                       C 40 68, 25 65, 20 73
                       C 15 80, 28 83, 38 81
                       C 44 80, 47 75, 48 70 Z" />
            <path d="M 52 70
                       C 60 68, 75 65, 80 73
                       C 85 80, 72 83, 62 81
                       C 56 80, 53 75, 52 70 Z" />
        </svg>
        <div><h4>Sustainable Tourism</h4><p>Travel responsibly, support local communities.</p></div>
      </div>
    </div>
  </div>
</section>
