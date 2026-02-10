<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../database/config.php';

$users = [];
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | RedHope</title>
    <?php include __DIR__ . '/../includes/meta.php'; ?>
    <link rel="stylesheet" href="/redhope/assets/css/global.css">
    <link rel="stylesheet" href="/redhope/assets/css/profile.css">
    <link rel="stylesheet" href="/redhope/assets/css/admin.css">
</head>
<body>
<?php include __DIR__ . '/../includes/loader.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="dashboard-wrapper">
    <div class="dashboard-container">
        <aside class="dashboard-sidebar admin-sidebar">
            <div class="sidebar-profile">
                <div class="profile-avatar">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="profile-name">Super Admin</h3>
                <span class="profile-role">System Administrator</span>
            </div>
            <nav class="sidebar-nav">
                <a href="/redhope/admin/" class="nav-item">
                    <i class="bi bi-speedometer2"></i>
                    <span>Overview</span>
                </a>
                <a href="/redhope/admin/hospitals.php" class="nav-item">
                    <i class="bi bi-hospital"></i>
                    <span>Hospitals</span>
                </a>
                <a href="/redhope/admin/users.php" class="nav-item active">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
                <a href="/redhope/admin/centers.php" class="nav-item">
                    <i class="bi bi-geo-alt"></i>
                    <span>Blood Centers</span>
                </a>
            </nav>
            <div class="sidebar-footer" style="border-top-color: #333;">
                <a href="/redhope/apis/logout.php" class="logout-btn">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <section class="dashboard-content">
            <div class="content-header">
                <h1>Manage Users</h1>
                <p>View, search, and manage platform users</p>
            </div>

            <div id="alertContainer"></div>

            <div class="appointments-header">
                <div class="user-filter-bar" style="margin-bottom: 0;">
                    <button class="user-filter-btn active" data-filter="all">All Users</button>
                    <button class="user-filter-btn" data-filter="Donor">Donors</button>
                    <button class="user-filter-btn" data-filter="Hospital Admin">Hospital Admins</button>
                    <button class="user-filter-btn" data-filter="Super Admin">System Admins</button>
                </div>
                <a href="/redhope/admin/user_form.php" class="btn-primary" style="text-decoration: none;">
                    <i class="bi bi-person-plus"></i> Add User
                </a>
            </div>

            <div class="donations-table-wrapper">
                <table class="donations-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php foreach ($users as $user): ?>
                        <tr data-role="<?php echo $user['role']; ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong><br>
                                <small class="text-muted">ID: #<?php echo $user['user_id']; ?></small>
                            </td>
                            <td>
                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $user['role'])); ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['email']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-action-group">
                                    <a href="/redhope/admin/user_form.php?id=<?php echo $user['user_id']; ?>" class="btn-icon btn-edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                    <button class="btn-icon btn-delete" onclick="deleteUser(<?php echo $user['user_id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<!-- Modals removed -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="/redhope/assets/js/global.js"></script>
<script src="/redhope/assets/js/admin.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Filter logic
    const filterBtns = document.querySelectorAll('.user-filter-btn');
    const tableBody = document.getElementById('usersTableBody');
    const rows = tableBody.querySelectorAll('tr');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.dataset.filter;

            rows.forEach(row => {
                if (filter === 'all' || row.dataset.role === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch('/redhope/apis/admin/manage_users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', user_id: userId })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                showToast(result.message, 'error');
            }
        });
    }
}
</script>
</body>
</html>
