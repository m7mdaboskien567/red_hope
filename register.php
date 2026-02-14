<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RedHope</title>
    <?php include  __DIR__ . "/includes/meta.php"; ?>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>

    <?php include  __DIR__ . "/includes/loader.php"; ?>
    <?php include  __DIR__ . "/includes/header.php"; ?>
    <div class="register-container">
        <div class="register-left">
            <div class="register-decoration">
                <div class="circle circle-1"></div>
                <div class="circle circle-2"></div>
                <div class="circle circle-3"></div>
            </div>
            <div class="register-content">
                <a href="index.php" class="back-home">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
                <div class="logo-section">
                    <i class="bi bi-heart-pulse"></i>
                    <h1>RedHope</h1>
                </div>
                <h2 class="welcome-text animate__animated animate__fadeInUp">Join Our Community</h2>
                <p class="subtitle animate__animated animate__fadeInUp animate__delay-1s">
                    Create an account and start making a difference
                </p>
                
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <div class="step-label">User Type</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Personal Info</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Contact</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">4</div>
                        <div class="step-label">Details</div>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-circle">5</div>
                        <div class="step-label">Security</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="register-right">
            <div class="form-container animate__animated animate__fadeInRight">
                <form id="registerForm" class="register-form">
                    <div class="form-step active" data-step="1">
                        <h3>Select Your Role</h3>
                        <p class="step-description">Choose how you want to contribute</p>
                        
                        <div class="user-type-selection">
                            <label class="user-type-card">
                                <input type="radio" name="userType" value="donor" required>
                                <div class="card-content">
                                    <div class="icon">
                                        <i class="bi bi-heart-fill"></i>
                                    </div>
                                    <h4>Donor</h4>
                                    <p>I want to donate blood and save lives</p>
                                </div>
                                <div class="check-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </label>
                            
                            <label class="user-type-card">
                                <input type="radio" name="userType" value="hospital" required>
                                <div class="card-content">
                                    <div class="icon">
                                        <i class="bi bi-hospital"></i>
                                    </div>
                                    <h4>Hospital Admin</h4>
                                    <p>I represent a medical facility</p>
                                </div>
                                <div class="check-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="form-step" data-step="2">
                        <h3>Personal Information</h3>
                        <p class="step-description">Tell us your name</p>
                        
                        <div class="form-group">
                            <label for="firstName">
                                <i class="bi bi-person"></i>
                                First Name
                            </label>
                            <input 
                                type="text" 
                                id="firstName" 
                                class="form-control" 
                                placeholder="John"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName">
                                <i class="bi bi-person"></i>
                                Last Name
                            </label>
                            <input 
                                type="text" 
                                id="lastName" 
                                class="form-control" 
                                placeholder="Doe"
                                required
                            >
                        </div>
                    </div>
                    <div class="form-step" data-step="3">
                        <h3>Contact Information</h3>
                        <p class="step-description">How can we reach you?</p>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="bi bi-envelope"></i>
                                Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                class="form-control" 
                                placeholder="john.doe@example.com"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">
                                <i class="bi bi-telephone"></i>
                                Phone Number
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                class="form-control" 
                                placeholder="+1 234 567 8900"
                                required
                            >
                        </div>
                    </div>
                    <div class="form-step" data-step="4">
                        <h3>Additional Details</h3>
                        <p class="step-description">Help us know you better</p>
                        
                        <div class="form-group">
                            <label>
                                <i class="bi bi-gender-ambiguous"></i>
                                Gender
                            </label>
                            <div class="gender-selection">
                                <label class="gender-option">
                                    <input type="radio" name="gender" value="male" required>
                                    <span><i class="bi bi-gender-male"></i> Male</span>
                                </label>
                                <label class="gender-option">
                                    <input type="radio" name="gender" value="female" required>
                                    <span><i class="fas fa-venus"></i> Female</span>
                                </label>
                                <label class="gender-option">
                                    <input type="radio" name="gender" value="other" required>
                                    <span><i class="bi bi-gender-trans"></i> Other</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="dob">
                                <i class="bi bi-calendar-event"></i>
                                Date of Birth
                            </label>
                            <input 
                                type="date" 
                                id="dob" 
                                class="form-control"
                                required
                            >
                        </div>
                        
                        <div class="form-group blood-type-group">
                            <label>
                                <i class="bi bi-droplet"></i>
                                Blood Type
                            </label>
                            <div class="blood-type-selection">
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="A+">
                                    <span>A+</span>
                                </label>
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="A-">
                                    <span>A-</span>
                                </label>
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="B+">
                                    <span>B+</span>
                                </label>
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="B-">
                                    <span>B-</span>
                                </label>
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="AB+">
                                    <span>AB+</span>
                                </label>
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="AB-">
                                    <span>AB-</span>
                                </label>
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="O+">
                                    <span>O+</span>
                                </label>
                                <label class="blood-type-option">
                                    <input type="radio" name="bloodType" value="O-">
                                    <span>O-</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-step" data-step="5">
                        <h3>Security & Agreement</h3>
                        <p class="step-description">Secure your account</p>
                        
                        <div class="form-group">
                            <label for="password">
                                <i class="bi bi-lock"></i>
                                Password
                            </label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="password" 
                                    class="form-control" 
                                    placeholder="Minimum 8 characters"
                                    required
                                >
                                <button type="button" class="toggle-password" data-target="password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword">
                                <i class="bi bi-lock"></i>
                                Confirm Password
                            </label>
                            <div class="password-input">
                                <input 
                                    type="password" 
                                    id="confirmPassword" 
                                    class="form-control" 
                                    placeholder="Re-enter your password"
                                    required
                                >
                                <button type="button" class="toggle-password" data-target="confirmPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="terms" required>
                                <span>I agree to the <a href="/redhope/terms-of-services.php">Terms of Service</a> and <a href="/redhope/privacy-policy.php">Privacy Policy</a></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-navigation">
                        <button type="button" class="btn-nav btn-prev" id="prevBtn">
                            <i class="bi bi-arrow-left"></i>
                            Previous
                        </button>
                        <button type="button" class="btn-nav btn-next" id="nextBtn">
                            Next
                            <i class="bi bi-arrow-right"></i>
                        </button>
                        <button type="submit" class="btn-nav btn-submit" id="submitBtn" style="display: none;">
                            Create Account
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </div>
                    
                    <div class="login-link">
                        Already have an account? <a href="login.php">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php include  __DIR__ . "/includes/footer.php"; ?>
    <script src="assets/js/register.js"></script>
</body>
</html>
