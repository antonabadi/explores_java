<?php
$metaTitle       = 'About Us — Explores Java';
$metaDescription = 'Explores Java was born from a simple love for travelling, nature, and discovering something new. Discover our story, our journey of reflection, and the community behind Explores Java.';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Caveat:wght@500;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&display=swap');

/* --- Page Theme Variables --- */
:root {
  --aj-bg-cream: #fbf9f4;
  --aj-bg-paper: #f6f3eb;
  --aj-green-dark: #1b3b28;
  --aj-green-main: #275638;
  --aj-green-light: #2d6a4f;
  --aj-text-dark: #2b332c;
  --aj-text-muted: #5c665e;
  --aj-serif: 'Playfair Display', Georgia, serif;
  --aj-handwriting: 'Caveat', cursive, sans-serif;
}

.about-page {
  font-family: inherit;
  color: var(--aj-text-dark);
  background-color: var(--aj-bg-cream);
  line-height: 1.7;
}

.about-page .eyebrow {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--aj-green-main);
  margin-bottom: 10px;
  display: block;
}

.about-page h1, .about-page h2, .about-page h3 {
  font-family: var(--aj-serif);
  font-weight: 600;
  color: var(--aj-text-dark);
}

/* ================= 1. HERO SECTION ================= */
.aj-hero {
  position: relative;
  padding: 140px 0 100px;
  background-color: var(--aj-green-dark);
  background-image: url('assets/images/hills.jpg');
  background-position: center 35%;
  background-size: cover;
  background-repeat: no-repeat;
  color: #ffffff;
}
.aj-hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(17, 41, 27, 0.78) 0%, rgba(27, 59, 40, 0.85) 100%);
}
.aj-hero-content {
  position: relative;
  z-index: 2;
  max-width: 660px;
}
.aj-hero .eyebrow {
  color: rgba(255, 255, 255, 0.75);
}
.aj-hero h1 {
  font-size: clamp(34px, 4.5vw, 54px);
  color: #ffffff;
  line-height: 1.18;
  margin: 12px 0 20px;
}
.aj-hero h1 .accent-text {
  color: #4ade80;
  font-style: italic;
  font-weight: 400;
}
.aj-hero-lead {
  font-size: 15.5px;
  line-height: 1.75;
  color: rgba(255, 255, 255, 0.88);
  font-weight: 300;
  max-width: 580px;
}

/* ================= 2. THE STORY BEHIND EXPLORES JAVA ================= */
.aj-story {
  padding: 100px 0;
  background-color: var(--aj-bg-cream);
}
.aj-story-grid {
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  gap: 70px;
  align-items: center;
}

/* Polaroid Styling */
.aj-polaroid-stack {
  position: relative;
  padding: 20px;
}
.aj-polaroid {
  background: #ffffff;
  padding: 14px 14px 24px;
  box-shadow: 0 14px 35px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0,0,0,0.04);
  border-radius: 2px;
  position: relative;
  transition: transform 0.3s ease;
}
.aj-polaroid-main {
  width: 86%;
  transform: rotate(-3deg);
  z-index: 2;
}
.aj-polaroid-secondary {
  width: 58%;
  position: absolute;
  bottom: 0;
  right: 10px;
  transform: rotate(6deg);
  z-index: 3;
}
.aj-polaroid-img {
  width: 100%;
  height: 320px;
  object-fit: cover;
  display: block;
  border-radius: 2px;
}
.aj-polaroid-secondary .aj-polaroid-img {
  height: 190px;
}
.aj-polaroid-caption {
  font-family: var(--aj-handwriting);
  font-size: 26px;
  color: #4a544d;
  margin-top: 14px;
  text-align: left;
  padding-left: 6px;
  line-height: 1;
}
/* Leaf SVG Decor */
.aj-leaf-decor {
  position: absolute;
  top: -15px;
  right: 15%;
  width: 60px;
  opacity: 0.45;
  pointer-events: none;
  z-index: 1;
}

