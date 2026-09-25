<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Asset.php';

$assetModel = new Asset();
$uploadedAssets = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? 'general');
    $titleInput = trim($_POST['title'] ?? '');
    $altTextInput = trim($_POST['alt_text'] ?? '');
    $imageUrlInput = trim($_POST['image_url'] ?? '');

    $uploadDir = __DIR__ . '/../../../assets/images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $errors = [];
    $successCount = 0;

    // Check file upload(s)
    if (isset($_FILES['image_files']) && !empty($_FILES['image_files']['name'][0])) {
        $files = $_FILES['image_files'];
        $totalFiles = count($files['name']);
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

        for ($i = 0; $i < $totalFiles; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $origName = $files['name'][$i];
                $tmpName = $files['tmp_name'][$i];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed, true)) {
                    $errors[] = "Format file '{$origName}' tidak didukung.";
                    continue;
                }

                $newFilename = 'asset_' . time() . '_' . uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $newFilename;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $filePath = 'assets/images/' . $newFilename;
                    $fileSize = filesize($targetPath) ?: 0;
                    $mimeType = $files['type'][$i] ?: 'image/' . $ext;

                    $dimensions = null;
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                        $info = @getimagesize($targetPath);
                        if ($info && isset($info[0], $info[1])) {
                            $dimensions = $info[0] . 'x' . $info[1];
                        }
                    }

                    $title = $titleInput !== '' ? ($totalFiles > 1 ? $titleInput . ' ' . ($i + 1) : $titleInput) : ucwords(str_replace(['-', '_'], ' ', pathinfo($origName, PATHINFO_FILENAME)));
                    $altText = $altTextInput !== '' ? $altTextInput : $title;

                    $assetId = $assetModel->create([
                        'title'      => $title,
                        'filename'   => $newFilename,
                        'file_path'  => $filePath,
                        'file_size'  => $fileSize,
                        'mime_type'  => $mimeType,
                        'dimensions' => $dimensions,
                        'alt_text'   => $altText,
                        'category'   => $category,
                    ]);

                    $uploadedAssets[] = $assetModel->find($assetId);
                    $successCount++;
                } else {
                    $errors[] = "Gagal menyimpan file '{$origName}'.";
                }
            }
        }
    } elseif ($imageUrlInput !== '') {
        $filename = basename(parse_url($imageUrlInput, PHP_URL_PATH) ?: 'external_image');
        $title = $titleInput !== '' ? $titleInput : ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME)));

        $assetId = $assetModel->create([
            'title'      => $title,
            'filename'   => $filename,
            'file_path'  => $imageUrlInput,
            'file_size'  => 0,
            'mime_type'  => 'image/external',
            'dimensions' => null,
            'alt_text'   => $altTextInput !== '' ? $altTextInput : $title,
            'category'   => $category,
        ]);

        $uploadedAssets[] = $assetModel->find($assetId);
        $successCount++;
    } else {
        $errors[] = "Harap pilih file gambar untuk diunggah atau masukkan URL gambar.";
    }

    if ($successCount > 0) {
        setFlash('success', "Berhasil mengunggah {$successCount} asset gambar baru.");
    }
    if (!empty($errors)) {
        setFlash('danger', implode('<br>', $errors));
    }
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Tambah Asset Gambar Baru</h1>
            <p class="date-indicator">Unggah berkas gambar baru atau tambahkan tautan gambar eksternal</p>
        </div>
        <a href="dashboard.php?page=assets" class="btn-secondary">
            ← Kembali ke Manajemen Asset
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <?php if (!empty($uploadedAssets)): ?>
        <div class="glass-card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--color-success);">
            <h3 class="card-title" style="color: var(--color-success); margin-bottom: 0.75rem;">✓ Asset Berhasil Diunggah</h3>
            <p class="text-muted" style="margin-bottom: 1rem;">Berikut adalah asset yang baru saja ditambahkan. Anda dapat langsung menyalin URL gambar di bawah ini:</p>
            
            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.25rem;">
                <?php foreach ($uploadedAssets as $asset): ?>
                    <?php
                    $isExternal = str_starts_with($asset['file_path'], 'http://') || str_starts_with($asset['file_path'], 'https://');
                    $src = $isExternal ? $asset['file_path'] : '../' . ltrim($asset['file_path'], '/');
                    ?>
                    <div style="display: flex; align-items: center; gap: 1rem; background: var(--bg-tertiary); padding: 0.75rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <img src="<?= e($src) ?>" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; background: #1a1d24;">
                        <div style="flex: 1; min-width: 0;">
                            <div class="fw-bold" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($asset['title']) ?></div>
                            <div class="text-muted text-sm" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($asset['file_path']) ?></div>
                        </div>
                        <button type="button" class="btn-secondary btn-sm copy-btn" data-url="<?= e($asset['file_path']) ?>">
                            Salin URL
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <a href="dashboard.php?page=assets" class="btn-primary">Lihat di Manajemen Asset</a>
                <a href="dashboard.php?page=assets_add" class="btn-secondary">Unggah Lagi</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="max-width: 800px; margin: 0 auto;">
        <!-- Upload Mode Tabs -->
        <div style="display: flex; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem;">
            <button type="button" class="tab-btn active" id="tabUploadFile" onclick="switchUploadMode('file')" style="padding: 0.75rem 1.25rem; font-weight: 600; border: none; background: none; color: var(--color-accent); border-bottom: 2px solid var(--color-accent); cursor: pointer;">
                Unggah File Gambar
            </button>
            <button type="button" class="tab-btn" id="tabUploadUrl" onclick="switchUploadMode('url')" style="padding: 0.75rem 1.25rem; font-weight: 600; border: none; background: none; color: var(--text-muted); cursor: pointer;">
                Input URL Gambar
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <!-- Mode 1: File Upload Dropzone -->
            <div id="sectionFileUpload">
                <div class="dropzone" id="dropzoneArea" style="border: 2px dashed var(--border-color); border-radius: var(--radius-lg); padding: 2.5rem 1.5rem; text-align: center; background: rgba(255,255,255,0.02); transition: all 0.2s; cursor: pointer; margin-bottom: 1.5rem;">
                    <input type="file" name="image_files[]" id="fileInput" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" style="display: none;" onchange="handleFileSelect(this.files)">
                    <div style="margin-bottom: 0.75rem;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--color-accent);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Tarik & Lepas berkas gambar ke sini</h3>
                    <p class="text-muted text-sm" style="margin-bottom: 1rem;">Mendukung format JPG, PNG, WEBP, GIF, dan SVG (bisa pilih banyak file sekaligus)</p>
                    <button type="button" class="btn-secondary" onclick="document.getElementById('fileInput').click()">Pilih Berkas dari Komputer</button>
                </div>

                <div id="filePreviewContainer" style="display: none; margin-bottom: 1.5rem;">
                    <label class="form-label" style="margin-bottom: 0.5rem;">Berkas Dipilih:</label>
                    <div id="previewList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem;"></div>
                </div>
            </div>

            <!-- Mode 2: External URL Input -->
            <div id="sectionUrlUpload" style="display: none; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">URL Gambar Eksternal</label>
                    <input type="url" name="image_url" id="imageUrlInput" class="form-control" placeholder="https://example.com/images/sample.jpg" oninput="previewUrlImage(this.value)">
                </div>
                <div id="urlPreviewContainer" style="display: none; text-align: center; padding: 1rem; background: #1a1d24; border-radius: var(--radius-md);">
                    <img id="urlPreviewImg" src="" alt="URL Preview" style="max-height: 200px; object-fit: contain; border-radius: 4px;">
                </div>
            </div>

            <!-- Metadata Fields -->
            <div style="background: var(--bg-tertiary); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 1.5rem;">
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Metadata Asset (Opsional)</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Kategori Asset</label>
                        <select name="category" class="form-control">
                            <option value="general">Umum / General</option>
                            <option value="destination">Destinasi</option>
                            <option value="tour">Paket Tour</option>
                            <option value="blog">Artikel Blog</option>
                            <option value="hero">Banner / Hero</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul Asset</label>
                        <input type="text" name="title" class="form-control" placeholder="Default dari nama berkas jika kosong">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 0.75rem; margin-bottom: 0;">
                    <label class="form-label">Alt Text (Deskripsi Aksesibilitas Gambar)</label>
                    <input type="text" name="alt_text" class="form-control" placeholder="Misal: Pemandangan matahari terbit di Bromo">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <a href="dashboard.php?page=assets" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" id="btnSubmit">
                    Unggah Gambar Sekarang
                </button>
            </div>
        </form>
    </div>
