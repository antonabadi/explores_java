<?php
$pdo = db();

// Auto-initialize blog tables and seed initial records if not existing/empty
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
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT NOT NULL,
        excerpt TEXT,
        featured_image VARCHAR(255),
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        author_id INT NULL,
        category_id INT NULL,
        view_count INT DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $catCount = (int) $pdo->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
    if ($catCount === 0) {
        $pdo->exec("INSERT INTO blog_categories (id, name, slug, description) VALUES
            (1, 'Tips', 'tips', 'Travel tips and advice'),
            (2, 'Guide', 'guide', 'Destination guides'),
            (3, 'Nature', 'nature', 'Nature and outdoor adventure'),
            (4, 'Culture', 'culture', 'Local culture and heritage')");
    }

    $postCount = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    if ($postCount === 0) {
        $pdo->exec("INSERT INTO blog_posts (id, title, slug, excerpt, content, status, category_id, featured_image, published_at, created_at) VALUES
            (1, 'Best Time to Visit Mount Bromo and What to Expect', 'best-time-to-visit-mount-bromo-and-what-to-expect', 'A complete guide on the optimal weather conditions and sunrise spots.', 'Full content about Mount Bromo travel advice.', 'published', 1, 'assets/images/bromo.jpg', '2024-05-10 10:00:00', '2024-05-10 10:00:00'),
            (2, 'Complete Travel Guide to Yogyakarta', 'complete-travel-guide-to-yogyakarta', 'Discover historical temples, culinary delights, and local arts in Jogja.', 'Full content about Yogyakarta destination overview.', 'published', 2, 'assets/images/temple.jpg', '2024-05-05 14:30:00', '2024-05-05 14:30:00')");
    }
} catch (Throwable $e) {
    // Ignore schema auto-creation errors if table already setup differently
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

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

    // Jika tidak ada file baru diunggah, gunakan input teks URL atau gambar lama
    $imageUrl = trim($_POST['featured_image'] ?? '');
    return $imageUrl !== '' ? $imageUrl : $existingImage;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = trim($_POST['status'] ?? 'draft');
        $image = handleBlogImageUpload();
        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'blog_posts', $title);
        $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

        if ($title === '' || $content === '') {
            setFlash('danger', 'Title and content are required.');
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO blog_posts (title, slug, excerpt, content, status, featured_image, category_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$title, $slug, $excerpt ?: null, $content, $status, $image ?: null, $categoryId, $publishedAt]);
                setFlash('success', 'Blog post created successfully.');
            } catch (Throwable $e) {
                setFlash('danger', 'Failed to save blog post: ' . $e->getMessage());
            }
        }
        redirect('dashboard.php?page=blogs');
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
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
                    'UPDATE blog_posts SET title = ?, slug = ?, excerpt = ?, content = ?, status = ?, featured_image = ?, category_id = ?, published_at = ? WHERE id = ?'
                );
                $stmt->execute([$title, $slug, $excerpt ?: null, $content, $status, $image ?: null, $categoryId, $publishedAt, $id]);
                setFlash('success', 'Blog post updated successfully.');
            } catch (Throwable $e) {
                setFlash('danger', 'Failed to update blog post: ' . $e->getMessage());
            }
        }
        redirect('dashboard.php?page=blogs');
    }
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    try {
        $pdo->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$id]);
        setFlash('success', 'Blog post deleted.');
    } catch (Throwable $e) {
        setFlash('danger', 'Failed to delete blog post: ' . $e->getMessage());
    }
    redirect('dashboard.php?page=blogs');
}

// Fetch blog posts
$posts = [];
try {
    $posts = $pdo->query(
        'SELECT p.*, c.name AS category_name
         FROM blog_posts p
         LEFT JOIN blog_categories c ON c.id = p.category_id
         ORDER BY p.created_at DESC'
    )->fetchAll();
} catch (Throwable $e) {
    $posts = [];
}

// Fetch categories for form select option
$categories = [];
try {
    $categories = $pdo->query('SELECT * FROM blog_categories ORDER BY name ASC')->fetchAll();
} catch (Throwable $e) {
    $categories = [];
}

