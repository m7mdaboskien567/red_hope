<?php
session_start();
include_once __DIR__ . '/../database/config.php';

// Auth Check — Super Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: /redhope/login.php");
    exit();
}

$user_name = 'Admin';
$total_donors = 0;
$total_hospitals = 0;
$total_donations = 0;
$open_requests = 0;
$all_users = [];
$all_hospitals = [];
$all_centers = [];
$all_requests = [];
$all_donor_profiles = [];
$all_inventory = [];
$all_messages = [];
$all_appointments = [];
$recent_activity = [];

// Chart data
$blood_type_dist = [];
$monthly_donations = [];
$urgency_dist = [];

try {
    // 1. Get Admin Name
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
    }

    // 2. Platform Stats
    $total_donors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Donor'")->fetchColumn();
    $total_hospitals = $pdo->query("SELECT COUNT(*) FROM hospitals")->fetchColumn();
    $total_donations = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'Approved'")->fetchColumn();
    $open_requests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'Open'")->fetchColumn();

    // 3. All Users
    $all_users = $pdo->query("SELECT user_id, first_name, last_name, email, phone, role, gender, date_of_birth, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 4. All Hospitals
    $all_hospitals = $pdo->query("SELECT h.*, CONCAT(u.first_name, ' ', u.last_name) as admin_name FROM hospitals h LEFT JOIN users u ON h.admin_id = u.user_id ORDER BY h.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 5. All Blood Centers
    $all_centers = $pdo->query("SELECT * FROM blood_centers ORDER BY city, name")->fetchAll(PDO::FETCH_ASSOC);

    // 6. All Blood Requests
    $all_requests = $pdo->query("
        SELECT br.*, h.name as hospital_name, h.city 
        FROM blood_requests br 
        JOIN hospitals h ON br.hospital_id = h.hospital_id 
        ORDER BY 
            CASE br.urgency_level 
                WHEN 'Emergency' THEN 1 
                WHEN 'Urgent' THEN 2 
                ELSE 3 
            END, 
            br.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 7. All Donor Profiles
    $all_donor_profiles = $pdo->query("
        SELECT dp.*, u.first_name, u.last_name, u.email 
        FROM donor_profiles dp 
        JOIN users u ON dp.donor_id = u.user_id 
        ORDER BY u.first_name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 8. All Blood Inventory
    $all_inventory = $pdo->query("
        SELECT bi.*, 
               COALESCE(bc.name, 'Unknown') as center_name,
               CONCAT(u.first_name, ' ', u.last_name) as donor_name
        FROM blood_inventory bi 
        JOIN donations d ON bi.donation_id = d.donation_id
        JOIN donor_profiles dp ON d.donor_id = dp.donor_id
        JOIN users u ON dp.donor_id = u.user_id
        LEFT JOIN blood_centers bc ON bi.current_location_id = bc.center_id
        ORDER BY bi.expiry_date ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 9. All Messages
    $all_messages = $pdo->query("
        SELECT m.*, 
               CONCAT(s.first_name, ' ', s.last_name) as sender_name,
               CONCAT(r.first_name, ' ', r.last_name) as receiver_name
        FROM messages m 
        JOIN users s ON m.sender_id = s.user_id 
        JOIN users r ON m.receiver_id = r.user_id 
        ORDER BY m.sent_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 10. All Appointments
    $all_appointments = $pdo->query("
        SELECT a.*, 
               CONCAT(u.first_name, ' ', u.last_name) as donor_name,
               bc.name as center_name, bc.city
        FROM appointments a 
        JOIN donor_profiles dp ON a.donor_id = dp.donor_id
        JOIN users u ON dp.donor_id = u.user_id
        JOIN blood_centers bc ON a.center_id = bc.center_id 
        ORDER BY a.scheduled_time DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 11. Recent Activity
    $recent_activity = $pdo->query("SELECT first_name, last_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // Urgency distribution
    $urgency_dist = $pdo->query("SELECT urgency_level, COUNT(*) as cnt FROM blood_requests GROUP BY urgency_level")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch All Users for Compose Message Dropdown (Grouped by Role)
    $all_users_grouped = $pdo->query("SELECT user_id, first_name, last_name, role FROM users WHERE role IN ('Hospital','Donor') ORDER BY role, first_name")->fetchAll(PDO::FETCH_ASSOC);
    $hospital_admins = [];
    $donors = [];
    foreach ($all_users_grouped as $u) {
        if ($u['role'] === 'Hospital') $hospital_admins[] = $u;
        else $donors[] = $u;
    }

    // Valid donations for inventory dropdown
    $valid_donations = $pdo->query("
        SELECT d.donation_id, CONCAT(u.first_name, ' ', u.last_name) as donor_name, dp.blood_type, d.donated_at
        FROM donations d
        JOIN donor_profiles dp ON d.donor_id = dp.donor_id
        JOIN users u ON dp.donor_id = u.user_id
        ORDER BY d.donation_id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user_name; ?> | Admin Dashboard</title>
    <?php include __DIR__ . '/../includes/meta.php'; ?>
    <link rel="stylesheet" href="/redhope/assets/css/profile.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include __DIR__ . "/../includes/loader.php"; ?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <section id="root">
        <section class="welcome-part">
            <div class="info-card">
                <div class="user-avatar">
                   <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h1>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>! 🛡️</h1>
                    <p class="user-role">Super Admin</p>
                    <div class="badges">
                        <span class="blood-group-badge" style="background: #e3f2fd; color: #1565c0;"><i class="bi bi-shield-lock-fill"></i> Admin Panel</span>
                        <span class="status-badge eligible">System Active</span>
                    </div>
                </div>
            </div>

            <div class="additional">
                <div class="statistics">
                    <div class="stat-box">
                        <div class="icon red"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <h3><?php echo $total_donors; ?></h3>
                            <p>Donors</p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="icon green"><i class="bi bi-hospital-fill"></i></div>
                        <div>
                            <h3><?php echo $total_hospitals; ?></h3>
                            <p>Hospitals</p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="icon purple"><i class="bi bi-droplet-fill"></i></div>
                        <div>
                            <h3><?php echo $total_donations; ?></h3>
                            <p>Donations</p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="icon" style="background: #fff3e0; color: #e65100;"><i class="bi bi-clipboard2-pulse-fill"></i></div>
                        <div>
                            <h3><?php echo $open_requests; ?></h3>
                            <p>Open Requests</p>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
                    <div style="background: var(--card-bg, #fff); border-radius: 16px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
                        <h4 style="margin-bottom: 12px; font-size: 0.95rem; color: var(--text-color, #333);">Blood Type Distribution</h4>
                        <canvas id="bloodTypeChart" height="220"></canvas>
                    </div>
                    <div style="background: var(--card-bg, #fff); border-radius: 16px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
                        <h4 style="margin-bottom: 12px; font-size: 0.95rem; color: var(--text-color, #333);">Monthly Donations</h4>
                        <canvas id="donationsChart" height="220"></canvas>
                    </div>
                    <div style="background: var(--card-bg, #fff); border-radius: 16px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
                        <h4 style="margin-bottom: 12px; font-size: 0.95rem; color: var(--text-color, #333);">Requests by Urgency</h4>
                        <canvas id="urgencyChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="user_dashboard">
            <div class="actions-text">
                <h2>Management</h2>
            </div>
            <div class="nav-links-btns" style="display: grid; grid-template-columns: repeat(4, 1fr); height: auto; gap: 15px; padding: 10px 0; width: 100%;">
            <style>
                .nav-links-btns > div { width: 100% !important; height: auto !important; }
                .nav-links-btns > div > span { width: 100% !important; }
            </style>
                <div>
                    <span onclick="loadSection('users')">
                        <i class="bi bi-people-fill"></i>
                        <p>Users</p>
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
                        <i class="bi bi-droplet-half"></i>
                        <p>Blood Centers</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('requests')">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <p>Requests</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('donors')">
                        <i class="bi bi-person-badge"></i>
                        <p>Donor Profiles</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('ai-docs')">
                        <i class="bi bi-robot"></i>
                        <p>AI DOCS</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('messages')">
                        <i class="bi bi-chat-dots-fill"></i>
                        <p>Messages</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('appointments')">
                        <i class="bi bi-calendar-check"></i>
                        <p>Appointments</p>
                    </span>
                </div>
            </div>
            <div class="view-nav">
<section id="contentFrameView">
                
                <!-- Users Section -->
                <div id="users-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h2 style="margin: 0;">All Users</h2>
                            <button class="btn btn-primary btn-sm" onclick="openModal('user')" style="font-size: 0.85rem; padding: 6px 16px;">
                                <i class="bi bi-plus-lg"></i> Add User
                            </button>
                        </div>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_users)): ?>
                                        <?php foreach ($all_users as $u): ?>
                                        <tr>
                                            <td><?php echo $u['user_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $u['role'])); ?>">
                                                    <?php echo $u['role']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button class="btn btn-warning btn-sm" onclick='openModal("user", <?php echo json_encode($u); ?>)' style="font-size: 0.75rem; padding: 4px 12px;">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                                        <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $u['user_id']; ?>)" style="font-size: 0.75rem; padding: 4px 12px;">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted small">You</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 20px; color: #888;">No users found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Hospitals Section -->
                <div id="hospitals-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h2 style="margin: 0;">All Hospitals</h2>
                            <button class="btn btn-primary btn-sm" onclick="openModal('hospital')" style="font-size: 0.85rem; padding: 6px 16px;">
                                <i class="bi bi-plus-lg"></i> Add Hospital
                            </button>
                        </div>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>City</th>
                                        <th>Admin</th>
                                        <th>Verified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_hospitals)): ?>
                                        <?php foreach ($all_hospitals as $hosp): ?>
                                        <tr>
                                            <td><?php echo $hosp['hospital_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($hosp['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($hosp['city']); ?></td>
                                            <td><?php echo htmlspecialchars($hosp['admin_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($hosp['is_verified']): ?>
                                                    <span class="status-badge approved"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                                <?php else: ?>
                                                    <span class="status-badge pending"><i class="bi bi-exclamation-circle"></i> Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button class="btn btn-warning btn-sm" onclick='openModal("hospital", <?php echo json_encode($hosp); ?>)' style="font-size: 0.75rem; padding: 4px 12px;">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <?php if (!$hosp['is_verified']): ?>
                                                        <button class="btn btn-success btn-sm" onclick="verifyHospital(<?php echo $hosp['hospital_id']; ?>, this)" style="font-size: 0.75rem; padding: 4px 12px;">
                                                            <i class="bi bi-check-lg"></i> Verify
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteHospital(<?php echo $hosp['hospital_id']; ?>)" style="font-size: 0.75rem; padding: 4px 12px;">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 20px; color: #888;">No hospitals found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Blood Centers Section -->
                <div id="centers-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h2 style="margin: 0;">All Blood Centers</h2>
                            <button class="btn btn-primary btn-sm" onclick="openModal('center')" style="font-size: 0.85rem; padding: 6px 16px;">
                                <i class="bi bi-plus-lg"></i> Add Center
                            </button>
                        </div>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>City</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_centers)): ?>
                                        <?php foreach ($all_centers as $center): ?>
                                        <tr>
                                            <td><?php echo $center['center_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($center['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($center['address']); ?></td>
                                            <td><?php echo htmlspecialchars($center['city']); ?></td>
                                            <td><?php echo htmlspecialchars($center['contact_number'] ?? 'N/A'); ?></td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button class="btn btn-warning btn-sm" onclick='openModal("center", <?php echo json_encode($center); ?>)' style="font-size: 0.75rem; padding: 4px 12px;">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteCenter(<?php echo $center['center_id']; ?>)" style="font-size: 0.75rem; padding: 4px 12px;">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 20px; color: #888;">No blood centers found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Blood Requests Section -->
                <div id="requests-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <h2>All Blood Requests</h2>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hospital</th>
                                        <th>Blood Type</th>
                                        <th>Units</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_requests)): ?>
                                        <?php foreach ($all_requests as $req): ?>
                                        <tr>
                                            <td><?php echo $req['request_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($req['hospital_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($req['city']); ?></small>
                                            </td>
                                            <td><span class="blood-group-badge"><?php echo $req['blood_type_required']; ?></span></td>
                                            <td><?php echo $req['units_requested']; ?></td>
                                            <td>
                                                <span class="activity-status <?php echo strtolower($req['urgency_level']); ?>" style="font-size: 0.7rem; padding: 4px 10px;">
                                                    <?php echo $req['urgency_level']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $req['status'])); ?>">
                                                    <?php echo $req['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 20px; color: #888;">No blood requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Donor Profiles Section -->
                <div id="donors-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <h2>All Donor Profiles</h2>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Blood Type</th>
                                        <th>Weight (kg)</th>
                                        <th>Last Donation</th>
                                        <th>Anonymous</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_donor_profiles)): ?>
                                        <?php foreach ($all_donor_profiles as $dp): ?>
                                        <tr>
                                            <td><?php echo $dp['donor_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($dp['first_name'] . ' ' . $dp['last_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($dp['email']); ?></td>
                                            <td><span class="blood-group-badge"><?php echo $dp['blood_type']; ?></span></td>
                                            <td><?php echo $dp['weight_kg']; ?></td>
                                            <td><?php echo $dp['last_donation_date'] ? date('M d, Y', strtotime($dp['last_donation_date'])) : 'Never'; ?></td>
                                            <td>
                                                <?php if ($dp['is_anonymous']): ?>
                                                    <span class="status-badge pending"><i class="bi bi-eye-slash"></i> Yes</span>
                                                <?php else: ?>
                                                    <span class="status-badge approved"><i class="bi bi-eye"></i> No</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 20px; color: #888;">No donor profiles found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- AI DOCS Section (Replacing Inventory) -->
                <div id="ai-docs-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <h2>AI Documentation & Insights</h2>
                        <div class="empty-state" style="text-align: center; padding: 4rem 2rem;">
                            <i class="bi bi-robot" style="font-size: 4rem; color: #d4145a; margin-bottom: 1.5rem; display: block;"></i>
                            <h3 style="color: #2d3436; margin-bottom: 1rem;">Coming Soon</h3>
                            <p style="color: #636e72; max-width: 500px; margin: 0 auto;">
                                This section will feature advanced AI-driven documentation, predictive analytics for blood supply, 
                                and intelligent donor matching insights.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Messages Section -->
                <div id="messages-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Messages</h2>
                            <button class="btn btn-primary" onclick="openComposeModal()"><i class="bi bi-pencil-square"></i> Compose</button>
                        </div>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Subject</th>
                                        <th>Sent At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_messages)): ?>
                                        <?php foreach ($all_messages as $msg): ?>
                                        <tr>
                                            <td><?php echo $msg['message_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($msg['receiver_name']); ?></td>
                                            <td><?php echo htmlspecialchars($msg['subject'] ?? '—'); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($msg['sent_at'])); ?></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewMessage(
                                                                '<?php echo htmlspecialchars($msg['subject'] ?? 'No Subject'); ?>',
                                                                '<?php echo htmlspecialchars($msg['sender_name']); ?>',
                                                                '<?php echo htmlspecialchars($msg['receiver_name']); ?>',
                                                                '<?php echo date('M d, Y H:i', strtotime($msg['sent_at'])); ?>',
                                                                `<?php echo htmlspecialchars($msg['message_content']); ?>`
                                                            )">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary btn-outline-success" 
                                                            onclick="replyMessage(
                                                                <?php echo $msg['sender_id']; ?>, 
                                                                'Re: <?php echo htmlspecialchars($msg['subject'] ?? 'No Subject'); ?>'
                                                            )">
                                                        <i class="bi bi-reply"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary btn-outline-danger" 
                                                            onclick="deleteMessage(<?php echo $msg['message_id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 20px; color: #888;">No messages found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Appointments Section -->
                <div id="appointments-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <h2>All Appointments</h2>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Donor</th>
                                        <th>Center</th>
                                        <th>City</th>
                                        <th>Scheduled</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_appointments)): ?>
                                        <?php foreach ($all_appointments as $appt): ?>
                                        <tr>
                                            <td><?php echo $appt['appointment_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($appt['donor_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($appt['center_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appt['city']); ?></td>
                                            <td><?php echo date('M d, Y — h:i A', strtotime($appt['scheduled_time'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $appt['status'])); ?>">
                                                    <?php echo $appt['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <?php if ($appt['status'] === 'Pending'): ?>
                                                        <button class="btn btn-success btn-sm" onclick="updateAppointment(<?php echo $appt['appointment_id']; ?>, 'approve', 'approve')" style="font-size: 0.75rem; padding: 4px 10px;">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="updateAppointment(<?php echo $appt['appointment_id']; ?>, 'reject', 'reject')" style="font-size: 0.75rem; padding: 4px 10px;">
                                                            <i class="bi bi-x-lg"></i> Reject
                                                        </button>
                                                    <?php elseif ($appt['status'] === 'Allowed'): ?>
                                                        <button class="btn btn-primary btn-sm" onclick="updateAppointment(<?php echo $appt['appointment_id']; ?>, 'complete', 'mark as completed')" style="font-size: 0.75rem; padding: 4px 10px;">
                                                            <i class="bi bi-check-circle"></i> Complete
                                                        </button>
                                                        <button class="btn btn-secondary btn-sm" onclick="updateAppointment(<?php echo $appt['appointment_id']; ?>, 'reset', 'reset to pending')" style="font-size: 0.75rem; padding: 4px 10px;">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                                        </button>
                                                    <?php elseif ($appt['status'] === 'Cancelled' || $appt['status'] === 'No-show'): ?>
                                                        <button class="btn btn-outline-secondary btn-sm" onclick="updateAppointment(<?php echo $appt['appointment_id']; ?>, 'reset', 'reset to pending')" style="font-size: 0.75rem; padding: 4px 10px;">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                                        </button>
                                                    <?php elseif ($appt['status'] === 'Completed'): ?>
                                                        <span class="text-muted small"><i class="bi bi-check-circle-fill text-success"></i> Done</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 20px; color: #888;">No appointments found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </section>
            </div>
            
        </section>
    </section>

<!-- Admin CRUD Modal -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid rgba(0,0,0,0.08); padding: 20px 24px;">
                <h5 class="modal-title" id="adminModalLabel" style="font-weight: 700;"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="adminModalBody" style="padding: 24px;"></div>
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.08); padding: 16px 24px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="adminModalSave">
                    <i class="bi bi-check-lg"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- Compose Message Modal -->
    <div class="modal fade" id="composeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Compose Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="composeForm">
                        <div class="mb-3">
                            <label class="form-label">To:</label>
                            <select id="msg_receiver" class="form-select" required>
                                <option value="">-- Select Recipient --</option>
                                <optgroup label="Hospital Admins">
                                    <?php foreach ($hospital_admins as $admin): ?>
                                        <option value="<?php echo $admin['user_id']; ?>"><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Donors">
                                    <?php foreach ($donors as $d): ?>
                                        <option value="<?php echo $d['user_id']; ?>"><?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject:</label>
                            <input type="text" id="msg_subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message:</label>
                            <textarea id="msg_content" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Message Modal -->
    <div class="modal fade" id="viewMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="view_msg_subject" class="mb-3 fw-bold"></h4>
                    <div class="d-flex justify-content-between mb-3 text-muted small">
                        <span>From: <strong id="view_msg_sender"></strong></span>
                        <span id="view_msg_date"></span>
                    </div>
                    <div class="p-3 bg-light rounded border">
                        <p id="view_msg_content" style="white-space: pre-wrap; margin:0;"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
<script src="/redhope/assets/js/admin.js"></script>
<script>
    // Pass centers + donations data to JS for inventory modal dropdowns
    window.centersData = <?php echo json_encode($all_centers); ?>;
    window.donationsData = <?php echo json_encode($valid_donations); ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'users';
        loadSection(tab);
        initCharts();
    });

    function loadSection(section) {
        document.querySelectorAll('.nav-links-btns span').forEach(el => el.classList.remove('active'));
        const activeBtn = document.querySelector(`.nav-links-btns span[onclick="loadSection('${section}')"]`);
        if (activeBtn) activeBtn.classList.add('active');

        document.querySelectorAll('.dashboard-section').forEach(el => el.style.display = 'none');
        const target = document.getElementById(section + '-section');
        if (target) {
            target.style.display = 'block';
            target.style.opacity = 0;
            setTimeout(() => {
                target.style.transition = 'opacity 0.3s ease';
                target.style.opacity = 1;
            }, 10);
        }
    }


    function initCharts() {
        const chartColors = ['#e53e3e','#dd6b20','#d69e2e','#38a169','#3182ce','#805ad5','#d53f8c','#2b6cb0'];

        // 1. Blood Type Distribution (Doughnut)
        const btLabels = <?php echo json_encode(array_column($blood_type_dist, 'blood_type')); ?>;
        const btData = <?php echo json_encode(array_map('intval', array_column($blood_type_dist, 'cnt'))); ?>;
        
        if (btLabels.length > 0) {
            new Chart(document.getElementById('bloodTypeChart'), {
                type: 'doughnut',
                data: {
                    labels: btLabels,
                    datasets: [{
                        data: btData,
                        backgroundColor: chartColors.slice(0, btLabels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } }
                    }
                }
            });
        }

        // 2. Monthly Donations (Bar)
        const mdLabels = <?php echo json_encode(array_column($monthly_donations, 'month')); ?>;
        const mdData = <?php echo json_encode(array_map('intval', array_column($monthly_donations, 'cnt'))); ?>;
        
        new Chart(document.getElementById('donationsChart'), {
            type: 'bar',
            data: {
                labels: mdLabels.map(m => { const [y, mo] = m.split('-'); return new Date(y, mo-1).toLocaleDateString('en', {month:'short', year:'2-digit'}); }),
                datasets: [{
                    label: 'Donations',
                    data: mdData,
                    backgroundColor: 'rgba(229, 62, 62, 0.75)',
                    borderColor: '#e53e3e',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });

        // 3. Urgency Distribution (Pie)
        const urgLabels = <?php echo json_encode(array_column($urgency_dist, 'urgency_level')); ?>;
        const urgData = <?php echo json_encode(array_map('intval', array_column($urgency_dist, 'cnt'))); ?>;
        const urgColors = { 'Normal': '#38a169', 'Urgent': '#dd6b20', 'Emergency': '#e53e3e' };
        
        if (urgLabels.length > 0) {
            new Chart(document.getElementById('urgencyChart'), {
                type: 'pie',
                data: {
                    labels: urgLabels,
                    datasets: [{
                        data: urgData,
                        backgroundColor: urgLabels.map(l => urgColors[l] || '#999'),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } }
                    }
                }
            });
        }
    }
</script>
</body>
</html>
