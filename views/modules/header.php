<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($metaTitle ?? 'Explores Java — Discover the Beauty of Java Island') ?></title>
<?php if (!empty($metaDescription)): ?>
<meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
<?php endif; ?>
<?php if (!empty($canonicalUrl)): ?>
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
<?php endif; ?>
<?php if (!empty($ogImage)): ?>
<meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
<?php endif; ?>
<?php if (!empty($metaTitle)): ?>
<meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
<?php endif; ?>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body id="top">

<!-- ================= HEADER ================= -->
<header class="site-header" id="siteHeader">
  <div class="header-inner">
    <a href="#top" class="brand" aria-label="Explores Java">
      <!--<svg class="brand-mark" viewBox="0 0 44 36" fill="none" aria-hidden="true">
        <circle cx="22" cy="8" r="6" fill="#F2A33C"/>
        <path d="M4 30 L14 14 L20 24 L26 12 L40 30" stroke="#166534" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M8 30 h28" stroke="#166534" stroke-width="2.4" stroke-linecap="round"/>
      </svg>-->
      <img class="brand-mark" src="/assets/icons/exploresjava.png" alt="Mount Bromo">
      <!--<span class="brand-text">
        <span class="brand-name">Explores</span>
        <span class="brand-sub">Java</span>
      </span>-->
    </a>

    <nav class="main-nav" id="mainNav" aria-label="Navigasi utama">
      <ul>
        <li><a class="nav-link active" href="/">Home</a></li>
        <li class="has-drop">
          <button class="nav-link drop-btn" id="dropBtn" aria-expanded="false">Destinations
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="dropdown" id="dropMenu">
            <a href="/destinations">East Java</a>
            <a href="/destinations">Central Java</a>
            <a href="/destinations">West Java</a>
            <a href="/destinations">Yogyakarta</a>
          </div>
        </li>
        <li><a class="nav-link" href="/packages">Tour Packages</a></li>
        <li><a class="nav-link" href="/blog">Blog</a></li>
        <li><a class="nav-link" href="/why">About Us</a></li>
        <li><a class="nav-link" href="/contact">Contact</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <a class="icon-btn wa" href="#" aria-label="Chat WhatsApp" title="Chat via WhatsApp">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.6 0-3.1-.4-4.4-1.2L3 20l1.2-5.1A8.5 8.5 0 1 1 21 11.5Z"/></svg>
      </a>
      <a class="btn btn-primary btn-sm" href="#cta">Plan Your Trip</a>
      <button class="menu-btn" id="menuBtn" aria-label="Buka menu" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
