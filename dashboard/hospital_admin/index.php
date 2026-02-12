<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Hospital Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../../database/config.php';

$user_id = $_SESSION['user_id'];
$user = null;
$hospital = null;
$pending_requests = 0;
$fulfilled_requests = 0;
$total_units_needed = 0;
$all_hospital_requests = [];
$recent_activity = [];

try {
    // 1. Fetch Admin Data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Fetch Hospital Data
    $stmt = $pdo->prepare("SELECT * FROM hospitals WHERE admin_id = ?");
    $stmt->execute([$user_id]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hospital || !$hospital['is_verified']) {
        header("Location: /redhope/dashboard/hospital_admin/fill_hospital_data.php");
        exit();
    }

    $hospital_id = $hospital['hospital_id'];

    // 3. Fetch Blood Requests & Stats (Include Donor Names if Progressing)
    $stmt = $pdo->prepare("
        SELECT br.*, u.first_name as donor_first, u.last_name as donor_last, u.phone as donor_phone
        FROM blood_requests br
        LEFT JOIN users u ON br.donor_id = u.user_id
        WHERE br.hospital_id = ? 
        ORDER BY br.created_at DESC
    ");
    $stmt->execute([$hospital_id]);
    $all_hospital_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_hospital_requests as $req) {
        if ($req['status'] === 'Open')
            $pending_requests++;
        if ($req['status'] === 'Fulfilled')
            $fulfilled_requests++;
        if ($req['status'] === 'Open')
            $total_units_needed += $req['units_requested'];
    }

    // 4. Recent Activity (Open high priority requests)
    $stmt = $pdo->prepare("
        SELECT * FROM blood_requests 
        WHERE hospital_id = ? AND status = 'Open'
        ORDER BY urgency_level DESC, created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$hospital_id]);
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Fetch Inventory for display
    $stmt = $pdo->prepare("
        SELECT bi.*, bc.name as center_name, bc.city as center_city
        FROM blood_inventory bi
        LEFT JOIN blood_centers bc ON bi.current_location_id = bc.center_id
        WHERE bi.status = 'Available' AND bi.expiry_date > CURDATE()
        ORDER BY bi.blood_type, bi.expiry_date ASC
    ");
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Fetch Messages
    $stmt = $pdo->prepare("
        SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.user_id 
        WHERE m.receiver_id = ? 
        ORDER BY m.sent_at DESC
    ");
    $stmt->execute([$user_id]);
    $all_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

} catch (PDOException $e) {
    // Basic error handling
}

$user_name = $user['first_name'] . ' ' . $user['last_name'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hospital['name']); ?> | Hospital Dashboard</title>
    <?php include __DIR__ . '/../../includes/meta.php'; ?>
    <link rel="stylesheet" href="/redhope/assets/css/profile.css">
    <link rel="stylesheet" href="/redhope/assets/css/ai_chat.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
</head>

<body>
    <?php include __DIR__ . "/../../includes/loader.php"; ?>
    <?php include __DIR__ . "/../../includes/header.php"; ?>
    <section id="root">
        <section class="welcome-part">
            <div class="info-card">
                <div class="user-avatar">
                    <i class="bi bi-hospital" style="font-size: 2rem;"></i>
                </div>
                <div class="user-details">
                    <h1><?php echo htmlspecialchars($hospital['name']); ?></h1>
                    <p class="user-role">Administrator: <?php echo htmlspecialchars($user['first_name']); ?></p>
                    <div class="badges">
                        <span class="blood-group-badge"><?php echo htmlspecialchars($hospital['city']); ?></span>
                        <span class="status-badge <?php echo ($hospital['is_verified']) ? 'eligible' : 'wait'; ?>">
                            <?php echo ($hospital['is_verified']) ? 'Verified Institution' : 'Pending Verification'; ?>
                        </span>
                    </div>
                </div>
                <!-- Quick action button -->
                <a href="javascript:void(0)" onclick="loadSection('new-request')" class="btn-donate-now">
                    <i class="bi bi-plus-circle"></i> New Request
                </a>
            </div>

            <div class="additional">
                <div class="statistics">
                    <div class="stat-box">
                        <div class="icon red"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div>
                            <h3><?php echo $pending_requests; ?></h3>
                            <p>Open Requests</p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="icon green"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <h3><?php echo $fulfilled_requests; ?></h3>
                            <p>Fulfilled</p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="icon purple"><i class="bi bi-droplet-fill"></i></div>
                        <div>
                            <h3><?php echo $total_units_needed; ?></h3>
                            <p>Units Required</p>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="chart-card">
                        <h3 style="font-size: 1.1rem; color: #2d3436; margin-bottom: 1rem;">Request Status</h3>
                        <div style="height: 250px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3 style="font-size: 1.1rem; color: #2d3436; margin-bottom: 1rem;">Urgency Distribution</h3>
                        <div style="height: 250px;">
                            <canvas id="urgencyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h3>Urgent Hospital Needs</h3>
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-row">
                                <div class="act-icon"><i class="bi bi-droplet-half text-danger"></i></div>
                                <div class="act-info">
                                    <strong><?php echo $activity['blood_type_required']; ?> Request</strong>
                                    <span><?php echo $activity['units_requested']; ?> units •
                                        <?php echo $activity['urgency_level']; ?> Priority</span>
                                </div>
                                <span
                                    class="badg <?php echo strtolower($activity['status']); ?>"><?php echo $activity['status']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #888; font-size: 0.9rem;">No urgent requests at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="user_dashboard">
            <div class="actions-text">
                <h2>Hospital Management</h2>
            </div>
            <div class="nav-links-btns">
                <div>
                    <span onclick="loadSection('new-request')">
                        <i class="bi bi-plus-lg"></i>
                        <p>New Request</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('all-requests')">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <p>Requests</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('donations-history')">
                        <i class="bi bi-clock-history"></i>
                        <p>History</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('profile')">
                        <i class="bi bi-person-gear"></i>
                        <p>Profile</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('messages')">
                        <i class="bi bi-envelope"></i>
                        <p>Messages</p>
                    </span>
                </div>
                <div style="flex: 1 1 100%; max-width: 100%;">
                    <span onclick="loadSection('ai')">
                        <i class="bi bi-robot"></i>
                        <p>Ask HopeAI</p>
                    </span>
                </div>
            </div>
            <div class="view-nav">
                <section id="contentFrameView">

                    <!-- AI Section -->
                    <div id="ai-section" class="dashboard-section" style="display: none;">
                        <?php include __DIR__ . '/../../includes/ai_chat_widget.php'; ?>
                    </div>

                    <!-- New Request Section -->
                    <div id="new-request-section" class="dashboard-section" style="display: none;">
                        <div class="appointment-form-wrapper" style="margin: 0; box-shadow: none;">
                            <h2>Submit Blood Request</h2>
                            <p>Specify the required units and urgency level.</p>
                            <form id="bloodRequestForm" class="profile-form">
                                <input type="hidden" name="hospital_id" value="<?php echo $hospital['hospital_id']; ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Blood Type</label>
                                        <select name="blood_type_required" required>
                                            <option value="">-- Select --</option>
                                            <?php foreach ($blood_types as $type): ?>
                                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Units Needed</label>
                                        <input type="number" name="units_requested" required min="1" value="1">
                                    </div>
                                    <div class="form-group">
                                        <label>Urgency</label>
                                        <select name="urgency_level" required>
                                            <option value="Normal">Normal</option>
                                            <option value="Urgent">Urgent</option>
                                            <option value="Emergency">Emergency</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Patient Identifier (Optional)</label>
                                    <input type="text" name="patient_identifier" placeholder="e.g. Ward 4 / Patient ID">
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">Submit Request</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- All Requests Section -->
                    <div id="all-requests-section" class="dashboard-section" style="display: none;">
                        <div class="content-wrapper">
                            <h2>Hospital Request History</h2>
                            <div class="activity-list">
                                <?php if (!empty($all_hospital_requests)): ?>
                                    <?php foreach ($all_hospital_requests as $req): ?>
                                        <div class="activity-item d-flex align-items-center">
                                            <div class="activity-icon"><i class="bi bi-droplet-fill text-danger"></i></div>
                                            <div class="activity-details flex-grow-1">
                                                <h4><?php echo $req['blood_type_required']; ?> for Patient:
                                                    <?php echo htmlspecialchars($req['patient_identifier'] ?? 'N/A'); ?></h4>
                                                <p><?php echo $req['units_requested']; ?> units •
                                                    <?php echo date('M d, Y', strtotime($req['created_at'])); ?></p>
                                                <?php if ($req['donor_id']): ?>
                                                    <p class="small text-muted"><i class="bi bi-person-fill"></i> Accepted by:
                                                        <?php echo htmlspecialchars($req['donor_first'] . ' ' . $req['donor_last']); ?>
                                                        (<?php echo htmlspecialchars($req['donor_phone']); ?>)</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="activity-status-wrapper d-flex flex-column align-items-end gap-2">
                                                <div class="activity-status <?php echo strtolower($req['urgency_level']); ?>">
                                                    <?php echo $req['urgency_level']; ?>
                                                </div>
                                                <div>
                                                    <span
                                                        class="status-badge <?php echo strtolower(str_replace(' ', '-', $req['status'])); ?>">
                                                        <?php echo $req['status']; ?>
                                                    </span>
                                                </div>
                                                <?php if ($req['status'] === 'In Progress'): ?>
                                                    <button class="btn btn-success btn-sm mt-1"
                                                        onclick="fulfillBloodRequest(<?php echo $req['request_id']; ?>)"
                                                        style="font-size: 0.75rem;">
                                                        Mark as Fulfilled
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="padding: 20px; color: #888;">No requests found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Donations History Section -->
                    <div id="donations-history-section" class="dashboard-section" style="display: none;">
                        <div class="content-wrapper">
                            <h2>Fulfilled Donations History</h2>
                            <div class="donations-table-wrapper">
                                <table class="donations-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Blood Type</th>
                                            <th>Donor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $has_fulfilled = false;
                                        foreach ($all_hospital_requests as $req):
                                            if ($req['status'] === 'Fulfilled'):
                                                $has_fulfilled = true;
                                                ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                                    <td><strong><?php echo $req['blood_type_required']; ?></strong></td>
                                                    <td><?php echo htmlspecialchars(($req['donor_first'] ?? '') . ' ' . ($req['donor_last'] ?? '')); ?>
                                                    </td>
                                                    <td><span class="status-badge eligible">Fulfilled</span></td>
                                                </tr>
                                            <?php
                                            endif;
                                        endforeach;
                                        if (!$has_fulfilled):
                                            ?>
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 20px;">No fulfilled
                                                    donations yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Section -->
                    <div id="profile-section" class="dashboard-section" style="display: none;">
                        <div class="content-wrapper">
                            <h2>Admin & Hospital Profile</h2>
                            <form id="adminProfileForm" class="profile-form mt-4">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Admin First Name</label>
                                        <input type="text" name="first_name"
                                            value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Admin Last Name</label>
                                        <input type="text" name="last_name"
                                            value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Official Email</label>
                                    <input type="email" name="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">Update Profile</button>
                                </div>
                            </form>

                            <hr style="margin: 30px 0; border-top: 1px solid #eee;">

                            <h3>Hospital Information</h3>
                            <div class="hospital-info-display mt-3">
                                <p><strong>Institution:</strong> <?php echo htmlspecialchars($hospital['name']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($hospital['address']); ?></p>
                                <p><strong>Contact:</strong>
                                    <?php echo htmlspecialchars($hospital['contact_number']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Section -->
                    <div id="messages-section" class="dashboard-section" style="display: none;">
                        <div class="content-wrapper">
                            <h2>Messages</h2>
                            <div class="donations-table-wrapper">
                                <table class="donations-table">
                                    <thead>
                                        <tr>
                                            <th>From</th>
                                            <th>Subject</th>
                                            <th>Sent At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($all_messages)): ?>
                                            <?php foreach ($all_messages as $msg): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($msg['subject'] ?? '—'); ?></td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($msg['sent_at'])); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="viewMessage(
                                                            '<?php echo htmlspecialchars($msg['subject'] ?? 'No Subject'); ?>',
                                                            '<?php echo htmlspecialchars($msg['sender_name']); ?>',
                                                            '<?php echo date('M d, Y H:i', strtotime($msg['sent_at'])); ?>',
                                                            `<?php echo htmlspecialchars($msg['message_content']); ?>`,
                                                            <?php echo $msg['sender_id']; ?>
                                                        )">
                                                            Read
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 20px; color: #888;">No
                                                    messages received.</td>
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

    <!-- View Message Modal -->
    <div class="modal fade" id="viewMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="view_msg_subject" class="mb-3 fw-bold"></h4>
                    <div class="d-flex justify-content-between mb-3 text-muted small">
                        <span>From: <strong id="view_msg_sender"></strong></span>
                        <span id="view_msg_date"></span>
                    </div>
                    <div class="p-3 bg-light rounded border mb-3">
                        <p id="view_msg_content" style="white-space: pre-wrap; margin:0;"></p>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-primary" id="replyBtn" onclick="openReplyModal()">
                            <i class="bi bi-reply"></i> Reply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reply to Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="replyForm">
                        <input type="hidden" id="reply_receiver_id">
                        <div class="mb-3">
                            <label class="form-label">Subject:</label>
                            <input type="text" id="reply_subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message:</label>
                            <textarea id="reply_content" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . "/../../includes/footer.php"; ?>
    <script src="/redhope/assets/js/hospital.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Show default section or section from URL
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'new-request';
            loadSection(tab);
        });

        function loadSection(section) {
            // 1. Update Buttons Active State
            document.querySelectorAll('.nav-links-btns span').forEach(el => el.classList.remove('active'));

            const activeBtn = document.querySelector(`.nav-links-btns span[onclick="loadSection('${section}')"]`);
            if (activeBtn) activeBtn.classList.add('active');

            // 2. Hide All Sections
            document.querySelectorAll('.dashboard-section').forEach(el => el.style.display = 'none');

            // 3. Show Target Section
            const target = document.getElementById(section + '-section');
            if (target) {
                target.style.display = 'block';
                target.style.opacity = 0;
                setTimeout(() => {
                    target.style.transition = 'opacity 0.3s ease';
                    target.style.opacity = 1;
                }, 10);
            }

            // Update URL
            const newUrl = window.location.pathname + '?tab=' + section;
            window.history.pushState({ path: newUrl }, '', newUrl);
        }
    </script>
    <script>
        // Prepare Data for Charts
        <?php
        $status_counts = ['Open' => 0, 'In Progress' => 0, 'Fulfilled' => 0, 'Cancelled' => 0];
        $urgency_counts = ['Normal' => 0, 'Urgent' => 0, 'Emergency' => 0];

        foreach ($all_hospital_requests as $req) {
            if (isset($status_counts[$req['status']]))
                $status_counts[$req['status']]++;
            if (isset($urgency_counts[$req['urgency_level']]))
                $urgency_counts[$req['urgency_level']]++;
        }
        ?>

        document.addEventListener('DOMContentLoaded', () => {
            // Status Chart
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Open', 'In Progress', 'Fulfilled', 'Cancelled'],
                    datasets: [{
                        data: [<?php echo implode(',', array_values($status_counts)); ?>],
                        backgroundColor: ['#f1c40f', '#3498db', '#2ecc71', '#e74c3c'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });

            // Urgency Chart
            new Chart(document.getElementById('urgencyChart'), {
                type: 'bar',
                data: {
                    labels: ['Normal', 'Urgent', 'Emergency'],
                    datasets: [{
                        label: 'Requests',
                        data: [<?php echo implode(',', array_values($urgency_counts)); ?>],
                        backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c'],
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: false } }
                }
            });
        });
    </script>
</body>

</html>