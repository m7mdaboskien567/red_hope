<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../database/config.php';

$stats = [
    'total_users' => 0,
    'total_hospitals' => 0,
    'pending_hospitals' => 0,
    'total_donations' => 0,
    'users_breakdown' => ['Donor' => 0, 'Hospital Admin' => 0, 'Super Admin' => 0],
    'hospitals_breakdown' => ['verified' => 0, 'pending' => 0]
];

try {
    // 1. User Stats
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stats['users_breakdown'][$row['role']] = $row['count'];
        $stats['total_users'] += $row['count'];
    }

    // 2. Hospital Stats
    $stmt = $pdo->query("SELECT is_verified, COUNT(*) as count FROM hospitals GROUP BY is_verified");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['is_verified'] == 1) {
            $stats['hospitals_breakdown']['verified'] = $row['count'];
        } else {
            $stats['hospitals_breakdown']['pending'] = $row['count'];
        }
    }
    $stats['total_hospitals'] = $stats['hospitals_breakdown']['verified'] + $stats['hospitals_breakdown']['pending'];
    $stats['pending_hospitals'] = $stats['hospitals_breakdown']['pending'];

    // 3. Donation Stats
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM donations");
    $donation_count = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_donations'] = $donation_count ? $donation_count['count'] : 0;

    // 4. All Users (for Users Tab)
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. All Hospitals (for Hospitals Tab)
    $stmt = $pdo->query("SELECT h.*, u.first_name, u.last_name FROM hospitals h LEFT JOIN users u ON h.admin_id = u.user_id ORDER BY h.created_at DESC");
    $all_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. All Blood Centers (for Centers Tab)
    $stmt = $pdo->query("SELECT * FROM blood_centers ORDER BY name ASC");
    $all_centers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | RedHope</title>
    <?php include __DIR__ . '/../includes/meta.php'; ?>
    <link rel="stylesheet" href="/redhope/assets/css/global.css">
    <link rel="stylesheet" href="/redhope/assets/css/profile.css">
    <link rel="stylesheet" href="/redhope/assets/css/admin.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="javascript:void(0)" onclick="loadSection('overview')" class="nav-item active" id="nav-overview">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Overview</span>
                </a>
                <a href="javascript:void(0)" onclick="loadSection('hospitals')" class="nav-item" id="nav-hospitals">
                    <i class="bi bi-hospital"></i>
                    <span>Hospitals</span>
                    <?php if ($stats['pending_hospitals'] > 0): ?>
                    <span class="badge bg-danger ms-auto"><?php echo $stats['pending_hospitals']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="javascript:void(0)" onclick="loadSection('users')" class="nav-item" id="nav-users">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
                <a href="javascript:void(0)" onclick="loadSection('centers')" class="nav-item" id="nav-centers">
                    <i class="bi bi-geo-alt"></i>
                    <span>Blood Centers</span>
                </a>
            </nav>
            <div class="sidebar-footer" style="border-top-color: rgba(255,255,255,0.1);">
                <a href="/redhope/apis/logout.php" class="logout-btn">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <section class="dashboard-content">
            <!-- Overview Section -->
            <div id="overview-section" class="dashboard-section">
                <div class="content-header">
                    <div>
                        <h1>Dashboard Overview</h1>
                        <p>Welcome back, Admin. Here's what's happening today.</p>
                    </div>
                    <div class="date-widget" style="color: var(--admin-text-light); font-weight: 500;">
                        <i class="bi bi-calendar3"></i> <?php echo date('F j, Y'); ?>
                    </div>
                </div>

                <!-- 1. Key Metrics Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_users']); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon hospitals">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_hospitals']); ?></h3>
                            <p>Hospitals</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon pending-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['pending_hospitals']); ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon donations">
                            <i class="bi bi-droplet-fill"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_donations']); ?></h3>
                            <p>Donations</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="user_dashboard" style="margin-top: 30px; padding: 2rem; background: transparent; box-shadow: none; border: none;">
                    <div class="actions-text" style="height: auto; margin-bottom: 30px;">
                        <h2 style="font-size: 2rem;">Quick Actions</h2>
                    </div>
                    <div class="nav-links-btns">
                        <div>
                            <span onclick="loadSection('users')">
                                <i class="bi bi-people-fill"></i>
                                <p>Manage Users</p>
                            </span>
                        </div>
                        <div>
                            <span onclick="loadSection('hospitals')">
                                <i class="bi bi-hospital"></i>
                                <p>Hospitals</p>
                            </span>
                        </div>
                        <div>
                            <span onclick="loadSection('centers')">
                                <i class="bi bi-geo-alt-fill"></i>
                                <p>Blood Centers</p>
                            </span>
                        </div>
                        <div>
                            <span onclick="showAlert('System logs and settings under maintenance', 'info')">
                                <i class="bi bi-gear-fill"></i>
                                <p>Settings</p>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. Charts Grid -->
                <div class="charts-grid">
                    <!-- User Distribution -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>User Distribution</h3>
                            <button class="btn-icon"><i class="bi bi-three-dots"></i></button>
                        </div>
                        <div class="chart-container">
                            <canvas id="userDistributionChart"></canvas>
                        </div>
                    </div>

                    <!-- Donation Trends -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Donation Activity</h3>
                            <select style="border: none; background: #f8f9fa; padding: 5px; border-radius: 6px; color: #6c757d;">
                                <option>Last 6 Months</option>
                                <option>Last Year</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="donationActivityChart"></canvas>
                        </div>
                    </div>

                    <!-- Hospital Status -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Hospital Status</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="hospitalStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 3. Recent Activity -->
                <div class="recent-activity">
                    <div class="content-header">
                        <h2>Recent Registrations</h2>
                        <a href="javascript:void(0)" onclick="loadSection('users')" style="color: var(--admin-primary); text-decoration: none; font-weight: 500;">View All</a>
                    </div>
                    <div class="donations-table-wrapper">
                        <table class="donations-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_users_slice = array_slice($all_users, 0, 5);
                                if (!empty($recent_users_slice)): 
                                ?>
                                <?php foreach ($recent_users_slice as $u): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle" style="width: 32px; height: 32px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #555; font-weight: 600;">
                                                <?php echo strtoupper(substr($u['first_name'], 0, 1)); ?>
                                            </div>
                                            <strong><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $u['role'])); ?>">
                                            <?php echo $u['role']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr><td colspan="4" class="text-center">No recent users found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Hospitals Section -->
            <div id="hospitals-section" class="dashboard-section" style="display: none;">
                <div class="content-header">
                    <h1>Hospital Management</h1>
                    <button class="btn-primary" onclick="openHospitalForm()">
                        <i class="bi bi-plus-lg"></i> Add Hospital
                    </button>
                </div>
                <div class="donations-table-wrapper">
                    <table class="donations-table">
                        <thead>
                            <tr>
                                <th>Hospital Name</th>
                                <th>Admin</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_hospitals as $h): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($h['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($h['first_name'] . ' ' . $h['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($h['city']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $h['is_verified'] ? 'approved' : 'pending'; ?>">
                                        <?php echo $h['is_verified'] ? 'Verified' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editHospital(<?php echo $h['hospital_id']; ?>)"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteHospital(<?php echo $h['hospital_id']; ?>)"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users-section" class="dashboard-section" style="display: none;">
                <div class="content-header">
                    <h1>User Management</h1>
                    <button class="btn-primary" onclick="openUserForm()">
                        <i class="bi bi-plus-lg"></i> Add User
                    </button>
                </div>
                <div class="donations-table-wrapper">
                    <table class="donations-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $u['role'])); ?>"><?php echo $u['role']; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $u['user_id']; ?>)"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $u['user_id']; ?>)"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Centers Section -->
            <div id="centers-section" class="dashboard-section" style="display: none;">
                <div class="content-header">
                    <h1>Blood Centers</h1>
                    <button class="btn-primary" onclick="openCenterForm()">
                        <i class="bi bi-plus-lg"></i> Add Center
                    </button>
                </div>
                <div class="donations-table-wrapper">
                    <table class="donations-table">
                        <thead>
                            <tr>
                                <th>Center Name</th>
                                <th>City</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_centers as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['city']); ?></td>
                                <td><?php echo htmlspecialchars($c['address']); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editCenter(<?php echo $c['center_id']; ?>)"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCenter(<?php echo $c['center_id']; ?>)"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Data for JS -->
<script>
    window.dashboardData = {
        users: {
            Donor: <?php echo $stats['users_breakdown']['Donor']; ?>,
            HospitalAdmin: <?php echo $stats['users_breakdown']['Hospital Admin']; ?>,
            SuperAdmin: <?php echo $stats['users_breakdown']['Super Admin']; ?>
        },
        hospitals: {
            verified: <?php echo $stats['hospitals_breakdown']['verified']; ?>,
            pending: <?php echo $stats['hospitals_breakdown']['pending']; ?>
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'overview';
        loadSection(tab);
    });

    function loadSection(sectionId) {
        // Hide all sections
        const sections = document.querySelectorAll('.dashboard-section');
        sections.forEach(s => s.style.display = 'none');

        // Show target section
        const targetSection = document.getElementById(sectionId + '-section');
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // Update navigation active state
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => item.classList.remove('active'));
        
        const activeNav = document.getElementById('nav-' + sectionId);
        if (activeNav) {
            activeNav.classList.add('active');
        }

        // Update URL without reload
        const newUrl = window.location.pathname + '?tab=' + sectionId;
        window.history.pushState({ path: newUrl }, '', newUrl);

        // Re-initialize charts if on overview
        if (sectionId === 'overview' && typeof initAdminCharts === 'function') {
            initAdminCharts();
        }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="/redhope/assets/js/global.js"></script>
<script src="/redhope/assets/js/admin_charts.js"></script>
</body>
</html>
