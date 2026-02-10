<?php
session_start();
include_once __DIR__ . '/../../database/config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header("Location: /redhope/login.php");
    exit();
}

$user_name = 'User';
$donor_profile = [];
$total_donations = 0;
$last_donation_date = null;
$days_until_eligible = 0;
$recent_activity = [];
$my_appointments = [];
$donation_history = [];
$all_requests = [];
$blood_centers = [];

try {
    // 1. Get User Name
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
    }

    // 2. Get Donor Profile (Blood Type, Weight, etc.)
    $stmt = $pdo->prepare("SELECT * FROM donor_profiles WHERE donor_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $donor_profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Get Total Donations & Last Donation Date
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, MAX(donated_at) as last_date 
        FROM donations 
        WHERE donor_id = ? AND status = 'Approved'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_donations = $stats['count'] ?? 0;
    $last_donation_date = $stats['last_date'];

    // 4. Calculate Eligibility
    if ($last_donation_date) {
        $last_date_obj = new DateTime($last_donation_date);
        $today = new DateTime();
        $interval = $last_date_obj->diff($today);
        $days_passed = $interval->days;
        $days_until_eligible = max(0, 56 - $days_passed); // Assuming 56 days gap
    }

    // 5. Get My Appointments (Upcoming)
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as center_name, c.city 
        FROM appointments a 
        JOIN blood_centers c ON a.center_id = c.center_id 
        WHERE a.donor_id = ? AND a.status IN ('Pending', 'In Progress', 'Allowed')
        ORDER BY a.scheduled_time ASC
    ");
    $stmt->execute([$donor_profile['donor_id'] ?? 0]);
    $my_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Get Donation History (All)
    $stmt = $pdo->prepare("
        SELECT d.*, c.name as center_name 
        FROM donations d 
        JOIN blood_centers c ON d.center_id = c.center_id 
        WHERE d.donor_id = ? 
        ORDER BY d.donated_at DESC
    ");
    $stmt->execute([$donor_profile['donor_id'] ?? 0]);
    $donation_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Get All Open Requests Matching Donor's Blood Type
    $donor_blood_type = $donor_profile['blood_type'] ?? null;
    $all_requests = [];

    if ($donor_blood_type) {
        $stmt = $pdo->prepare("
            SELECT br.*, h.name as hospital_name, h.city 
            FROM blood_requests br 
            JOIN hospitals h ON br.hospital_id = h.hospital_id 
            WHERE (br.status = 'Open' AND br.blood_type_required = ?)
            OR (br.status = 'In Progress' AND br.donor_id = ?)
            ORDER BY 
                CASE br.urgency_level 
                    WHEN 'Emergency' THEN 1 
                    WHEN 'Urgent' THEN 2 
                    ELSE 3 
                END, 
                br.created_at DESC
        ");
        $stmt->execute([$donor_blood_type, $_SESSION['user_id']]);
        $all_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Get Blood Centers (For Donate Form)
    $stmt = $pdo->query("SELECT * FROM blood_centers ORDER BY city, name");
    $blood_centers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reuse $all_requests for the widget (limit 3) if needed, or keep separate query
    $recent_activity = array_slice($all_requests, 0, 3); 

} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user_name; ?> | Donator Dashboard</title>
    <?php include __DIR__ . '/../../includes/meta.php'; ?>
    <link rel="stylesheet" href="/redhope/assets/css/profile.css">
</head>
<body>
<?php include __DIR__ . "/../../includes/loader.php"; ?>
<?php include __DIR__ . "/../../includes/header.php"; ?>
    <section id="root">
        <section class="welcome-part">
            <div class="info-card">
                <div class="user-avatar">
                   <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h1>Hello, <?php echo htmlspecialchars($user['first_name']); ?>! 👋</h1>
                    <p class="user-role">RedHero Donor</p>
                    <div class="badges">
                        <span class="blood-group-badge"><?php echo htmlspecialchars($donor_profile['blood_type'] ?? 'Unknown'); ?></span>
                        <span class="status-badge <?php echo ($days_until_eligible <= 0) ? 'eligible' : 'wait'; ?>">
                            <?php echo ($days_until_eligible <= 0) ? 'Eligible to Donate' : 'Wait ' . $days_until_eligible . ' Days'; ?>
                        </span>
                    </div>
                </div>
                <a href="/redhope/dashboard/donator/donate.php" class="btn-donate-now">
                    <i class="bi bi-droplet-fill"></i> Donate Now
                </a>
            </div>

            <div class="additional">
                <div class="statistics">
                    <div class="stat-box">
                        <div class="icon red"><i class="bi bi-heart-pulse-fill"></i></div>
                        <div>
                            <h3><?php echo $total_donations; ?></h3>
                            <p>Donations</p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="icon green"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <h3><?php echo $total_donations * 3; ?></h3>
                            <p>Lives Saved</p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="icon purple"><i class="bi bi-calendar-event"></i></div>
                        <div>
                            <h3><?php echo $last_donation_date ? date('M d, Y', strtotime($last_donation_date)) : 'N/A'; ?></h3>
                            <p>Last Donation</p>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h3>Pending Requests</h3>
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-row">
                            <div class="act-icon"><i class="bi bi-hospital"></i></div>
                            <div class="act-info">
                                <strong>Request at <?php echo htmlspecialchars($activity['hospital_name']); ?></strong>
                                <span><?php echo date('M d, Y', strtotime($activity['created_at'])); ?> • <?php echo $activity['urgency_level']; ?> Priority</span>
                            </div>
                            <span class="badg <?php echo strtolower($activity['status']); ?>"><?php echo $activity['status']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #888; font-size: 0.9rem;">No pending requests for your blood type (<?php echo htmlspecialchars($donor_blood_type ?? 'Unknown'); ?>).</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="user_dashboard">
            <div class="actions-text">
                <h2>Actions</h2>
            </div>
            <div class="nav-links-btns">
                <div>
                    <span onclick="loadSection('donate')">
                        <i class="bi bi-droplet-fill"></i>
                        <p>Donate Now</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('requests')">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <p>Requests</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('appointments')">
                        <i class="bi bi-calendar-check"></i>
                        <p>Appointments</p>
                    </span>
                </div>
                <div>
                    <span onclick="loadSection('history')">
                        <i class="bi bi-clock-history"></i>
                        <p>History</p>
                    </span>
                </div>
            </div>
            <div class="view-nav">
<section id="contentFrameView">
                
                <!-- Donate Section -->
                <div id="donate-section" class="dashboard-section" style="display: none;">
                    <div class="appointment-form-wrapper" style="margin: 0; box-shadow: none;">
                        <h2>Schedule a Donation</h2>
                        <p>Choose a center and time to save a life.</p>
                        <form id="appointmentForm" class="profile-form" action="/redhope/apis/create_appointment.php" method="POST">
                            <input type="hidden" name="donor_id" value="<?php echo $donor_profile['donor_id'] ?? ''; ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Select Center</label>
                                    <select name="center_id" required>
                                        <option value="">-- Choose Center --</option>
                                        <?php foreach ($blood_centers as $center): ?>
                                            <option value="<?php echo $center['center_id']; ?>">
                                                <?php echo htmlspecialchars($center['name'] . ' - ' . $center['address'] . ' (' . $center['city'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Preferred Time</label>
                                    <input type="time" name="appointment_time" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Notes (Optional)</label>
                                <textarea name="notes" rows="2" placeholder="Any special requests or medical notes?"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">Book Appointment</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Requests Section -->
                <div id="requests-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <h2>Urgent Blood Requests</h2>
                        <div class="activity-list">
                            <?php if (!empty($all_requests)): ?>
                                <?php foreach ($all_requests as $req): ?>
                                <div class="activity-item d-flex align-items-center">
                                    <div class="activity-icon"><i class="bi bi-hospital"></i></div>
                                    <div class="activity-details flex-grow-1">
                                        <h4>Request at <?php echo htmlspecialchars($req['hospital_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($req['city']); ?> • <?php echo date('M d, Y', strtotime($req['created_at'])); ?></p>
                                        <p><strong>Needed:</strong> <?php echo $req['blood_type_required']; ?> (<?php echo $req['units_requested']; ?> units)</p>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <div class="activity-status <?php echo strtolower($req['urgency_level']); ?>">
                                            <?php echo $req['urgency_level']; ?>
                                        </div>
                                        <div class="mt-1">
                                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $req['status'])); ?>" style="font-size: 0.7rem;">
                                                <?php echo $req['status']; ?>
                                            </span>
                                        </div>
                                        <?php if ($req['status'] === 'Open'): ?>
                                            <button class="btn btn-primary btn-sm" onclick="acceptBloodRequest(<?php echo $req['request_id']; ?>)" style="font-size: 0.75rem; padding: 5px 15px;">
                                                Accept Request
                                            </button>
                                        <?php elseif ($req['status'] === 'In Progress'): ?>
                                            <button class="btn btn-success btn-sm" onclick="completeBloodRequest(<?php echo $req['request_id']; ?>)" style="font-size: 0.75rem; padding: 5px 15px;">
                                                Mark as Fulfilled
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="padding: 20px; color: #888;">No urgent requests for your blood type (<?php echo htmlspecialchars($donor_blood_type ?? 'Unknown'); ?>).</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Appointments Section -->
                <div id="appointments-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <h2>My Appointments</h2>
                        <?php if (!empty($my_appointments)): ?>
                            <div class="appointments-list">
                                <?php foreach ($my_appointments as $appt): ?>
                                <div class="appointment-card" data-appt-id="<?php echo $appt['appointment_id']; ?>">
                                    <div class="appointment-card-body">
                                        <div class="appointment-date">
                                            <span class="day"><?php echo date('d', strtotime($appt['scheduled_time'])); ?></span>
                                            <span class="month"><?php echo date('M', strtotime($appt['scheduled_time'])); ?></span>
                                        </div>
                                        <div class="appointment-info">
                                            <h3><?php echo htmlspecialchars($appt['center_name']); ?></h3>
                                            <div class="appt-meta">
                                                <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($appt['city']); ?></span>
                                                <span><i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($appt['scheduled_time'])); ?></span>
                                            </div>
                                            <div class="appt-status-row">
                                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $appt['status'])); ?>">
                                                    <?php echo $appt['status']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($appt['status'] === 'Pending' || $appt['status'] === 'Allowed'): ?>
                                    <div class="appointment-actions">
                                        <button class="btn-reschedule" onclick="openRescheduleModal(<?php echo $appt['appointment_id']; ?>, '<?php echo date('Y-m-d', strtotime($appt['scheduled_time'])); ?>', '<?php echo date('H:i', strtotime($appt['scheduled_time'])); ?>')">
                                            <i class="bi bi-pencil-square"></i> Reschedule
                                        </button>
                                        <button class="btn-cancel-appt" onclick="confirmCancelAppointment(<?php echo $appt['appointment_id']; ?>)">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Reschedule Modal -->
                            <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reschedule Appointment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form id="rescheduleForm">
                                            <div class="modal-body">
                                                <input type="hidden" id="reschedule_appt_id" name="appointment_id">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">New Date</label>
                                                    <input type="date" id="reschedule_date" name="appointment_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">New Time</label>
                                                    <input type="time" id="reschedule_time" name="appointment_time" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="alert alert-danger">
                                    <i class="bi bi-calendar-x" style="font-size: 3rem"></i>
                                    <h3>No Upcoming Appointments</h3>
                                    <p>You haven't scheduled any donations yet.</p>
                                    <button class="btn-primary" onclick="loadSection('donate')">Schedule Now</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- History Section -->
                <div id="history-section" class="dashboard-section" style="display: none;">
                    <div class="content-wrapper">
                        <h2>Donation History</h2>
                        <div class="donations-table-wrapper">
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($donation_history)): ?>
                                        <?php foreach ($donation_history as $donation): ?>
                                            <td class="date-cell">
                                                <span class="date"><?php echo date('M d, Y', strtotime($donation['donated_at'])); ?></span>
                                            </td>
                                            <td class="center-cell">
                                                <span class="name"><?php echo htmlspecialchars($donation['center_name']); ?></span>
                                            </td>
                                            <td><?php echo $donation['volume_ml']; ?> ml</td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $donation['status'])); ?>">
                                                    <?php echo $donation['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; padding: 20px; color: #888;">No donation history found.</td>
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

    <!-- Hospital Info Modal -->
    <div class="modal fade" id="hospitalInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content profile-form-wrapper" style="margin: 0; box-shadow: none;">
                <div class="modal-header border-0 pb-0">
                    <h2 class="h4 mb-0"><i class="bi bi-geo-alt-fill text-danger"></i> Hospital Information</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-secondary mb-4">Please proceed to this hospital to fulfill your donation commitment. Your help saves lives!</p>
                    <div class="hospital-info-card p-3 rounded" style="background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05);">
                        <div class="mb-3">
                            <label class="text-uppercase small fw-bold text-muted d-block">Institution Name</label>
                            <span id="h_name" class="h5 fw-bold"></span>
                        </div>
                        <div class="mb-3">
                            <label class="text-uppercase small fw-bold text-muted d-block">Location / Address</label>
                            <span id="h_address"></span><br>
                            <span id="h_city" class="fw-bold"></span>
                        </div>
                        <div class="mb-3">
                            <label class="text-uppercase small fw-bold text-muted d-block">Contact Support</label>
                            <span id="h_phone"><i class="bi bi-telephone"></i> </span><br>
                            <span id="h_email"><i class="bi bi-envelope"></i> </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Got it!</button>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . "/../../includes/footer.php"; ?>
<script src="/redhope/assets/js/profile.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Show default section or section from URL
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'donate';
        loadSection(tab);
    });

    function loadSection(section) {
        // 1. Update Buttons Active State
        document.querySelectorAll('.nav-links-btns span').forEach(el => el.classList.remove('active'));
        
        // Find button by onclick attribute (since we don't have IDs on them)
        const activeBtn = document.querySelector(`.nav-links-btns span[onclick="loadSection('${section}')"]`);
        if (activeBtn) activeBtn.classList.add('active');

        // 2. Hide All Sections
        document.querySelectorAll('.dashboard-section').forEach(el => el.style.display = 'none');

        // 3. Show Target Section
        const target = document.getElementById(section + '-section');
        if (target) {
            target.style.display = 'block';
            // Animation effect
            target.style.opacity = 0;
            setTimeout(() => {
                target.style.transition = 'opacity 0.3s ease';
                target.style.opacity = 1;
            }, 10);
        }
    }
</script>
</body>
</html>
