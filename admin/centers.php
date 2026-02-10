<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../database/config.php';

$centers = [];

try {
    $stmt = $pdo->query("SELECT * FROM blood_centers ORDER BY name ASC");
    $centers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blood Centers | RedHope</title>
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
                <a href="/redhope/admin/users.php" class="nav-item">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
                <a href="/redhope/admin/centers.php" class="nav-item active">
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
                <h1>Blood Centers</h1>
                <p>Manage blood donation centers locations</p>
            </div>

            <div id="alertContainer"></div>

            <div class="appointments-header">
                <a href="/redhope/admin/center_form.php" class="btn-primary" style="text-decoration: none;">
                    <i class="bi bi-plus-lg"></i> Add New Center
                </a>
            </div>

            <div class="donations-table-wrapper">
                <table class="donations-table">
                    <thead>
                        <tr>
                            <th>Center Name</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($centers as $c): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($c['address']); ?></td>
                            <td><?php echo htmlspecialchars($c['city']); ?></td>
                            <td>
                                <div class="action-btn-group">
                                    <a href="/redhope/admin/center_form.php?id=<?php echo $c['center_id']; ?>" class="btn-sm btn-outline-primary" style="margin-right: 5px; text-decoration: none;">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn-sm btn-outline-danger btn-delete" data-id="<?php echo $c['center_id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<div class="toast-container"></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="/redhope/assets/js/global.js"></script>
<script src="/redhope/assets/js/admin.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Handle delete
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to delete this center?')) return;
            
            const id = btn.dataset.id;
            try {
                const response = await fetch('/redhope/apis/admin/manage_center.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', center_id: id })
                });
                const result = await response.json();
                if (result.success) location.reload();
                else showToast(result.message, 'error');
            } catch (error) {
                showToast('Error deleting center', 'error');
            }
        });
    });
});
</script>
</body>
</html>
