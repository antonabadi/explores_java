<?php
$adminName = $_SESSION['admin_username'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'admin-content';
$initial = strtoupper(substr($adminName, 0, 1));
?>
<aside class="sidebar">
    <div class="brand-section">
        <div class="brand-logo">EJ</div>
        <span class="brand-name">Explores Java</span>
    </div>

    <ul class="nav-menu">
        <li class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <a href="dashboard.php?page=dashboard">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </span>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item <?= ($activePage ?? '') === 'destinations' ? 'active' : '' ?>">
            <a href="dashboard.php?page=destinations">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </span>
                <span>Destinations</span>
            </a>
        </li>
        <li class="nav-item <?= ($activePage ?? '') === 'packages' ? 'active' : '' ?>">
            <a href="dashboard.php?page=packages">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </span>
                <span>Packages</span>
            </a>
        </li>
        <li class="nav-item <?= ($activePage ?? '') === 'tours' ? 'active' : '' ?>">
            <a href="dashboard.php?page=tours">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </span>
                <span>Tours</span>
            </a>
        </li>
        <li class="nav-item <?= ($activePage ?? '') === 'bookings' ? 'active' : '' ?>">
            <a href="dashboard.php?page=bookings">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </span>
                <span>Bookings</span>
            </a>
        </li>
        <li class="nav-item <?= ($activePage ?? '') === 'testimonials' ? 'active' : '' ?>">
            <a href="dashboard.php?page=testimonials">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <span>Testimonials</span>
            </a>
        </li>
        <li class="nav-item <?= ($activePage ?? '') === 'blogs' ? 'active' : '' ?>">
            <a href="dashboard.php?page=blogs">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </span>
                <span>Blogs</span>
            </a>
        </li>
        <li class="nav-item <?= ($activePage ?? '') === 'admins' ? 'active' : '' ?>">
            <a href="dashboard.php?page=admins">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span>Admins</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info-panel">
            <div class="user-avatar"><?= e($initial) ?></div>
            <div class="user-details">
                <span class="user-name"><?= e($adminName) ?></span>
                <span class="user-role"><?= e(ucwords(str_replace('-', ' ', $adminRole))) ?></span>
            </div>
        </div>

        <div class="ui-controls">
            <button type="button" id="theme-toggle" class="btn-control">
                <span class="nav-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                </span>
                <span id="theme-label">Light Mode</span>
            </button>
        </div>

        <a href="dashboard.php?page=logout" class="btn-control btn-logout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Logout
        </a>
    </div>
</aside>
