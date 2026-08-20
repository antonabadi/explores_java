// assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inisialisasi Tema
    initTheme();

    // 2. Inisialisasi i18n (Multi-bahasa)
    initI18n();

    // 3. Inisialisasi Modul Kasir (jika di halaman kasir)
    if (document.getElementById('pos-cashier-container')) {
        initCashier();
    }

    // 4. Inisialisasi Chart.js (jika di dashboard/laporan)
    if (document.getElementById('salesChart')) {
        initSalesChart();
    }

    // 5. Setup Tab Navigation
    initTabs();
});

/* ==========================================================================
   1. TEMA (DARK / LIGHT MODE)
   ========================================================================== */
function initTheme() {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;

    // Baca tema tersimpan atau gunakan dark mode sebagai default
    const savedTheme = localStorage.getItem('theme') || 'dark-theme';
    if (savedTheme === 'light-theme') {
        document.body.classList.add('light-theme');
        updateThemeButton('light');
    } else {
        document.body.classList.remove('light-theme');
        updateThemeButton('dark');
    }

    themeToggle.addEventListener('click', () => {
        if (document.body.classList.contains('light-theme')) {
            document.body.classList.remove('light-theme');
            localStorage.setItem('theme', 'dark-theme');
            updateThemeButton('dark');
        } else {
            document.body.classList.add('light-theme');
            localStorage.setItem('theme', 'light-theme');
            updateThemeButton('light');
        }
    });
}

function updateThemeButton(mode) {
    const textSpan = document.querySelector('#theme-toggle span:last-child');
    if (!textSpan) return;

    if (mode === 'light') {
        textSpan.setAttribute('data-i18n', 'theme_dark');
        textSpan.textContent = 'Mode Gelap';
    } else {
        textSpan.setAttribute('data-i18n', 'theme_light');
        textSpan.textContent = 'Mode Terang';
    }
}

/* ==========================================================================
   2. MULTI-BAHASA (i18n)
   ========================================================================== */
let translations = {};

async function initI18n() {
    const langSelect = document.getElementById('lang-select');
    if (!langSelect) return;

    let currentLang = localStorage.getItem('lang') || 'id';
    langSelect.value = currentLang;

    await loadTranslations(currentLang);

    langSelect.addEventListener('change', async (e) => {
        currentLang = e.target.value;
        localStorage.setItem('lang', currentLang);
        await loadTranslations(currentLang);
    });
}

async function loadTranslations(lang) {
    try {
        const response = await fetch(`locales/${lang}.json`);
        translations = await response.json();
        applyTranslations();
    } catch (error) {
        console.error('Gagal memuat file terjemahan:', error);
    }
}

function applyTranslations() {
    const elements = document.querySelectorAll('[data-i18n]');
    elements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[key]) {
            if (el.tagName === 'INPUT' && (el.type === 'text' || el.type === 'search')) {
                el.placeholder = translations[key];
            } else {
                el.textContent = translations[key];
            }
        }
    });
}

/* ==========================================================================
   3. APLIKASI KASIR (POS INTERFACE)
   ========================================================================== */
let cart = [];
let memberDiscountPercent = 5; // Diskon member 5% default

