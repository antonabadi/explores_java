/**
 * Explores Java CMS — vanilla JS (no framework)
 */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initModals();
    initConfirmDelete();
    initAutoDismissAlerts();
    initRevenueChart();
});

/* Theme toggle */
function initTheme() {
    const toggle = document.getElementById('theme-toggle');
    const label = document.getElementById('theme-label');
    if (!toggle) return;

    const saved = localStorage.getItem('ej-theme') || 'dark';
    applyTheme(saved, label);

    toggle.addEventListener('click', () => {
        const next = document.body.classList.contains('light-theme') ? 'dark' : 'light';
        localStorage.setItem('ej-theme', next);
        applyTheme(next, label);
    });
}

function applyTheme(mode, label) {
    if (mode === 'light') {
        document.body.classList.add('light-theme');
        if (label) label.textContent = 'Dark Mode';
    } else {
        document.body.classList.remove('light-theme');
        if (label) label.textContent = 'Light Mode';
    }
}

/* Modals */
function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-modal-open');
            const formId = btn.getAttribute('data-reset-form');
            if (formId) {
                const form = document.getElementById(formId);
                if (form) form.reset();
            }
            openModal(id);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const overlay = btn.closest('.modal-overlay');
            if (overlay) closeModal(overlay.id);
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) closeModal(overlay.id);
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => closeModal(m.id));
        }
    });
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

/* Delete confirmation */
function initConfirmDelete() {
    document.querySelectorAll('[data-confirm]').forEach(link => {
        link.addEventListener('click', e => {
            const msg = link.getAttribute('data-confirm');
            if (!confirm(msg)) e.preventDefault();
        });
    });
}

/* Auto-dismiss flash alerts */
function initAutoDismissAlerts() {
    document.querySelectorAll('.alert-box').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
}

/* Vanilla canvas bar chart */
function initRevenueChart() {
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;

    let labels = [];
    let values = [];

    try {
        labels = JSON.parse(canvas.dataset.labels || '[]');
        values = JSON.parse(canvas.dataset.values || '[]');
    } catch {
        return;
    }

    if (!labels.length) {
        drawEmptyChart(canvas);
        return;
    }

    drawBarChart(canvas, labels, values);
    window.addEventListener('resize', debounce(() => drawBarChart(canvas, labels, values), 200));
}

function drawEmptyChart(canvas) {
    const ctx = canvas.getContext('2d');
    const rect = canvas.parentElement.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height || 260;

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--text-muted').trim() || '#64748b';
    ctx.font = '14px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('No revenue data yet', canvas.width / 2, canvas.height / 2);
}

function drawBarChart(canvas, labels, values) {
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.parentElement.getBoundingClientRect();
    const w = rect.width;
    const h = rect.height || 260;

    canvas.width = w * dpr;
    canvas.height = h * dpr;
    canvas.style.width = w + 'px';
    canvas.style.height = h + 'px';
    ctx.scale(dpr, dpr);

    const styles = getComputedStyle(document.body);
    const accent = styles.getPropertyValue('--color-accent').trim() || '#6366f1';
    const textColor = styles.getPropertyValue('--text-secondary').trim() || '#94a3b8';
    const gridColor = styles.getPropertyValue('--border-color').trim() || '#26335a';

    const padding = { top: 20, right: 20, bottom: 40, left: 70 };
    const chartW = w - padding.left - padding.right;
    const chartH = h - padding.top - padding.bottom;

    const maxVal = Math.max(...values, 1);
    const barGap = 12;
    const barW = (chartW - barGap * (labels.length - 1)) / labels.length;

    ctx.clearRect(0, 0, w, h);

    // Grid lines & Y labels
    const gridLines = 5;
    ctx.font = '11px Inter, sans-serif';
    ctx.fillStyle = textColor;
    ctx.textAlign = 'right';

    for (let i = 0; i <= gridLines; i++) {
        const y = padding.top + chartH - (chartH / gridLines) * i;
        const val = (maxVal / gridLines) * i;

        ctx.strokeStyle = gridColor;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(w - padding.right, y);
        ctx.stroke();

        ctx.fillText(formatShortRupiah(val), padding.left - 8, y + 4);
    }

    // Bars
    labels.forEach((label, i) => {
        const barH = (values[i] / maxVal) * chartH;
        const x = padding.left + i * (barW + barGap);
        const y = padding.top + chartH - barH;

        const gradient = ctx.createLinearGradient(x, y, x, y + barH);
        gradient.addColorStop(0, accent);
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.35)');

        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.roundRect(x, y, barW, barH, [4, 4, 0, 0]);
        ctx.fill();

        // X label
        ctx.fillStyle = textColor;
        ctx.textAlign = 'center';
        ctx.font = '10px Inter, sans-serif';
        const shortLabel = label.length > 7 ? label.slice(5) : label;
        ctx.fillText(shortLabel, x + barW / 2, h - padding.bottom + 20);
    });
}

function formatShortRupiah(val) {
    if (val >= 1e9) return (val / 1e9).toFixed(1) + 'B';
    if (val >= 1e6) return (val / 1e6).toFixed(1) + 'M';
    if (val >= 1e3) return (val / 1e3).toFixed(0) + 'K';
    return Math.round(val).toString();
}

function debounce(fn, ms) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
}
