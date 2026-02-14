<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? '';
?>
<script>
    window.PHP_SESSION_ACTIVE = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
</script>
<header>
    <nav>
        <div class="navpart logoNav">
            <h1><a href="/redhope/">RedHope</a></h1>
        </div>
        <div class="navpart btnNav">
            <span id="menuBtn" onclick="openCloseMenu()">
                <p>Menu</p>
            </span>
            <div id="authNav" style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                <span id="themeToggle" onclick="toggleTheme()" title="Toggle Theme">
                    <p><i class="bi bi-moon-fill"></i></p>
                </span>
                <?php if ($is_logged_in): ?>
                    <span id="dashboardBtn" onclick="location.href='/redhope/dashboard.php'" title="Dashboard">
                        <p><i class="bi bi-person-fill"></i></p>
                    </span>
                    <span id="logoutBtn" onclick="logoutUser()" title="Logout">
                        <p><i class="bi bi-box-arrow-right"></i></p>
                    </span>
                <?php else: ?>
                    <span id="loginBtn" onclick="location.href='/redhope/login.php'" title="Login">
                        <p><i class="bi bi-box-arrow-in-right"></i></p>
                    </span>
                    <span id="registerBtn" onclick="location.href='/redhope/register.php'" title="Register">
                        <p><i class="bi bi-person-plus"></i></p>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<div class="menu" id="menu">
    <ul class="menu-items" id="mainMenu">
        <li onclick="location.href='/redhope/'">Home</li>
        <?php if (!$is_logged_in): ?>
            <li onclick="location.href='/redhope/login.php'">Login</li>
            <li onclick="location.href='/redhope/register.php'">Register</li>
        <?php else: ?>
            <?php if ($user_role === 'Donor'): ?>
                <li onclick="location.href='/redhope/dashboard/donator/'">Dashboard</li>
                <li onclick="location.href='/redhope/dashboard/donator/?tab=donate'">Donate</li>
                <li onclick="location.href='/redhope/dashboard/donator/?tab=requests'">Requests</li>
                <li onclick="location.href='/redhope/dashboard/donator/?tab=appointments'">Appointments</li>
                <li onclick="location.href='/redhope/dashboard/donator/?tab=history'">History</li>
            <?php elseif ($user_role === 'Hospital Admin'): ?>
                <li onclick="location.href='/redhope/dashboard/hospital_admin/'">Dashboard</li>
                <li onclick="location.href='/redhope/dashboard/hospital_admin/?tab=requests'">Requests</li>
                <li onclick="location.href='/redhope/dashboard/hospital_admin/?tab=inventory'">Inventory</li>
            <?php elseif ($user_role === 'Super Admin'): ?>
                <li onclick="location.href='/redhope/admin/'">Dashboard</li>
                <li onclick="location.href='/redhope/admin/?tab=users'">Users</li>
                <li onclick="location.href='/redhope/admin/?tab=hospitals'">Hospitals</li>
                <li onclick="location.href='/redhope/admin/?tab=centers'">Centers</li>
            <?php endif; ?>
            <li onclick="logoutUser()">Logout</li>
        <?php endif; ?>
    </ul>
</div>

<script>
    const menuBtn = document.getElementById("menuBtn");
    const menu = document.getElementById("menu");

    function openCloseMenu() {
        menu.classList.toggle("active");
    }

    function closeModalWithAnimation(modalId) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) return;
        modalElement.classList.add('modal-exit');

        modalElement.addEventListener('animationend', () => {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) modalInstance.hide();
            modalElement.classList.remove('modal-exit');
        }, { once: true });
    }
</script>