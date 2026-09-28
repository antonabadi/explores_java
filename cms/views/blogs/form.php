<?php

declare(strict_types=1);

$pdo = db();

// Ensure blog_categories and blog_posts tables exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        meta_title VARCHAR(255) DEFAULT NULL,
        meta_description TEXT DEFAULT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        canonical_url VARCHAR(255) DEFAULT NULL,
        content LONGTEXT NOT NULL,
        excerpt TEXT,
        featured_image VARCHAR(255),
        og_image VARCHAR(255) DEFAULT NULL,
        reading_time INT DEFAULT 0,
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        author_id INT NULL,
        category_id INT NULL,
        view_count INT DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Throwable $e) {
    // Ignore schema errors
}

// Fetch categories for dropdown
$categories = [];
try {
    $categories = $pdo->query('SELECT * FROM blog_categories ORDER BY name ASC')->fetchAll();
} catch (Throwable $e) {
    $categories = [];
}

// Helper function to handle image upload
function handleBlogImageUpload($existingImage = null) {
    if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['featured_image_file']['tmp_name'];
        $fileName = $_FILES['featured_image_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = __DIR__ . '/../../../assets/images/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $newFileName = 'blog_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $destPath = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                return 'assets/images/' . $newFileName;
            }
        }
    }

    // If no new file is uploaded, use URL text input or existing image
    $imageUrl = trim($_POST['featured_image'] ?? '');
    return $imageUrl !== '' ? $imageUrl : $existingImage;
}

$action = $_POST['action'] ?? '';
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$post = null;
if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        $post = null;
    }

    if (!$post && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        setFlash('danger', 'Blog post not found.');
        redirect('dashboard.php?page=blogs');
    }
}