$editItem = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($posts as $p) {
        if ((int)$p['id'] === $editId) {
            $editItem = $p;
            break;
        }
    }
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Blog Posts</h1>
            <p class="date-indicator">Manage blog articles and stories</p>
        </div>
        <button type="button" class="btn-primary" id="btnAddBlog">
            + Add Blog Post
        </button>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <?php if ($posts): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $p): ?>
                            <tr>
                                <td><?= (int) $p['id'] ?></td>
                                <td class="fw-bold">
                                    <?= e($p['title']) ?>
                                    <br>
                                    <small><code class="code-badge"><?= e($p['slug']) ?></code></small>
                                </td>
                                <td><?= e($p['category_name'] ?? 'Uncategorized') ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($p['status'] ?? 'draft') {
                                        'published' => 'badge-success',
                                        'archived' => 'badge-muted',
                                        default => 'badge-warning',
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= e(ucfirst($p['status'] ?? 'draft')) ?></span>
                                </td>
                                <td><?= formatDate($p['created_at'] ?? null) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="../blog-detail?slug=<?= urlencode($p['slug']) ?>"
                                           target="_blank"
                                           class="btn-icon btn-view" title="Visit Blog Page">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <button type="button"
                                                class="btn-icon btn-edit btn-edit-blog"
                                                title="Edit"
                                                data-id="<?= (int) $p['id'] ?>"
                                                data-title="<?= e($p['title']) ?>"
                                                data-slug="<?= e($p['slug']) ?>"
                                                data-category_id="<?= (int) ($p['category_id'] ?? 0) ?>"
                                                data-status="<?= e($p['status'] ?? 'draft') ?>"
                                                data-featured_image="<?= e($p['featured_image'] ?? '') ?>"
                                                data-excerpt="<?= e($p['excerpt'] ?? '') ?>"
                                                data-content="<?= e($p['content'] ?? '') ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <a href="dashboard.php?page=blogs&action=delete&id=<?= (int) $p['id'] ?>"
                                           class="btn-icon btn-delete" title="Delete"
                                           data-confirm="Delete blog post &quot;<?= e($p['title']) ?>&quot;?">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">No blog posts found. Create your first article.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="blogModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="blogModalTitle">Add Blog Post</h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post" id="blogForm" enctype="multipart/form-data">
            <input type="hidden" name="action" id="blog_action" value="create">
            <input type="hidden" name="id" id="blog_id" value="">
            <div class="form-grid">
                <div class="form-group">
                    <label for="blog_title">Title *</label>
                    <input type="text" id="blog_title" name="title" class="form-control" required value="">
                </div>
                <div class="form-group">
                    <label for="blog_slug">Slug</label>
                    <input type="text" id="blog_slug" name="slug" class="form-control"
                           placeholder="Auto-generated if empty" value="">
                </div>
                <div class="form-group">
                    <label for="blog_category">Category</label>
                    <select id="blog_category" name="category_id" class="form-control">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>">
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="blog_status">Status</label>
                    <select id="blog_status" name="status" class="form-control">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="blog_image_file">Upload Featured Image</label>
                    <input type="file" id="blog_image_file" name="featured_image_file" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="blog_image">Or Image URL</label>
                    <input type="text" id="blog_image" name="featured_image" class="form-control"
                           placeholder="assets/images/bromo.jpg" value="">
                </div>
                <div class="form-group full-width">
                    <label for="blog_excerpt">Excerpt</label>
                    <textarea id="blog_excerpt" name="excerpt" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group full-width">
                    <label for="blog_content">Content *</label>
                    <textarea id="blog_content" name="content" class="form-control" rows="4" required></textarea>
                </div>
                <div class="modal-actions full-width">
                    <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn-submit" id="blogSubmitBtn">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnAddBlog = document.getElementById('btnAddBlog');
    const blogForm = document.getElementById('blogForm');
    const blogModalTitle = document.getElementById('blogModalTitle');
    const blogAction = document.getElementById('blog_action');
    const blogId = document.getElementById('blog_id');
    const blogSubmitBtn = document.getElementById('blogSubmitBtn');

    if (btnAddBlog) {
        btnAddBlog.addEventListener('click', () => {
            blogForm.reset();
            blogAction.value = 'create';
            blogId.value = '';
            blogModalTitle.textContent = 'Add Blog Post';
            blogSubmitBtn.textContent = 'Create';
            openModal('blogModal');
        });
    }

    document.querySelectorAll('.btn-edit-blog').forEach(btn => {
        btn.addEventListener('click', () => {
            blogForm.reset();
            blogAction.value = 'update';
            blogId.value = btn.dataset.id || '';
            document.getElementById('blog_title').value = btn.dataset.title || '';
            document.getElementById('blog_slug').value = btn.dataset.slug || '';
            document.getElementById('blog_category').value = btn.dataset.category_id || '';
            document.getElementById('blog_status').value = btn.dataset.status || 'draft';
            document.getElementById('blog_image').value = btn.dataset.featured_image || '';
            document.getElementById('blog_excerpt').value = btn.dataset.excerpt || '';
            document.getElementById('blog_content').value = btn.dataset.content || '';

            blogModalTitle.textContent = 'Edit Blog Post';
            blogSubmitBtn.textContent = 'Update';
            openModal('blogModal');
        });
    });
});
</script>
