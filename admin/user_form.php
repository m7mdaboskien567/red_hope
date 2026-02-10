<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../database/config.php';

$user_id = $_GET['id'] ?? null;
$user = null;
$is_edit = false;

if ($user_id) {
    $is_edit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /redhope/admin/users.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching user");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit User' : 'Add User'; ?> | RedHope Admin</title>
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
                <div class="d-flex align-items-center gap-3">
                    <a href="/redhope/admin/users.php" class="btn-icon" style="background: #eee; color: #333;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1><?php echo $is_edit ? 'Edit User' : 'Add New User'; ?></h1>
                        <p><?php echo $is_edit ? 'Update user details and permissions' : 'Create a new user account'; ?></p>
                    </div>
                </div>
            </div>

            <div id="alertContainer"></div>

            <div class="card" style="max-width: 800px; margin-top: 20px; padding: 30px; border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <form id="userForm">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                    <?php if ($is_edit): ?>
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <?php endif; ?>

                    <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div class="col" style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">First Name</label>
                            <input type="text" name="first_name" class="form-control" required 
                                value="<?php echo $is_edit ? htmlspecialchars($user['first_name']) : ''; ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                        <div class="col" style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required
                                value="<?php echo $is_edit ? htmlspecialchars($user['last_name']) : ''; ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div class="col" style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email Address</label>
                            <input type="email" name="email" class="form-control" required
                                value="<?php echo $is_edit ? htmlspecialchars($user['email']) : ''; ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                        <div class="col" style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required
                                value="<?php echo $is_edit ? htmlspecialchars($user['phone']) : ''; ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div class="col" style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Role</label>
                            <select name="role" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                                <option value="Donor" <?php echo ($is_edit && $user['role'] === 'Donor') ? 'selected' : ''; ?>>Donor</option>
                                <option value="Hospital Admin" <?php echo ($is_edit && $user['role'] === 'Hospital Admin') ? 'selected' : ''; ?>>Hospital Admin</option>
                                <option value="Super Admin" <?php echo ($is_edit && $user['role'] === 'Super Admin') ? 'selected' : ''; ?>>Super Admin</option>
                            </select>
                        </div>
                        <div class="col" style="flex: 1;">
                             <label style="display: block; margin-bottom: 8px; font-weight: 500;">Gender</label>
                            <select name="gender" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                                <option value="Male" <?php echo ($is_edit && $user['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($is_edit && $user['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div class="col" style="flex: 1;">
                             <label style="display: block; margin-bottom: 8px; font-weight: 500;">Date of Birth</label>
                             <input type="date" name="date_of_birth" class="form-control" required
                                value="<?php echo $is_edit ? htmlspecialchars($user['date_of_birth']) : ''; ?>"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                        <div class="col" style="flex: 1;">
                        </div>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">
                            <?php echo $is_edit ? 'New Password (leave blank to keep current)' : 'Password'; ?>
                        </label>
                        <input type="password" name="password" class="form-control" 
                            <?php echo $is_edit ? '' : 'required'; ?> minlength="8"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>

                    <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                        <a href="/redhope/admin/users.php" class="btn-outline-secondary" style="margin-right: 10px; text-decoration: none; display: inline-block;">Cancel</a>
                        <button type="submit" class="btn-primary">
                            <?php echo $is_edit ? 'Update User' : 'Create User'; ?>
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
    const form = document.getElementById('userForm');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        handleFormSubmit(form, '/redhope/apis/admin/manage_users.php', (result) => {
            setTimeout(() => {
                window.location.href = '/redhope/admin/users.php';
            }, 1000);
        });
    });
});
</script>
</body>
</html>