function initCashier() {
    const searchInput = document.getElementById('pos-search');//*
    const dropdown = document.getElementById('search-dropdown');
    const cartTableBody = document.getElementById('cart-table-body');//*
    const memberSelect = document.getElementById('member-select');
    const discountInput = document.getElementById('discount-input');
    const paymentMethodSelect = document.getElementById('payment-method');
    const cashInput = document.getElementById('cash-input');
    const checkoutBtn = document.getElementById('btn-checkout');//*

    if (!searchInput || !dropdown) return;

    // Live search event
    let debounceTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = searchInput.value.trim();

        if (query.length < 2) {
            dropdown.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`index.php?action=search_barang&q=${encodeURIComponent(query)}`);
                const items = await res.json();

                if (items.length > 0) {
                    renderDropdown(items);
                } else {
                    dropdown.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--text-muted)">Barang tidak ditemukan</div>';
                    dropdown.style.display = 'block';
                }
            } catch (err) {
                console.error('Error fetching search results:', err);
            }
        }, 300);
    });

    // Menutup dropdown ketika klik di luar
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    function renderDropdown(items) {
        dropdown.innerHTML = '';
        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'search-item-row';
            row.innerHTML = `
                <div>
                    <div class="search-item-name">${item.nama}</div>
                    <div class="search-item-barcode">${item.barcode}</div>
                </div>
                <div class="search-item-meta">
                    <div class="search-item-price">Rp ${formatRupiah(item.harga)}</div>
                    <div class="search-item-stock ${item.stok <= 0 ? 'out' : ''}">Stok: ${item.stok}</div>
                </div>
            `;

            row.addEventListener('click', () => {
                addToCart(item);
                dropdown.style.display = 'none';
                searchInput.value = '';
                searchInput.focus();
            });

            dropdown.appendChild(row);
        });
        dropdown.style.display = 'block';
    }

    function addToCart(item) {
        if (item.stok <= 0) {
            alert('Stok barang habis!');
            return;
        }

        const existing = cart.find(c => c.type === item.type && c.id === item.id);
        if (existing) {
            if (existing.jumlah >= item.stok) {
                alert('Jumlah melebihi stok gudang!');
                return;
            }
            existing.jumlah += 1;
            existing.subtotal = existing.jumlah * existing.harga;
        } else {
            cart.push({
                type: item.type,
                id: item.id,
                barcode: item.barcode,
                nama: item.nama,
                harga: item.harga,
                stok: item.stok,
                jumlah: 1,
                subtotal: item.harga
            });
        }
        renderCart();
    }
    window.addToCart = addToCart;

    function renderCart() {
        cartTableBody.innerHTML = '';

        if (cart.length === 0) {
            cartTableBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;" data-i18n="empty_cart">
                        Keranjang kosong
                    </td>
                </tr>
            `;
            applyTranslations();
            updateSummary();
            return;
        }

        cart.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div style="font-weight: 600;">${item.nama}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">${item.barcode}</div>
                </td>
                <td>Rp ${formatRupiah(item.harga)}</td>
                <td>
                    <div class="qty-control">
                        <button type="button" class="btn-qty decrease" data-index="${index}">-</button>
                        <span class="qty-input">${item.jumlah}</span>
                        <button type="button" class="btn-qty increase" data-index="${index}">+</button>
                    </div>
                </td>
                <td style="font-weight: 600; color: var(--color-accent);">Rp ${formatRupiah(item.subtotal)}</td>
                <td style="text-align: center;">
                    <button type="button" class="btn-remove" data-index="${index}">
                        &#128465;
                    </button>
                </td>
            `;

            // Event Listeners
            tr.querySelector('.decrease').addEventListener('click', () => {
    if (item.jumlah > 1) {
        item.jumlah -= 1;
        item.subtotal = item.jumlah * item.harga;
    } else {
        // Remove item from cart when quantity reaches zero
        cart.splice(index, 1);
    }
    renderCart();
});

            tr.querySelector('.increase').addEventListener('click', () => {
                if (item.jumlah < item.stok) {
                    item.jumlah += 1;
                    item.subtotal = item.jumlah * item.harga;
                    renderCart();
                } else {
                    alert('Jumlah melebihi stok yang tersedia!');
                }
            });

            tr.querySelector('.btn-remove').addEventListener('click', () => {
                cart.splice(index, 1);
                renderCart();
            });

            cartTableBody.appendChild(tr);
        });

        updateSummary();
    }

    // Auto Apply Diskon Member
    memberSelect.addEventListener('change', () => {
        updateSummary();
    });

    discountInput.addEventListener('input', () => {
        updateSummary();
    });

    cashInput.addEventListener('input', () => {
        calculateChange();
    });

    function updateSummary() {
        let totalKotor = 0;
        cart.forEach(item => totalKotor += item.subtotal);

        // Hitung diskon manual
        let diskonManual = parseFloat(discountInput.value) || 0;

        // Hitung diskon member (jika member dipilih)
        let diskonMember = 0;
        if (memberSelect.value !== '') {
            diskonMember = Math.floor(totalKotor * (memberDiscountPercent / 100));
        }

        let totalDiskon = diskonManual + diskonMember;
        let totalBersih = Math.max(0, totalKotor - totalDiskon);

        document.getElementById('sum-subtotal').textContent = 'Rp ' + formatRupiah(totalKotor);
        document.getElementById('sum-discount').textContent = 'Rp ' + formatRupiah(totalDiskon);
        document.getElementById('sum-total').textContent = 'Rp ' + formatRupiah(totalBersih);

        calculateChange();
    }

    function calculateChange() {
        let totalKotor = 0;
        cart.forEach(item => totalKotor += item.subtotal);
        let diskonManual = parseFloat(discountInput.value) || 0;
        let diskonMember = (memberSelect.value !== '') ? Math.floor(totalKotor * (memberDiscountPercent / 100)) : 0;
        let totalBersih = Math.max(0, totalKotor - (diskonManual + diskonMember));

        let cashValue = parseFloat(cashInput.value) || 0;
        let change = Math.max(0, cashValue - totalBersih);

        document.getElementById('sum-change').textContent = 'Rp ' + formatRupiah(change);
    }

    // Handle Checkout Action
    checkoutBtn.addEventListener('click', async () => {
        if (cart.length === 0) {
            alert('Keranjang belanja kosong.');
            return;
        }

        let totalKotor = 0;
        cart.forEach(item => totalKotor += item.subtotal);
        let diskonManual = parseFloat(discountInput.value) || 0;
        let diskonMember = (memberSelect.value !== '') ? Math.floor(totalKotor * (memberDiscountPercent / 100)) : 0;
        let totalDiskon = diskonManual + diskonMember;
        let totalBersih = Math.max(0, totalKotor - totalDiskon);

        let cashValue = parseFloat(cashInput.value) || 0;
        if (paymentMethodSelect.value === 'Tunai' && cashValue < totalBersih) {
            alert('Jumlah uang tunai tidak mencukupi.');
            return;
        }

        const checkoutData = {
            member_id: memberSelect.value !== '' ? parseInt(memberSelect.value) : null,
            total_kotor: totalKotor,
            diskon: totalDiskon,
            total_bersih: totalBersih,
            metode_bayar: paymentMethodSelect.value,
            items: cart.map(c => ({
                type: c.type,
                id: c.id,
                harga: c.harga,
                jumlah: c.jumlah,
                subtotal: c.subtotal
            }))
        };

        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Memproses...';

        try {
            const response = await fetch('index.php?action=checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(checkoutData)
            });
            const res = await response.json();

            if (res.success) {
                alert('Transaksi Berhasil! Invoice: ' + res.invoice);

                // Buka modal cetak struk
                showReceipt(res.penjualan_id);

                // Reset Kasir
                cart = [];
                discountInput.value = '0';
                cashInput.value = '';
                memberSelect.value = '';
                renderCart();
            } else {
                alert('Transaksi Gagal: ' + res.message);
            }
        } catch (err) {
            console.error('Error saat checkout:', err);
            alert('Koneksi terputus. Gagal melakukan checkout.');
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = translations['checkout'] || 'Bayar / Selesai';
        }
    });
}