/* Story Text */
.aj-story-text h2 {
  font-size: clamp(28px, 3.2vw, 40px);
  margin-bottom: 24px;
  line-height: 1.22;
}
.aj-story-text p {
  font-size: 14.5px;
  color: var(--aj-text-muted);
  margin-bottom: 16px;
  line-height: 1.78;
}
.aj-highlight-green {
  color: var(--aj-green-main);
  font-weight: 600;
  font-style: italic;
}
.aj-bold-dark {
  color: var(--aj-text-dark);
  font-weight: 700;
}
.aj-quote-green {
  color: var(--aj-green-main);
  font-weight: 600;
  font-style: italic;
  margin-top: 22px;
}

/* ================= 3. WHY JAVA SECTION ================= */
.aj-why-java {
  position: relative;
  padding: 110px 0;
  background-image: url('assets/images/hero.jpg');
  background-position: center center;
  background-size: cover;
  background-repeat: no-repeat;
  color: var(--aj-text-dark);
}
.aj-why-java-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(248, 246, 240, 0.94) 0%, rgba(248, 246, 240, 0.88) 55%, rgba(248, 246, 240, 0.65) 100%);
}
.aj-why-java-content {
  position: relative;
  z-index: 2;
  max-width: 620px;
}
.aj-why-java h2 {
  font-size: clamp(28px, 3.2vw, 42px);
  margin-bottom: 20px;
  line-height: 1.2;
}
.aj-why-java p {
  font-size: 14.5px;
  color: #3d4740;
  margin-bottom: 16px;
  line-height: 1.78;
}

/* ================= 4. MORE THAN A TOUR ================= */
.aj-more-tour {
  padding: 100px 0;
  background-color: #ffffff;
  text-align: center;
}
.aj-more-tour .section-head {
  max-width: 680px;
  margin: 0 auto 60px;
}
.aj-more-tour h2 {
  font-size: clamp(28px, 3.2vw, 42px);
  margin-bottom: 14px;
}
.aj-more-tour .section-sub {
  font-size: 14.5px;
  color: var(--aj-text-muted);
}

.aj-features-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
  margin-bottom: 40px;
}
.aj-feature-card {
  padding: 30px 16px 24px;
  background: var(--aj-bg-cream);
  border: 1px solid rgba(0,0,0,0.04);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.aj-feature-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}
.aj-feature-icon {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  border: 1.5px solid var(--aj-green-main);
  display: grid;
  place-items: center;
  margin-bottom: 20px;
  color: var(--aj-green-main);
  background: #ffffff;
}
.aj-feature-icon svg {
  width: 24px;
  height: 24px;
}
.aj-feature-card h3 {
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--aj-text-dark);
}
.aj-feature-card p {
  font-size: 13px;
  color: var(--aj-text-muted);
  line-height: 1.55;
}

.aj-more-footer {
  font-size: 14px;
  color: var(--aj-text-muted);
  max-width: 620px;
  margin: 0 auto;
}

/* ================= 5. A JOURNEY OF REFLECTION ================= */
.aj-reflection {
  padding: 100px 0;
  background-color: var(--aj-bg-paper);
}
.aj-reflection-grid {
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  gap: 70px;
  align-items: center;
}

