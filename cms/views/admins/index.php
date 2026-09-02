<?php
$pdo = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$currentAdminId = $_SESSION['admin_id'] ?? 0;

$roles = [
    'superadmin'    => 'Super Admin',
    'admin'         => 'Administrator',
    'admin-content' => 'Content Admin',
    'admin-tour'    => 'Tour Admin',
    'admin-booking' => 'Booking Admin',
    'moderator'     => 'Moderator',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'admin-content';

        if ($fullname === '' || $username === '' || $email === '' || $password === '') {
            setFlash('danger', 'Nama lengkap, username, email, dan password wajib diisi.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('danger', 'Format email tidak valid.');
        } elseif (strlen($password) < 8) {
            setFlash('danger', 'Password minimal harus 8 karakter.');
        } elseif (!array_key_exists($role, $roles)) {
            setFlash('danger', 'Role yang dipilih tidak valid.');
        } else {
            $chkUser = $pdo->prepare('SELECT id FROM admins WHERE username = ?');
            $chkUser->execute([$username]);
            if ($chkUser->fetch()) {
                setFlash('danger', 'Username sudah digunakan.');
            } else {
                $chkEmail = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
                $chkEmail->execute([$email]);
                if ($chkEmail->fetch()) {
                    setFlash('danger', 'Email sudah terdaftar.');
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO admins (fullname, username, email, password, role) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$fullname, $username, $email, $hashedPassword, $role]);
                    setFlash('success', 'Akun admin berhasil ditambahkan.');
                }
            }
        }
        redirect('dashboard.php?page=admins');
    }

    if ($action === 'update') {
        $id       = (int) ($_POST['id'] ?? 0);
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'admin-content';

        if ($id <= 0 || $fullname === '' || $username === '' || $email === '') {
            setFlash('danger', 'Nama lengkap, username, dan email wajib diisi.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('danger', 'Format email tidak valid.');
        } elseif ($password !== '' && strlen($password) < 8) {
            setFlash('danger', 'Password baru minimal harus 8 karakter.');
        } elseif (!array_key_exists($role, $roles)) {
            setFlash('danger', 'Role yang dipilih tidak valid.');
        } else {
            $chkUser = $pdo->prepare('SELECT id FROM admins WHERE username = ? AND id != ?');
            $chkUser->execute([$username, $id]);
            if ($chkUser->fetch()) {
                setFlash('danger', 'Username sudah digunakan oleh akun lain.');
            } else {
                $chkEmail = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id != ?');
                $chkEmail->execute([$email, $id]);
                if ($chkEmail->fetch()) {
                    setFlash('danger', 'Email sudah terdaftar untuk akun lain.');
                } else {
                    if ($password !== '') {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE admins SET fullname = ?, username = ?, email = ?, password = ?, role = ? WHERE id = ?');
                        $stmt->execute([$fullname, $username, $email, $hashedPassword, $role, $id]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE admins SET fullname = ?, username = ?, email = ?, role = ? WHERE id = ?');
                        $stmt->execute([$fullname, $username, $email, $role, $id]);
                    }
                    setFlash('success', 'Akun admin berhasil diperbarui.');
                }
            }
        }
        redirect('dashboard.php?page=admins');
    }
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id === (int) $currentAdminId) {
        setFlash('danger', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
    } else {
        $stmt = $pdo->prepare('DELETE FROM admins WHERE id = ?');
        $stmt->execute([$id]);
        setFlash('success', 'Akun admin berhasil dihapus.');
    }
    redirect('dashboard.php?page=admins');
}

$roleFilter = $_GET['role'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

$sql = 'SELECT id, username, email, fullname, role, created_at, updated_at FROM admins WHERE 1=1';
$params = [];

if ($roleFilter !== 'all' && array_key_exists($roleFilter, $roles)) {
    $sql .= ' AND role = ?';
    $params[] = $roleFilter;
}

if ($searchQuery !== '') {
    $sql .= ' AND (fullname LIKE ? OR username LIKE ? OR email LIKE ?)';
    $searchTerm = '%' . $searchQuery . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= ' ORDER BY fullname ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$admins = $stmt->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id, fullname, username, email, role FROM admins WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}

function roleBadge(string $role): string
{
    $badges = [
        'superadmin'    => 'badge-danger',
        'admin'         => 'badge-info',
        'admin-content' => 'badge-success',
        'admin-tour'    => 'badge-warning',
        'admin-booking' => 'badge-info',
        'moderator'     => 'badge-muted',
    ];
    $class = $badges[$role] ?? 'badge-muted';
    $label = ucwords(str_replace('-', ' ', $role));
    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Admin Accounts</h1>
            <p class="date-indicator">Manajemen akun pengelola dan hak akses CMS</p>
        </div>
        <button type="button" class="btn-primary" data-modal-open="adminModal"
                <?= $editItem ? '' : 'data-reset-form="adminForm"' ?>>
            + Add Admin
        </button>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card filter-bar">
        <form method="get" class="filter-form">
            <input type="hidden" name="page" value="admins">
            <div class="form-group">
                <label for="filter_q">Search</label>
                <input type="text" id="filter_q" name="q" class="form-control"
                       placeholder="Cari nama, username, email..."
                       value="<?= e($searchQuery) ?>">
            </div>
            <div class="form-group">
                <label for="filter_role">Role</label>
                <select id="filter_role" name="role" class="form-control">
                    <option value="all">Semua Role</option>
                    <?php foreach ($roles as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $roleFilter === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn-primary" style="padding: 0.55rem 1rem;">Filter</button>
                <?php if ($searchQuery !== '' || $roleFilter !== 'all'): ?>
                    <a href="dashboard.php?page=admins" class="btn-secondary" style="padding: 0.55rem 1rem; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="glass-card">
        <?php if ($admins): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $a): ?>
                            <?php
                            $isSelf = (int) $a['id'] === (int) $currentAdminId;
                            $initial = strtoupper(substr($a['fullname'] ?: $a['username'], 0, 1));
                            ?>
                            <tr>
                                <td><?= (int) $a['id'] ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div class="user-avatar" style="width: 34px; height: 34px; font-size: 0.85rem; flex-shrink: 0; background: var(--color-accent); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                            <?= e($initial) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                <?= e($a['fullname']) ?>
                                                <?php if ($isSelf): ?>
                                                    <span class="badge badge-info" style="font-size: 0.65rem; padding: 0.1rem 0.4rem; margin-left: 0.25rem;">You</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted text-sm">@<?= e($a['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($a['email']) ?></td>
                                <td><?= roleBadge($a['role']) ?></td>
                                <td><?= formatDate($a['created_at']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="dashboard.php?page=admins&edit=<?= (int) $a['id'] ?>"
                                           class="btn-icon btn-edit" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php if (!$isSelf): ?>
                                            <a href="dashboard.php?page=admins&action=delete&id=<?= (int) $a['id'] ?>"
                                               class="btn-icon btn-delete" title="Delete"
                                               data-confirm="Hapus akun admin &quot;<?= e($a['fullname']) ?>&quot;?">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">Tidak ada akun admin yang ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="adminModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Admin Account' : 'Add Admin Account' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post" id="adminForm">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="adm_fullname">Full Name *</label>
                    <input type="text" id="adm_fullname" name="fullname" class="form-control" required
                           placeholder="Nama Lengkap"
                           value="<?= e($editItem['fullname'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="adm_username">Username *</label>
                    <input type="text" id="adm_username" name="username" class="form-control" required
                           placeholder="Username"
                           value="<?= e($editItem['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="adm_email">Email *</label>
                    <input type="email" id="adm_email" name="email" class="form-control" required
                           placeholder="email@example.com"
                           value="<?= e($editItem['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="adm_role">Role *</label>
                    <select id="adm_role" name="role" class="form-control" required>
                        <?php foreach ($roles as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($editItem['role'] ?? 'admin-content') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="adm_password">
                    Password <?= $editItem ? '<span class="text-muted text-sm">(Kosongkan jika tidak ingin mengubah)</span>' : '*' ?>
                </label>
                <input type="password" id="adm_password" name="password" class="form-control"
                       placeholder="<?= $editItem ? 'Password baru (opsional)' : 'Minimal 8 karakter' ?>"
                       <?= $editItem ? '' : 'required' ?> minlength="8">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-submit"><?= $editItem ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<script>document.addEventListener('DOMContentLoaded', () => openModal('adminModal'));</script>
<?php endif; ?>