// Menampilkan Struk Thermal & Trigger Print
async function showReceipt(transaksiId) {
    try {
        const response = await fetch(`index.php?action=get_invoice&id=${transaksiId}`);
        const res = await response.json();

        if (res.success) {
            const data = res.data;
            const modal = document.getElementById('receipt-modal');
            const content = document.getElementById('receipt-print-content');

            if (!modal || !content) return;

            let itemsHtml = '';
            data.items.forEach(item => {
                let name = item.nama_barang || item.nama_paket;
                itemsHtml += `
                    <div class="receipt-row">
                        <span>${name} x${item.jumlah}</span>
                        <span>Rp ${formatRupiah(item.subtotal)}</span>
                    </div>
                `;
            });

            content.innerHTML = `
                <div class="receipt-header">
                    <h2 class="receipt-title">POS APOTEK / TOKO</h2>
                    <p>Cabang: ${data.kasir_nama}</p>
                    <p>No: ${data.invoice_number}</p>
                    <p>Tgl: ${data.created_at}</p>
                </div>
                <div class="receipt-divider"></div>
                <div class="receipt-items">
                    ${itemsHtml}
                </div>
                <div class="receipt-divider"></div>
                <div class="receipt-row" style="font-weight: bold;">
                    <span>Total Kotor:</span>
                    <span>Rp ${formatRupiah(data.total_kotor)}</span>
                </div>
                <div class="receipt-row">
                    <span>Diskon:</span>
                    <span>Rp ${formatRupiah(data.diskon)}</span>
                </div>
                <div class="receipt-row" style="font-weight: bold; font-size:12px;">
                    <span>Total Bersih:</span>
                    <span>Rp ${formatRupiah(data.total_bersih)}</span>
                </div>
                <div class="receipt-row">
                    <span>Metode:</span>
                    <span>${data.metode_bayar}</span>
                </div>
                ${data.nama_member ? `<div class="receipt-row"><span>Member:</span><span>${data.nama_member}</span></div>` : ''}
                <div class="receipt-divider"></div>
                <p style="text-align: center; margin-top: 10px;">Terima Kasih Atas Kunjungan Anda</p>
            `;

            // Buka Modal overlay
            modal.classList.add('active');

            // Setup cetak & tutup
            const printBtn = document.getElementById('btn-print-receipt');
            const closeBtn = document.getElementById('btn-close-receipt');

            printBtn.onclick = () => {
                window.print();
            };

            closeBtn.onclick = () => {
                modal.classList.remove('active');
            };

        } else {
            alert('Gagal mengambil data struk.');
        }
    } catch (err) {
        console.error('Error rendering receipt:', err);
    }
}

