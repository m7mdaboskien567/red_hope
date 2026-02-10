<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

switch ($role) {
    case 'Donor':
        header("Location: dashboard/donator/");
        break;
    case 'Hospital Admin':
        header("Location: dashboard/hospital_admin/");
        break;
    case 'Super Admin':
        header("Location: admin/");
        break;
    default:
        header("Location: login.php");
        break;
}
exit();

