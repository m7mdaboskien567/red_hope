<meta name="description" content="Hope begins with a donation">
<meta name="keywords" content="RedHope">
<meta property="fb:app_id" content="">
<meta property="og:url" content="localhost/redhope/">
<meta property="og:type" content="website">
<meta property="og:title" content="RedHope">
<meta property="og:image" content="/redhope/assets/imgs/logo.png">
<meta property="og:description" content="Hope begins with a donation">
<meta property="og:site_name" content="RedHope">
<meta property="og:locale" content="en">
<meta property="article:author" content="RedHope">
<meta itemprop="name" content="RedHope">
<meta itemprop="description" content="Hope begins with a donation">
<meta itemprop="image" content="/redhope/assets/imgs/logo.png">
<link rel="icon" type="image/png" href="/redhope/assets/imgs/logo.png" sizes="96x96">
<link rel="icon" type="image/svg+xml" href="/redhope/assets/imgs/favicon.png">
<link rel="shortcut icon" href="/redhope/assets/imgs/favicon.png">
<link rel="apple-touch-icon" sizes="180x180" href="/redhope/assets/imgs/favicon.png">
<meta name="apple-mobile-web-app-title" content="RedHope">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">

<?php
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$scriptPath = $_SERVER['SCRIPT_NAME'];
$page_id = 'home';

if (strpos($scriptPath, '/dashboard/donator/') !== false) {
    $page_id = 'donor';
} elseif (strpos($scriptPath, '/dashboard/hospital_admin/') !== false) {
    $page_id = 'hospital';
} elseif (strpos($scriptPath, '/admin/') !== false) {
    $page_id = 'admin';
} elseif ($currentPage !== 'index') {
    $page_id = $currentPage;
}
?>
<link rel="stylesheet" href="/redhope/assets/css/responsive.php?p=<?php echo $page_id; ?>">