/* ==========================================================================
   4. VISUALISASI GRAFIK TREN PENJUALAN (CHART.JS)
   ========================================================================== */
async function initSalesChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    try {
        const response = await fetch('index.php?page=dashboard_data');
        const res = await response.json();

        const labels = res.map(row => {
            const d = new Date(row.tanggal);
            return d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });
        });
        const salesData = res.map(row => parseFloat(row.total_penjualan));

        // Konfigurasi Chart.js
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan Bersih (Rp)',
                    data: salesData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#a855f7',
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: function(value) {
                                return 'Rp ' + formatRupiah(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });

    } catch (err) {
        console.error('Gagal menginisialisasi grafik:', err);
    }
}

/* ==========================================================================
   5. MODUL NAVIGASI TAB (UNTUK SUB-HALAMAN)
   ========================================================================== */
function initTabs() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabLinks.length === 0) return;

    // Membaca tab aktif dari URL hash/param jika ada
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab');
    if (initialTab) {
        const activeLink = document.querySelector(`.tab-link[data-tab="${initialTab}"]`);
        const activeContent = document.getElementById(`tab-${initialTab}`);
        if (activeLink && activeContent) {
            tabLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            activeLink.classList.add('active');
            activeContent.classList.add('active');
        }
    }

    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tabId = link.getAttribute('data-tab');

            tabLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            link.classList.add('active');
            const targetContent = document.getElementById(`tab-${tabId}`);
            if (targetContent) {
                targetContent.classList.add('active');
            }

            // Update URL query string tanpa reload halaman
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        });
    });
}

/* ==========================================================================
   HELPER UTILITY
   ========================================================================== */
function formatRupiah(angka) {
    if (isNaN(angka)) return '0';
    return parseFloat(angka).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}