$isEdit = $post !== null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = trim($_POST['status'] ?? 'draft');
        $image = handleBlogImageUpload();
        $ogImage = trim($_POST['og_image'] ?? '');
        $canonicalUrl = trim($_POST['canonical_url'] ?? '');
        $readingTimeInput = $_POST['reading_time'] ?? '';
        $wordCount = str_word_count(strip_tags($content));
        $calcReadingTime = max(1, (int) ceil($wordCount / 200));
        $readingTime = ($readingTimeInput !== '' && is_numeric($readingTimeInput)) ? (int) $readingTimeInput : $calcReadingTime;

        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'blog_posts', $title);
        $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

        if ($title === '' || $content === '') {
            setFlash('danger', 'Title and content are required.');
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO blog_posts (title, meta_title, meta_description, slug, canonical_url, excerpt, content, status, featured_image, og_image, reading_time, category_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $title,
                    $metaTitle ?: null,
                    $metaDescription ?: null,
                    $slug,
                    $canonicalUrl ?: null,
                    $excerpt ?: null,
                    $content,
                    $status,
                    $image ?: null,
                    $ogImage ?: null,
                    $readingTime,
                    $categoryId,
                    $publishedAt
                ]);
                setFlash('success', 'Blog post created successfully.');
            } catch (Throwable $e) {
                setFlash('danger', 'Failed to save blog post: ' . $e->getMessage());
            }
        }
        redirect('dashboard.php?page=blogs');
    }

    if ($action === 'update') {
        $title = trim($_POST['title'] ?? '');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = trim($_POST['status'] ?? 'draft');

        $current = null;
        if ($id > 0) {
            $existing = $pdo->prepare('SELECT status, published_at, featured_image FROM blog_posts WHERE id = ?');
            $existing->execute([$id]);
            $current = $existing->fetch() ?: null;
        }

        $image = handleBlogImageUpload($current['featured_image'] ?? null);
        $ogImage = trim($_POST['og_image'] ?? '');
        $canonicalUrl = trim($_POST['canonical_url'] ?? '');
        $readingTimeInput = $_POST['reading_time'] ?? '';
        $wordCount = str_word_count(strip_tags($content));
        $calcReadingTime = max(1, (int) ceil($wordCount / 200));
        $readingTime = ($readingTimeInput !== '' && is_numeric($readingTimeInput)) ? (int) $readingTimeInput : $calcReadingTime;

        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'blog_posts', $title, $id);

        if ($title === '' || $content === '') {
            setFlash('danger', 'Title and content are required.');
        } elseif (!$current) {
            setFlash('danger', 'Blog post not found.');
        } else {
            try {
                $publishedAt = $current['published_at'] ?? null;
                if ($status === 'published' && !$publishedAt) {
                    $publishedAt = date('Y-m-d H:i:s');
                }

                $stmt = $pdo->prepare(
                    'UPDATE blog_posts SET title = ?, meta_title = ?, meta_description = ?, slug = ?, canonical_url = ?, excerpt = ?, content = ?, status = ?, featured_image = ?, og_image = ?, reading_time = ?, category_id = ?, published_at = ? WHERE id = ?'
                );
                $stmt->execute([
                    $title,
                    $metaTitle ?: null,
                    $metaDescription ?: null,
                    $slug,
                    $canonicalUrl ?: null,
                    $excerpt ?: null,
                    $content,
                    $status,
                    $image ?: null,
                    $ogImage ?: null,
                    $readingTime,
                    $categoryId,
                    $publishedAt,
                    $id
                ]);
                setFlash('success', 'Blog post updated successfully.');
            } catch (Throwable $e) {
                setFlash('danger', 'Failed to update blog post: ' . $e->getMessage());
            }
        }
        redirect('dashboard.php?page=blogs');
    }
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title"><?= $isEdit ? 'Edit Blog Post' : 'Add Blog Post' ?></h1>
            <p class="date-indicator"><?= $isEdit ? 'Update article details and SEO configuration' : 'Create and publish a new blog article' ?></p>
        </div>
        <a href="dashboard.php?page=blogs" class="btn-secondary">
            ← Kembali ke Daftar Blog
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="blog_title">Title *</label>
                    <input type="text" id="blog_title" name="title" class="form-control" required value="<?= e($post['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="blog_slug">Slug</label>
                    <input type="text" id="blog_slug" name="slug" class="form-control"
                           placeholder="Auto-generated if empty" value="<?= e($post['slug'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="blog_category">Category</label>
                    <select id="blog_category" name="category_id" class="form-control">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($post['category_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="blog_status">Status</label>
                    <select id="blog_status" name="status" class="form-control">
                        <option value="draft" <?= (($post['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= (($post['status'] ?? '') === 'published') ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= (($post['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="blog_image_file">Upload Featured Image</label>
                    <input type="file" id="blog_image_file" name="featured_image_file" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="blog_image">Or Image URL</label>
                    <input type="text" id="blog_image" name="featured_image" class="form-control"
                           placeholder="assets/images/bromo.jpg" value="<?= e($post['featured_image'] ?? '') ?>">
                </div>

                <!-- SEO Fields -->
                <div class="form-group full-width" style="grid-column: 1 / -1; border-top: 1px solid var(--border-color); margin-top: 10px; padding-top: 15px;">
                    <strong style="color: var(--color-accent, #e67e22);">SEO Settings</strong>
                </div>
                <div class="form-group">
                    <label for="blog_meta_title">Meta Title</label>
                    <input type="text" id="blog_meta_title" name="meta_title" class="form-control"
                           placeholder="SEO Title (defaults to post title)" value="<?= e($post['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="blog_canonical_url">Canonical URL</label>
                    <input type="text" id="blog_canonical_url" name="canonical_url" class="form-control"
                           placeholder="https://exploresjava.com/blog-detail?slug=..." value="<?= e($post['canonical_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="blog_og_image">OG Image URL</label>
                    <input type="text" id="blog_og_image" name="og_image" class="form-control"
                           placeholder="OpenGraph image URL (defaults to featured image)" value="<?= e($post['og_image'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="blog_reading_time">Reading Time (minutes)</label>
                    <input type="number" id="blog_reading_time" name="reading_time" class="form-control"
                           placeholder="Auto-calculated if left 0/empty" min="0" value="<?= (int) ($post['reading_time'] ?? 0) ?: '' ?>">
                </div>
                <div class="form-group full-width" style="grid-column: 1 / -1;">
                    <label for="blog_meta_description">Meta Description</label>
                    <textarea id="blog_meta_description" name="meta_description" class="form-control" rows="2"
                              placeholder="SEO meta description summary"><?= e($post['meta_description'] ?? '') ?></textarea>
                </div>

                <div class="form-group full-width" style="grid-column: 1 / -1;">
                    <label for="blog_excerpt">Excerpt</label>
                    <textarea id="blog_excerpt" name="excerpt" class="form-control" rows="2"><?= e($post['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="form-group full-width" style="grid-column: 1 / -1;">
                    <label for="blog_content">Content *</label>
                    <textarea id="blog_content" name="content" class="form-control" rows="10" required><?= e($post['content'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="dashboard.php?page=blogs" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Blog Post' ?></button>
            </div>
        </form>
    </div>
</main>
