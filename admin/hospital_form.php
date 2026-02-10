<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../database/config.php';

$hospital_id = $_GET['id'] ?? null;
$hospital = null;
$is_edit = false;

if ($hospital_id) {
    $is_edit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM hospitals WHERE hospital_id = ?");
        $stmt->execute([$hospital_id]);
        $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hospital) {
            header("Location: /redhope/admin/hospitals.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching hospital");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Hospital' : 'Add Hospital'; ?> | RedHope Admin</title>
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
                <div class="d-flex align-items-center gap-3">
                    <a href="/redhope/admin/hospitals.php" class="btn-icon" style="background: #eee; color: #333;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1><?php echo $is_edit ? 'Edit Hospital' : 'Add New Hospital'; ?></h1>
                        <p><?php echo $is_edit ? 'Update hospital details' : 'Register a new hospital manually'; ?></p>
                    </div>
                </div>
            </div>

            <div id="alertContainer"></div>

            <div class="card" style="max-width: 800px; margin-top: 20px; padding: 30px; border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <form id="hospitalForm">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                    <?php if ($is_edit): ?>
                    <input type="hidden" name="hospital_id" value="<?php echo $hospital['hospital_id']; ?>">
                    <?php endif; ?>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Hospital Name</label>
                        <input type="text" name="name" class="form-control" required 
                            value="<?php echo $is_edit ? htmlspecialchars($hospital['name']) : ''; ?>"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>

                    <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div class="col" style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">City</label>
                            <input type="text" name="city" class="form-control" required
                                value="<?php echo $is_edit ? htmlspecialchars($hospital['city']) : ''; ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                        <div class="col" style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Contact Number</label>
                            <input type="tel" name="contact_number" class="form-control" required
                                value="<?php echo $is_edit ? htmlspecialchars($hospital['contact_number']) : ''; ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Address</label>
                        <textarea name="address" class="form-control" required rows="3"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;"><?php echo $is_edit ? htmlspecialchars($hospital['address']) : ''; ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email Address</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?php echo $is_edit ? htmlspecialchars($hospital['email']) : ''; ?>"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>

                    <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                        <a href="/redhope/admin/hospitals.php" class="btn-outline-secondary" style="margin-right: 10px; text-decoration: none; display: inline-block;">Cancel</a>
                        <button type="submit" class="btn-primary">
                            <?php echo $is_edit ? 'Update Hospital' : 'Create Hospital'; ?>
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
    const form = document.getElementById('hospitalForm');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        handleFormSubmit(form, '/redhope/apis/admin/manage_hospitals.php', (result) => {
            setTimeout(() => {
                window.location.href = '/redhope/admin/hospitals.php';
            }, 1000);
        });
    });
});
</script>
</body>
</html>