/* ================= 6. THE PEOPLE BEHIND THE JOURNEY ================= */
.aj-community {
  padding: 100px 0;
  background-color: var(--aj-bg-cream);
}
.aj-community-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 36px;
}
.aj-community-card {
  background: #ffffff;
  border: 1px solid rgba(0,0,0,0.05);
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.aj-founder-card {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 24px;
  align-items: start;
}
.aj-founder-avatar {
  width: 140px;
  height: 180px;
  object-fit: cover;
  border-radius: 12px;
}

.aj-community-card h3 {
  font-size: 26px;
  margin-bottom: 16px;
  line-height: 1.25;
}
.aj-community-card p {
  font-size: 14px;
  color: var(--aj-text-muted);
  margin-bottom: 14px;
  line-height: 1.75;
}

/* ================= 7. CTA SECTION ================= */
.aj-cta {
  position: relative;
  padding: 110px 0;
  background-image: url('assets/images/bromo-hero-sunrise.jpg');
  background-position: center center;
  background-size: cover;
  background-repeat: no-repeat;
  color: #ffffff;
  text-align: center;
}
.aj-cta-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(17, 41, 27, 0.82) 0%, rgba(20, 48, 32, 0.90) 100%);
}
.aj-cta-content {
  position: relative;
  z-index: 2;
  max-width: 600px;
  margin: 0 auto;
}
.aj-cta .eyebrow {
  color: rgba(255, 255, 255, 0.75);
}
.aj-cta h2 {
  font-size: clamp(32px, 4vw, 48px);
  color: #ffffff;
  margin: 10px 0 16px;
}
.aj-cta p {
  font-size: 15px;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: 30px;
}
.aj-btn-green {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background-color: #276749;
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
  padding: 14px 28px;
  border-radius: 30px;
  transition: all 0.25s ease;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(0,0,0,0.2);
}
.aj-btn-green:hover {
  background-color: #2f7d58;
  color: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

/* Responsive Breakpoints */
@media (max-width: 1024px) {
  .aj-features-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 868px) {
  .aj-story-grid, .aj-reflection-grid, .aj-community-grid {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  .aj-founder-card {
    grid-template-columns: 1fr;
  }
  .aj-founder-avatar {
    width: 100%;
    height: 240px;
  }
  .aj-features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 580px) {
  .aj-features-grid {
    grid-template-columns: 1fr;
  }
  .aj-community-card {
    padding: 26px 20px;
  }
}
</style>

<div class="about-page">

  <!-- ================= 1. HERO SECTION ================= -->
  <section class="aj-hero">
    <div class="aj-hero-overlay"></div>
    <div class="container aj-hero-content">
      <span class="eyebrow">ABOUT EXPLORES JAVA</span>
      <h1>A Journey of Exploration, Curiosity <span class="accent-text">&amp; Faith</span></h1>
      <p class="aj-hero-lead">Explores Java was born from a simple love for travelling, nature, and discovering something new. What began as a personal passion has grown into a journey we hope to share with travellers from around the world.</p>
    </div>
  </section>

  <!-- ================= 2. THE STORY BEHIND EXPLORES JAVA ================= -->
  <section class="aj-story">
    <div class="container aj-story-grid">
      
      <!-- Polaroid Stack Media -->
      <div class="aj-polaroid-stack">
        <!-- SVG Botanical Leaf Decor -->
        <svg class="aj-leaf-decor" viewBox="0 0 100 140" fill="none" stroke="#275638" stroke-width="2">
          <path d="M50 130 Q30 80 50 10 Q70 80 50 130 Z"/>
          <path d="M50 130 Q10 100 30 70 M50 100 Q80 80 70 50 M50 70 Q20 50 35 30"/>
        </svg>
        
        <div class="aj-polaroid aj-polaroid-main">
          <img src="assets/images/bromo.jpg" alt="Traveler exploring Wonogiri landscape" class="aj-polaroid-img">
          <div class="aj-polaroid-caption">Wonogiri</div>
        </div>

        <div class="aj-polaroid aj-polaroid-secondary">
          <img src="assets/images/hills.jpg" alt="Landscape of Java" class="aj-polaroid-img">
        </div>
      </div>

      <!-- Story Text Content -->
      <div class="aj-story-text">
        <span class="eyebrow">THE STORY BEHIND EXPLORES JAVA</span>
        <h2>One Island. One Journey.<br>A World to Discover.</h2>
        
        <p>Explores Java was created by Raka Dianjaya, a traveller, designer, and lifelong explorer from Indonesia.</p>
        <p>Since childhood, I have always been curious about what lies beyond the familiar. I have loved walking through nature, discovering new places, meeting different people, and experiencing things I had never experienced before.</p>
        <p>I was born in Wonogiri, a city whose name comes from the words <em>wana</em> (forest) and <em>giri</em> (mountain). Perhaps it is fitting that my journey began there, surrounded by the landscapes that shaped my love for nature and exploration.</p>
        
        <p class="aj-highlight-green">But for me, travelling is about more than simply visiting beautiful places.</p>
        <p class="aj-bold-dark">It is about seeing. Experiencing. Reflecting.</p>
        
        <p>As a Muslim, I believe that Allah created the universe and everything within it as signs for humanity to contemplate — to recognize His greatness, His wisdom, and His countless attributes.</p>
        <p>For me, travelling is one way of doing that.<br>Every mountain, forest, river, village, sunrise, culture, and human story can become an opportunity to pause and reflect.</p>
        
        <p class="aj-quote-green">Explores Java is my small expression of that belief.</p>
      </div>

    </div>
  </section>

  <!-- ================= 3. WHY JAVA SECTION ================= -->
  <section class="aj-why-java">
    <div class="aj-why-java-overlay"></div>
    <div class="container aj-why-java-content">
      <span class="eyebrow">WHY JAVA?</span>
      <h2>This Is Where My Journey Begins.</h2>
      
      <p><strong>Why Java? Because this is home.</strong></p>
      <p>Java is where I was born, where I live, and where my own journey of exploration began. It is an island of mountains, volcanoes, forests, beaches, ancient kingdoms, living traditions, vibrant cities, and countless stories waiting to be discovered.</p>
      
      <p class="aj-bold-dark" style="margin: 20px 0 10px;">
        I don't want to simply show you the famous places.<br>
        I want to explore the roads between them.
      </p>
      
      <p>To discover the small villages, hidden landscapes, local food, unexpected encounters, and stories that you might never find in a standard itinerary.</p>
      
      <p class="aj-highlight-green" style="margin-top: 24px; font-size: 15.5px;">
        Java is only the beginning.<br>
        There is still so much of Indonesia to explore — and eventually, perhaps, the rest of the world.
      </p>
    </div>
  </section>

  <!-- ================= 4. MORE THAN A TOUR ================= -->
  <section class="aj-more-tour">
    <div class="container">
      
      <div class="section-head">
        <span class="eyebrow">MORE THAN A TOUR</span>
        <h2>Travel Is a Journey,<br>Not Just a Destination.</h2>
        <p class="section-sub">We believe the best journeys are not measured by how many places you visit. They are remembered through the things you experience along the way.</p>
      </div>

      <div class="aj-features-grid">
        
        <!-- 1. Discover -->
        <div class="aj-feature-card">
          <div class="aj-feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="m16.2 7.8-2 6.3-6.4 2.1 2-6.3z"/>
            </svg>
          </div>
          <h3>Discover</h3>
          <p>places beyond the usual tourist routes.</p>
        </div>

        <!-- 2. Experience -->
        <div class="aj-feature-card">
          <div class="aj-feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <h3>Experience</h3>
          <p>local life instead of simply observing it.</p>
        </div>

        <!-- 3. Meet -->
        <div class="aj-feature-card">
          <div class="aj-feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><circle cx="19" cy="11" r="3"/>
            </svg>
          </div>
          <h3>Meet</h3>
          <p>people and hear their stories.</p>
        </div>

        <!-- 4. Explore -->
        <div class="aj-feature-card">
          <div class="aj-feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="m8 3 4 8 5-5 5 15H2L8 3z"/>
            </svg>
          </div>
          <h3>Explore</h3>
          <p>nature, culture, history and everyday life.</p>
        </div>

        <!-- 5. Reflect -->
        <div class="aj-feature-card">
          <div class="aj-feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
            </svg>
          </div>
          <h3>Reflect</h3>
          <p>on what the journey teaches you.</p>
        </div>

      </div>

      <p class="aj-more-footer">Whether you want an adventure, a cultural experience, a nature escape, or simply a different way to see Java, we create journeys that leave room for curiosity.</p>

    </div>
  </section>

  <!-- ================= 5. A JOURNEY OF REFLECTION ================= -->
  <section class="aj-reflection">
    <div class="container aj-reflection-grid">
      
      <!-- Left Polaroid Frame -->
      <div class="aj-polaroid-stack">
        <div class="aj-polaroid" style="transform: rotate(-2deg); max-width: 440px; margin: 0 auto;">
          <img src="assets/images/bromo-hero-sunrise.jpg" alt="Explore Experience Reflect sunrise" class="aj-polaroid-img" style="height: 300px;">
          <div class="aj-polaroid-caption" style="font-size: 28px; padding-top: 10px;">Explore. Experience. Reflect.</div>
        </div>
      </div>

      <!-- Right Reflection Text -->
      <div class="aj-story-text">
        <span class="eyebrow">A JOURNEY OF REFLECTION</span>
        <h2>Explore. Experience. Reflect.</h2>
        
        <p>For me, exploration has a deeper meaning.</p>
        <p>I believe the world around us is full of signs worth contemplating — from the vastness of mountains and oceans to the smallest details of nature and the diversity of human cultures.</p>
        <p>As a Muslim, I see travelling as an opportunity to reflect upon the creation of Allah, appreciate its beauty, and become more conscious of the world we have been given.</p>
        
        <p class="aj-highlight-green" style="font-size: 16px; margin-top: 20px;">
          Explores Java is part of that personal journey.
        </p>
        <p class="aj-quote-green" style="margin-top: 4px; font-weight: 500;">
          A journey to see more, learn more, appreciate more, and become more grateful.
        </p>
      </div>

    </div>
  </section>

  <!-- ================= 6. THE PEOPLE BEHIND THE JOURNEY ================= -->
  <section class="aj-community">
    <div class="container aj-community-grid">
      
      <!-- Card 1: Meet the Founder -->
      <div class="aj-community-card">
        <div class="aj-founder-card">
          <img src="assets/images/bromo-jeep-video.jpg" alt="Raka Dianjaya" class="aj-founder-avatar">
          <div>
            <span class="eyebrow">MEET THE FOUNDER</span>
            <h3>Hi, I'm Raka.</h3>
            <p>I'm the person behind Explores Java.</p>
            <p>I'm passionate about nature, travelling, design, photography, cycling, culture, and discovering new places.</p>
            <p>I've always had a desire to explore — not just to see somewhere new, but to understand it, experience it, and remember it.</p>
            <p>Explores Java is my way of turning that passion into something I can share with others.</p>
            <p>I don't see myself simply as someone who organizes tours. <strong class="aj-highlight-green">I'm still a traveller myself.</strong></p>
            <p>I'm learning, exploring, discovering and sometimes getting lost along the way.</p>
            <p style="margin-top: 14px;">And that's exactly what I hope Explores Java can be: <span class="aj-highlight-green">a journey we experience together.</span></p>
          </div>
        </div>
      </div>

      <!-- Card 2: The People Behind The Journey -->
      <div class="aj-community-card">
        <span class="eyebrow">THE PEOPLE BEHIND THE JOURNEY</span>
        <h3>More Than Just Us.<br>A Whole Community.</h3>
        
        <p>Explores Java may grow into a team in the future, but it started with one person and one simple idea:</p>
        <p class="aj-highlight-green" style="font-size: 16px; font-weight: 700; margin: 16px 0;">
          There is still so much to discover.
        </p>
        <p>From local guides and drivers to artisans, farmers, hosts and communities, every journey is also made possible by the people we meet along the way.</p>
        <p>We believe these people are not simply "service providers".</p>
        <p class="aj-quote-green" style="font-size: 16px; margin-top: 20px;">
          They are part of the journey.
        </p>
      </div>

    </div>
  </section>

  <!-- ================= 7. CTA SECTION ================= -->
  <section class="aj-cta">
    <div class="aj-cta-overlay"></div>
    <div class="container aj-cta-content">
      <span class="eyebrow">YOUR JOURNEY STARTS HERE</span>
      <h2>Java is waiting.</h2>
      <p>Come with an open mind, a curious heart, and a willingness to experience something new.</p>
      <a href="/packages" class="aj-btn-green">
        <span>Start Your Journey</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
        </svg>
      </a>
    </div>
  </section>

</div>