</main>

<script>
function switchUploadMode(mode) {
    const tabFile = document.getElementById('tabUploadFile');
    const tabUrl = document.getElementById('tabUploadUrl');
    const secFile = document.getElementById('sectionFileUpload');
    const secUrl = document.getElementById('sectionUrlUpload');
    const urlInput = document.getElementById('imageUrlInput');

    if (mode === 'file') {
        tabFile.style.color = 'var(--color-accent)';
        tabFile.style.borderBottom = '2px solid var(--color-accent)';
        tabUrl.style.color = 'var(--text-muted)';
        tabUrl.style.borderBottom = 'none';
        secFile.style.display = 'block';
        secUrl.style.display = 'none';
        urlInput.value = '';
    } else {
        tabUrl.style.color = 'var(--color-accent)';
        tabUrl.style.borderBottom = '2px solid var(--color-accent)';
        tabFile.style.color = 'var(--text-muted)';
        tabFile.style.borderBottom = 'none';
        secUrl.style.display = 'block';
        secFile.style.display = 'none';
        document.getElementById('fileInput').value = '';
        document.getElementById('filePreviewContainer').style.display = 'none';
    }
}

function handleFileSelect(files) {
    const container = document.getElementById('filePreviewContainer');
    const previewList = document.getElementById('previewList');
    previewList.innerHTML = '';

    if (!files || files.length === 0) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';

    Array.from(files).forEach(file => {
        const item = document.createElement('div');
        item.style.cssText = 'background: #1a1d24; border-radius: 6px; padding: 0.5rem; text-align: center; border: 1px solid var(--border-color);';

        const reader = new FileReader();
        reader.onload = function(e) {
            item.innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 90px; object-fit: cover; border-radius: 4px; margin-bottom: 0.4rem;">
                <div style="font-size: 0.75rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${file.name}</div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">${(file.size / 1024).toFixed(1)} KB</div>
            `;
        };
        reader.readAsDataURL(file);
        previewList.appendChild(item);
    });
}

function previewUrlImage(url) {
    const container = document.getElementById('urlPreviewContainer');
    const img = document.getElementById('urlPreviewImg');
    if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
        img.src = url;
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// Drag and drop event listeners
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzoneArea');
    const fileInput = document.getElementById('fileInput');

    if (dropzone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.style.borderColor = 'var(--color-accent)';
                dropzone.style.background = 'rgba(79, 70, 229, 0.08)';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.style.borderColor = 'var(--border-color)';
                dropzone.style.background = 'rgba(255, 255, 255, 0.02)';
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files);
            }
        }, false);
    }

    // Copy URL helper
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                navigator.clipboard.writeText(url).then(() => {
                    this.textContent = '✓ Copied!';
                    setTimeout(() => { this.textContent = 'Salin URL'; }, 2000);
                });
            }
        });
    });
});
</script>
