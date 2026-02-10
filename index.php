<?php
    session_start();
    include_once __DIR__ . '/database/config.php';

    $user_data = null;
    if (isset($_SESSION['user_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
        }
    }

    $blood_counts = [
        'A+' => 0, 'A-' => 0, 'B+' => 0, 'B-' => 0,
        'AB+' => 0, 'AB-' => 0, 'O+' => 0, 'O-' => 0
    ];
    
    try {
        $stmt = $pdo->query("SELECT blood_type, COUNT(*) as count FROM donor_profiles GROUP BY blood_type");
        while ($row = $stmt->fetch()) {
            if (isset($blood_counts[$row['blood_type']])) {
                $blood_counts[$row['blood_type']] = $row['count'];
            }
        }
    } catch (PDOException $e) {
        // handle silently
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RedHope | Save Lives Through Donation</title>
    <?php include __DIR__ . '/includes/meta.php'; ?>
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
<?php include __DIR__ . '/includes/loader.php'; ?>
<?php include __DIR__ . '/includes/header.php';?>

<main class="home-wrapper">
    <section class="hero-modern">
        <div class="hero-decoration">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>
        <div class="hero-container">
            <div class="hero-badge" data-aos="fade-up">
                <i class="bi bi-heart-pulse-fill"></i>
                <span>Making a Difference</span>
            </div>
            <h1 class="hero-heading" data-aos="fade-up">
                Every Drop <span class="text-highlight">Saves a Life</span>
            </h1>
            <p class="hero-description" data-aos="fade-up">
                Join our community of heroes and make a real impact. Your donation can save up to three lives.
            </p>
            <div class="hero-actions" data-aos="fade-up">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/redhope/register.php" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Become a Donor</span>
                    </a>
                <?php else: ?>
                    <?php 
                        $role = $_SESSION['role'] ?? '';
                        if ($role === 'Donor'): 
                    ?>
                        <a href="/redhope/dashboard/donator/" class="btn btn-primary">
                            <i class="bi bi-droplet-fill"></i>
                            <span>Create a new donation</span>
                        </a>
                    <?php elseif ($role === 'Hospital Admin'): ?>
                        <a href="/redhope/dashboard/hospital_admin/" class="btn btn-primary">
                            <i class="bi bi-clipboard-data-fill"></i>
                            <span>Manage Donations</span>
                        </a>
                    <?php elseif ($role === 'Super Admin'): ?>
                        <a href="/redhope/admin/" class="btn btn-primary">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="#about" class="btn btn-secondary">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>Learn More</span>
                </a>
            </div>
            <div class="hero-stats" data-aos="fade-up">
                <div class="stat-item">
                    <div class="stat-number"><?php echo array_sum($blood_counts); ?></div>
                    <div class="stat-label">Active Donors</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support Available</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Secure & Safe</div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-donors" id="donations">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-badge">Blood Availability</span>
                <h2 class="section-title">Donor Statistics</h2>
                <p class="section-subtitle">Real-time availability across all blood groups</p>
            </div>

            <div class="blood-types-grid">
                <?php 
                $delay = 0;
                foreach ($blood_counts as $type => $count): 
                ?>
                    <div class="blood-type-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="blood-icon">
                            <i class="bi bi-droplet-fill"></i>
                        </div>
                        <div class="blood-info">
                            <h3 class="blood-label"><?php echo $type; ?></h3>
                            <p class="blood-count"><?php echo $count; ?> Donors</p>
                        </div>
                        <div class="blood-indicator">
                            <div class="indicator-bar" style="width: <?php echo min(100, ($count * 10)); ?>%"></div>
                        </div>
                    </div>
                <?php 
                    $delay += 50;
                endforeach; 
                ?>
            </div>
        </div>
    </section>

    <section class="section-about" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-content" data-aos="fade-right">
                    <span class="section-badge">About RedHope</span>
                    <h2 class="section-title">Bridging Lives Through Technology</h2>
                    <p class="about-text">
                        RedHope is a dedicated platform designed to connect blood donors with those in critical need. 
                        We leverage technology to make the donation process more accessible, transparent, and efficient.
                    </p>
                    <p class="about-text">
                        Our mission is simple yet powerful: ensure that no life is lost due to a lack of blood. 
                        Through our network of donors, hospitals, and blood centers, we create a seamless experience 
                        for anyone looking to give the gift of life.
                    </p>
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="bi bi-shield-fill-check"></i>
                            <div>
                                <h4>Verified & Secure</h4>
                                <p>All donors are verified for safety</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-clock-fill"></i>
                            <div>
                                <h4>Fast Response</h4>
                                <p>Quick matching with urgent needs</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-globe"></i>
                            <div>
                                <h4>Wide Network</h4>
                                <p>Connected with major hospitals</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-visual" data-aos="fade-left">
                    <div class="visual-card">
                        <i class="bi bi-heart-pulse-fill"></i>
                        <h3>Be a Hero Today</h3>
                        <p>Your single donation can save up to 3 lives</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-contact" id="contact">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info" data-aos="fade-right">
                    <span class="section-badge">Get in Touch</span>
                    <h2 class="section-title">We're Here to Help</h2>
                    <p class="contact-description">
                        Have questions? We're available 24/7 to assist you with anything you need.
                    </p>

                    <div class="info-items">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="info-details">
                                <h4>Visit Us</h4>
                                <p>123 Heartbeat Ave, RedCity, RC 101</p>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="info-details">
                                <h4>Email Us</h4>
                                <p>contact@redhope.com</p>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div class="info-details">
                                <h4>Call Us</h4>
                                <p>24/7 Emergency Support</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-form-wrapper" data-aos="fade-left">
                    <form action="apis/sendmessage.php" method="POST" class="modern-form">
                        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Message sent successfully!</span>
                            </div>
                        <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
                            <div class="alert alert-error">
                                <i class="bi bi-x-circle-fill"></i>
                                <span><?php echo htmlspecialchars($_GET['msg'] ?? 'Something went wrong'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="form-grid">
                            <div class="form-field">
                                <label><i class="bi bi-person"></i> Your Name</label>
                                <input type="text" name="name" placeholder="John Doe" 
                                    value="<?php echo htmlspecialchars($user_data ? ($user_data['first_name'] . ' ' . $user_data['last_name']) : ''); ?>" required>
                            </div>
                            <div class="form-field">
                                <label><i class="bi bi-telephone"></i> Phone</label>
                                <input type="tel" name="phone" placeholder="+1 234 567 8900"
                                    value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-field">
                            <label><i class="bi bi-envelope"></i> Email Address</label>
                            <input type="email" name="email" placeholder="your.email@example.com"
                                value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-field">
                            <label><i class="bi bi-chat-dots"></i> Subject</label>
                            <input type="text" name="subject" placeholder="How can we help?" required>
                        </div>
                        <div class="form-field">
                            <label><i class="bi bi-pencil"></i> Message</label>
                            <textarea name="message" rows="4" placeholder="Your message here..." required></textarea>
                        </div>
                        <button type="submit" name="contact_submit" class="btn btn-primary btn-block">
                            <i class="bi bi-send-fill"></i>
                            <span>Send Message</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
