(function () {
  /* Header shadow saat scroll */
  var header = document.getElementById('siteHeader');
  function onScroll() { header.classList.toggle('scrolled', window.scrollY > 10); }
  onScroll(); window.addEventListener('scroll', onScroll, { passive: true });

  /* Menu mobile */
  var menuBtn = document.getElementById('menuBtn'), nav = document.getElementById('mainNav');
  menuBtn.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    menuBtn.classList.toggle('open', open);
    menuBtn.setAttribute('aria-expanded', open);
  });
  nav.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () { nav.classList.remove('open'); menuBtn.classList.remove('open'); });
  });

  /* Dropdown Destinations */
  var dropBtn = document.getElementById('dropBtn'), dropMenu = document.getElementById('dropMenu');
  dropBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    var open = dropMenu.classList.toggle('show');
    dropBtn.setAttribute('aria-expanded', open);
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.has-drop')) { dropMenu.classList.remove('show'); dropBtn.setAttribute('aria-expanded', 'false'); }
  });

  /* Navigasi aktif */
  document.querySelectorAll('.main-nav .nav-link').forEach(function (link) {
    link.addEventListener('click', function () {
      document.querySelectorAll('.main-nav .nav-link').forEach(function (l) { l.classList.remove('active'); });
      link.classList.add('active');
    });
    });

  document.addEventListener("DOMContentLoaded", function () {
    // 1. Ambil query string dari URL browser saat ini (contoh: "?page=contact")
    let currentQuery = window.location.search;
    // const currentQuery = window.location.search || "?page=home";
    if(currentQuery === "" || currentQuery == null) {
      currentQuery = "?page=home";
    }

    // 2. Cari semua elemen link navigasi
    document.querySelectorAll('.main-nav .nav-link').forEach(function (link) {
      const linkHref = link.getAttribute('href');

      // 3. Jika URL browser mengandung nilai href dari link tersebut
      if (linkHref && currentQuery.includes(linkHref)) {
        link.classList.add('active');
      } else {
        link.classList.remove('active'); // Memastikan menu lain bersih dari class active
      }
    });
  });

  /* Carousel destinations */
  /*
  var track = document.getElementById('destTrack');
  function step(){ var c = track.querySelector('.dest-card'); return c ? c.offsetWidth + 20 : 280; }
  document.getElementById('carPrev').addEventListener('click', function(){ track.scrollBy({left:-step(), behavior:'smooth'}); });
  document.getElementById('carNext').addEventListener('click', function(){ track.scrollBy({left: step(), behavior:'smooth'}); });
 */

  /* Toast demo */
  var toast = document.getElementById('toast'), tId;
  function showToast(msg) {
    toast.textContent = msg; toast.classList.add('show');
    clearTimeout(tId); tId = setTimeout(function () { toast.classList.remove('show'); }, 2600);
  }
  /*
  document.getElementById('searchForm').addEventListener('submit', function (e) {
    e.preventDefault(); showToast('Mencari perjalanan terbaik… (demo)');
  });
  document.getElementById('watchVideo').addEventListener('click', function(){
    showToast('Video segera hadir (demo)');
  });
  */
  document.querySelectorAll('a[href="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) { e.preventDefault(); showToast('Tautan demo — belum terhubung.'); });
  });

  /* Animasi reveal saat scroll */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('visible'); io.unobserve(en.target); }
      });
    }, { threshold: .12 });
    document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('visible'); });
  }
})();
