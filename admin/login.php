<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_msg = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        try {
            $stmt = $db->prepare("SELECT id, username, password, name, role, role_id, is_active FROM admins WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // เช็คว่า user ถูก disable หรือไม่
                if (isset($user['is_active']) && $user['is_active'] == 0) {
                    $error_msg = "บัญชีผู้ใช้นี้ถูกปิดใช้งาน";
                } else {
                    // Login Success
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_role_id'] = $user['role_id'];

                    // Update Last Login
                    $update_stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
                    $update_stmt->execute([':id' => $user['id']]);

                    header("Location: index.php");
                    exit;
                }
            } else {
                $error_msg = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบจัดการเว็บไซต์ | Mida Leasing Admin</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- CSS -->
    <style>
        :root {
            --primary-blue: #002D62;
            --accent-gold: #C5A059;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-logo img {
            max-width: 150px;
            margin-bottom: 20px;
        }

        h2 {
            color: var(--primary-blue);
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            /* Fix padding causing overflow */
            font-family: 'Prompt', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background: #004a99;
        }

        .error-msg {
            background: #fde8e8;
            color: #d32f2f;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .back-home {
            display: block;
            margin-top: 20px;
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-home:hover {
            color: var(--primary-blue);
        }
    </style>
</head>

<body>

    <div class="login-box">
        <div class="login-logo">
            <!-- Reuse main site logo provided path is correct relative to /admin -->
            <img src="../img/mida_logo_5.png" alt="MIDA LEASING">
        </div>
        <h2>ระบบจัดการเว็บไซต์</h2>

        <?php if ($error_msg): ?>
            <div class="error-msg">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่าน (Password)</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
        </form>

        <a href="../index.php" class="back-home"><i class="fa fa-arrow-left"></i> กลับไปหน้าเว็บไซต์หลัก</a>
    </div>

</body>

</html>