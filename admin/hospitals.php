<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../database/config.php';

$pending_hospitals = [];
$verified_hospitals = [];

try {
    // Get pending hospitals
    $stmt = $pdo->query("SELECT * FROM hospitals WHERE is_verified = 0 ORDER BY created_at DESC");
    $pending_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get verified hospitals
    $stmt = $pdo->query("SELECT * FROM hospitals WHERE is_verified = 1 ORDER BY name ASC");
    $verified_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospitals | RedHope</title>
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
                <a href="/redhope/admin/hospitals.php" class="nav-item active">
                    <i class="bi bi-hospital"></i>
                    <span>Hospitals</span>
                    <?php if (count($pending_hospitals) > 0): ?>
                    <span class="badge bg-danger ms-auto"><?php echo count($pending_hospitals); ?></span>
                    <?php endif; ?>
                </a>
                <a href="/redhope/admin/users.php" class="nav-item">
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
                <h1>Manage Hospitals</h1>
                <p>Verify new registrations and view verified hospitals</p>
            </div>

            <div id="alertContainer"></div>

            <div class="appointments-header">
                <a href="/redhope/admin/hospital_form.php" class="btn-primary" style="text-decoration: none;">
                    <i class="bi bi-plus-lg"></i> Add Hospital
                </a>
            </div>

            <!-- Pending Hospitals -->
            <?php if (!empty($pending_hospitals)): ?>
            <div class="recent-activity" style="border: 2px solid #ffc107; background: #fffcf5;">
                <div class="section-header">
                    <h2><i class="bi bi-exclamation-circle-fill text-warning"></i> Pending Verification</h2>
                </div>
                <div class="donations-table-wrapper">
                    <table class="donations-table">
                        <thead>
                            <tr>
                                <th>Hospital Name</th>
                                <th>City</th>
                                <th>Contact</th>
                                <th>Submitted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_hospitals as $h): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($h['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($h['address']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($h['city']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($h['contact_number']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($h['email']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($h['created_at'])); ?></td>
                                <td>
                                    <div class="btn-action-group">
                                        <button class="btn-primary btn-sm btn-verify" data-id="<?php echo $h['hospital_id']; ?>">
                                            <i class="bi bi-check-circle"></i> Verify
                                        </button>
                                        <button class="btn-icon btn-edit" onclick='editHospital(<?php echo json_encode($h); ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="deleteHospital(<?php echo $h['hospital_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Verified Hospitals -->
            <div class="recent-activity">
                <div class="section-header">
                    <h2><i class="bi bi-check-circle-fill text-success"></i> Verified Hospitals</h2>
                </div>
                <?php if (empty($verified_hospitals)): ?>
                    <p class="text-muted">No verified hospitals found.</p>
                <?php else: ?>
                <div class="donations-table-wrapper">
                    <table class="donations-table">
                        <thead>
                            <tr>
                                <th>Hospital Name</th>
                                <th>City</th>
                                <th>Contact</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($verified_hospitals as $h): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($h['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($h['address']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($h['city']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($h['contact_number']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($h['email']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($h['created_at'])); ?></td>
                                <td>
                                    <div class="btn-action-group">
                                        <button class="btn-icon btn-edit" onclick='editHospital(<?php echo json_encode($h); ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="deleteHospital(<?php echo $h['hospital_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
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
    // Verify Button
    document.querySelectorAll('.btn-verify').forEach(btn => {
        btn.addEventListener('click', async () => {
            const hospitalId = btn.dataset.id;
            if (!confirm('Are you sure you want to verify this hospital?')) return;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';
            btn.disabled = true;

            try {
                const response = await fetch('/redhope/apis/admin/manage_hospitals.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'verify', hospital_id: hospitalId })
                });
                const result = await response.json();
                if (result.success) location.reload();
                else {
                    showToast(result.message, 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                showToast('An error occurred', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });
});

function deleteHospital(hospitalId) {
    if (confirm('Are you sure you want to delete this hospital? This action cannot be undone.')) {
        fetch('/redhope/apis/admin/manage_hospitals.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', hospital_id: hospitalId })
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
