<?php
/**
 * หน้า Login
 * ระบบจัดการอัตราเบี้ยประกัน
 */

session_start();

// ถ้า login แล้วให้ไปหน้าหลัก
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

// ตรวจสอบ login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // ตรวจสอบ credentials
    if ($username === 'admin' && $password === 'mida4825') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();

        header('Location: index.php');
        exit;
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | Loan Protect Rate</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/modern-style.css">

    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }

        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .login-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .login-body {
            padding: 2rem;
        }

        .login-form .form-group {
            margin-bottom: 1.5rem;
        }

        .login-form .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #475569;
        }

        .login-form .form-control {
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border-radius: 12px;
        }

        .login-form .btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            border-radius: 12px;
        }

        .login-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            background: rgba(241, 245, 249, 0.5);
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }

        .login-footer p {
            margin: 0;
            color: #64748B;
            font-size: 0.85rem;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: #DC2626;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            padding: 0.25rem;
        }

        .password-toggle .toggle-btn:hover {
            color: #3B82F6;
        }

        .password-toggle .form-control {
            padding-right: 2.5rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fa-solid fa-shield-heart"></i>
                <h1>Loan Protect Rate</h1>
                <p>ระบบจัดการอัตราเบี้ยประกัน</p>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-user"></i>
                            ชื่อผู้ใช้
                        </label>
                        <input type="text" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้" required
                            autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-lock"></i>
                            รหัสผ่าน
                        </label>
                        <div class="password-toggle">
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="กรอกรหัสผ่าน" required>
                            <button type="button" class="toggle-btn" onclick="togglePassword()">
                                <i class="fa-solid fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        เข้าสู่ระบบ
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p>&copy;
                    <?= date('Y') ?> Loan Protect Rate Management System
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>