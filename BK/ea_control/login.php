<?php
session_start();

// ถ้า Login อยู่แล้ว ให้เด้งไป Dashboard เลย ไม่ต้องกรอกใหม่
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: dashboard.php");
    exit;
}

// เช็คเมื่อมีการกดปุ่ม Login
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // --- ตรวจสอบ User / Password ตรงนี้ ---
    if ($username === 'admin' && $password === 'nat123') {
        // ถ้ารหัสถูก
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        
        // เด้งไปหน้า Dashboard
        header("location: dashboard.php");
        exit;
    } else {
        // ถ้ารหัสผิด
        $error = "ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EA Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #e9ecef; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">🔐 EA Admin</h3>
        <p class="text-muted">กรุณาเข้าสู่ระบบ</p>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger text-center p-2"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">เข้าสู่ระบบ</button>
    </form>
</div>

</body>
</html>