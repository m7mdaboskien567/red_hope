<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../database/config.php';

$center_id = $_GET['id'] ?? null;
$center = null;
$is_edit = false;

if ($center_id) {
    $is_edit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM blood_centers WHERE center_id = ?");
        $stmt->execute([$center_id]);
        $center = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$center) {
            header("Location: /redhope/admin/centers.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching center");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Blood Center' : 'Add Blood Center'; ?> | RedHope Admin</title>
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
                <div class="d-flex align-items-center gap-3">
                    <a href="/redhope/admin/centers.php" class="btn-icon" style="background: #eee; color: #333;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1><?php echo $is_edit ? 'Edit Blood Center' : 'Add New Blood Center'; ?></h1>
                        <p><?php echo $is_edit ? 'Update center details' : 'Register a new donation center'; ?></p>
                    </div>
                </div>
            </div>

            <div id="alertContainer"></div>

            <div class="card" style="max-width: 800px; margin-top: 20px; padding: 30px; border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <form id="centerForm">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                    <?php if ($is_edit): ?>
                    <input type="hidden" name="center_id" value="<?php echo $center['center_id']; ?>">
                    <?php endif; ?>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Center Name</label>
                        <input type="text" name="name" class="form-control" required 
                            value="<?php echo $is_edit ? htmlspecialchars($center['name']) : ''; ?>"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">City</label>
                        <input type="text" name="city" class="form-control" required
                            value="<?php echo $is_edit ? htmlspecialchars($center['city']) : ''; ?>"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Address</label>
                        <textarea name="address" class="form-control" required rows="3"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;"><?php echo $is_edit ? htmlspecialchars($center['address']) : ''; ?></textarea>
                    </div>

                    <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                        <a href="/redhope/admin/centers.php" class="btn-outline-secondary" style="margin-right: 10px; text-decoration: none; display: inline-block;">Cancel</a>
                        <button type="submit" class="btn-primary">
                            <?php echo $is_edit ? 'Update Center' : 'Create Center'; ?>
                        </button>
                    </div>
                </form>
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
    const form = document.getElementById('centerForm');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        handleFormSubmit(form, '/redhope/apis/admin/manage_center.php', (result) => {
            setTimeout(() => {
                window.location.href = '/redhope/admin/centers.php';
            }, 1000);
        });
    });
});
</script>
</body>
</html>
