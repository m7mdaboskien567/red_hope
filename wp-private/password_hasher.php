<?php
$hashed = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"] ?? "";
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Hasher</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0A4D68, #05BFDB);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,.3);
        }
        .hash-box {
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card p-4" style="width: 420px;">
    <h4 class="text-center mb-3">
        <i class="fa-solid fa-lock"></i> Password Hasher
    </h4>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100">
            <i class="fa-solid fa-hammer"></i> Generate Hash
        </button>
    </form>

    <?php if ($hashed): ?>
        <hr>
        <label class="form-label mt-2">Hashed Password</label>
        <textarea class="form-control hash-box" rows="3" id="hash" readonly><?= $hashed ?></textarea>

        <button class="btn btn-success w-100 mt-2" onclick="copyHash()">
            <i class="fa-solid fa-copy"></i> Copy Hash
        </button>
    <?php endif; ?>
</div>

<script>
function copyHash() {
    const hash = document.getElementById("hash");
    hash.select();
    document.execCommand("copy");
}
</script>

</body>
</html>
