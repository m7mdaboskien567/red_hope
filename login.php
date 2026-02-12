
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
    <title>Login - RedHope</title>
<?php include __DIR__ . '/includes/meta.php'; ?>
    <link rel="stylesheet" href="/redhope/assets/css/login-style.css">
</head>
<body>
<?php include __DIR__ . '/includes/loader.php'; ?>
<?php include __DIR__ . '/includes/header.php';?> 
    <div class="login-container">
        <div class="login-left">
            <div class="login-decoration">
                <div class="circle circle-1"></div>
                <div class="circle circle-2"></div>
                <div class="circle circle-3"></div>
            </div>
            <div class="login-content">
                <a href="index.php" class="back-home">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
                <div class="logo-section">
                    <i class="bi bi-heart-pulse"></i>
                    <h1>RedHope</h1>
                </div>
                <h2 class="welcome-text animate__animated animate__fadeInUp">Welcome Back!</h2>
                <p class="subtitle animate__animated animate__fadeInUp animate__delay-1s">
                    Sign in to continue saving lives
                </p>
            </div>
        </div>
        
        <div class="login-right">
            <div class="form-container animate__animated animate__fadeInRight">
                <h3>Sign In</h3>
                
                <form id="loginForm" class="login-form">
                    <div class="form-group">
                        <label for="email">
                            <i class="bi bi-envelope"></i>
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            class="form-control" 
                            placeholder="your.email@example.com"
                            required
                        >
                    </div>
                    
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
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" id="rememberMe">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        Sign In
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    
                    <div class="divider">
                        <span>or continue with</span>
                    </div>
                    
                    <div class="social-login">
                        <button type="button" class="social-btn google">
                            <i class="bi bi-google"></i>
                            Google
                        </button>
                        <button type="button" class="social-btn facebook">
                            <i class="bi bi-facebook"></i>
                            Facebook
                        </button>
                    </div>
                    
                    <div class="signup-link">
                        Don't have an account? <a href="register.php">Sign Up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/includes/footer.php"; ?>
    <script src="assets/js/login-script.js"></script>
</body>
</html>
