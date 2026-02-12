<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Hospital Admin') {
    header("Location: /redhope/login.php");
    exit();
}

include_once __DIR__ . '/../../database/config.php';

$user_id = $_SESSION['user_id'];
$user = null;
$existing_hospital = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if this user already has a hospital registered
    $stmt = $pdo->prepare("SELECT * FROM hospitals WHERE admin_id = ?");
    $stmt->execute([$user_id]);
    $existing_hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    // If hospital exists and is verified, redirect to dashboard
    if ($existing_hospital && $existing_hospital['is_verified']) {
        header("Location: /redhope/dashboard/hospital_admin/");
        exit();
    }

} catch (PDOException $e) {
    // Handle silently
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Hospital | RedHope</title>
    <?php include __DIR__ . '/../../includes/meta.php'; ?>
    <link rel="stylesheet" href="/redhope/assets/css/global.css">
    <link rel="stylesheet" href="/redhope/assets/css/profile.css">
</head>

<body>
    <?php include __DIR__ . '/../../includes/loader.php'; ?>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <main class="dashboard-wrapper">
        <div class="dashboard-container">
            <section class="dashboard-content" style="max-width: 800px; margin: 0 auto;">
                <div class="content-header" style="text-align: center;">
                    <h1><?php echo $existing_hospital ? 'Pending Approval' : 'Register Your Hospital'; ?></h1>
                    <p><?php echo $existing_hospital ? 'Your hospital registration is pending admin approval.' : 'Please fill in your hospital details to get started.'; ?>
                    </p>
                </div>

                <div id="alertContainer"></div>

                <?php if ($existing_hospital): ?>
                    <!-- Pending Approval State -->
                    <div class="pending-approval-card">
                        <div class="pending-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <h2>Awaiting Verification</h2>
                        <p>Your hospital registration has been submitted and is currently under review by our
                            administrators.</p>

                        <div class="hospital-summary">
                            <h3>Submitted Details</h3>
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <label>Hospital Name</label>
                                    <p><?php echo htmlspecialchars($existing_hospital['name']); ?></p>
                                </div>
                                <div class="summary-item">
                                    <label>City</label>
                                    <p><?php echo htmlspecialchars($existing_hospital['city']); ?></p>
                                </div>
                                <div class="summary-item">
                                    <label>Contact</label>
                                    <p><?php echo htmlspecialchars($existing_hospital['contact_number']); ?></p>
                                </div>
                                <div class="summary-item">
                                    <label>Submitted On</label>
                                    <p><?php echo date('M d, Y', strtotime($existing_hospital['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="pending-actions">
                            <a href="javascript:logoutUser()" class="btn-outline">
                                <i class="bi bi-box-arrow-left"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Registration Form -->
                    <div class="profile-section">
                        <div class="section-header">
                            <h2><i class="bi bi-hospital"></i> Hospital Information</h2>
                        </div>
                        <form id="hospitalRegistrationForm" class="profile-form">
                            <div class="form-group">
                                <label for="hospital_name">Hospital Name *</label>
                                <input type="text" id="hospital_name" name="name" required
                                    placeholder="e.g., Cairo General Hospital">
                            </div>
                            <div class="form-group">
                                <label for="address">Full Address *</label>
                                <textarea id="address" name="address" required rows="3"
                                    placeholder="e.g., 123 Main Street, Downtown"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" required placeholder="e.g., Cairo">
                                </div>
                                <div class="form-group">
                                    <label for="contact_number">Contact Number *</label>
                                    <input type="tel" id="contact_number" name="contact_number" required
                                        placeholder="e.g., +20 2 1234 5678">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Hospital Email</label>
                                <input type="email" id="email" name="email" placeholder="e.g., info@hospital.com">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <i class="bi bi-send"></i> Submit for Approval
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <script src="/redhope/assets/js/global.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('hospitalRegistrationForm');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Submitting...';
                btn.disabled = true;

                const formData = {
                    name: form.name.value,
                    address: form.address.value,
                    city: form.city.value,
                    contact_number: form.contact_number.value,
                    email: form.email.value
                };

                try {
                    const response = await fetch('/redhope/apis/register_hospital.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        location.reload();
                    } else {
                        showAlert(result.message, 'error');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                } catch (error) {
                    showAlert('An error occurred. Please try again.', 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        });
    </script>
    <style>
        .pending-approval-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 48px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        }

        .pending-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff9800, #ffc107);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.5rem;
            color: white;
        }

        .pending-approval-card h2 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: #1a1a1a;
        }

        .pending-approval-card>p {
            color: #666;
            margin-bottom: 32px;
        }

        .hospital-summary {
            background: #f8f8f8;
            border-radius: 12px;
            padding: 24px;
            text-align: left;
            margin-bottom: 24px;
        }

        .hospital-summary h3 {
            font-size: 1rem;
            margin-bottom: 16px;
            color: #333;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .summary-item label {
            font-size: 0.8rem;
            color: #888;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }

        .summary-item p {
            color: #1a1a1a;
            margin: 0;
            font-weight: 600;
        }

        .pending-actions {
            margin-top: 24px;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
    </style>
</body>

</html>