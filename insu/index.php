<?php
session_start();
if ($_SESSION["sess_login"] == "") {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบคำนวณสินเชื่อ</title>

  <!-- Google Fonts - Prompt -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Modern Style -->
  <link href="css/modern-style.css" rel="stylesheet">

  <style>
    .main-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--spacing-lg);
    }

    .main-card {
      width: 100%;
      max-width: 450px;
    }

    .welcome-text {
      text-align: center;
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: var(--spacing-xl);
    }

    .user-badge {
      display: inline-flex;
      align-items: center;
      gap: var(--spacing-sm);
      padding: 0.4rem 1rem;
      background: rgba(67, 97, 238, 0.2);
      border-radius: 100px;
      color: var(--primary);
      font-weight: 500;
    }

    .menu-section-title {
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--text-muted);
      margin-bottom: var(--spacing-md);
      padding-left: var(--spacing-sm);
    }

    .floating-shape {
      position: fixed;
      border-radius: 50%;
      opacity: 0.08;
      pointer-events: none;
    }

    .shape-1 {
      width: 400px;
      height: 400px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      top: -150px;
      right: -150px;
      animation: float 12s ease-in-out infinite;
    }

    .shape-2 {
      width: 300px;
      height: 300px;
      background: linear-gradient(135deg, var(--accent), var(--secondary));
      bottom: -100px;
      left: -100px;
      animation: float 15s ease-in-out infinite reverse;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0) rotate(0deg);
      }

      50% {
        transform: translateY(-30px) rotate(15deg);
      }
    }
  </style>
</head>

<body>
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>

  <div class="main-container">
    <div class="main-card glass-card fade-in">

      <!-- Header -->
      <div class="page-header">
        <h1 class="page-title">ระบบคำนวณสินเชื่อ</h1>
        <a href="logout.php" class="logout-btn" title="Logout">
          <i class="fas fa-power-off"></i>
        </a>
      </div>

      <!-- Welcome -->
      <div class="welcome-text fade-in-delay-1">
        <span class="user-badge">
          <i class="fas fa-user-circle"></i>
          <?php echo $_SESSION["sess_login"]; ?>
        </span>
      </div>

      <!-- Menu Title -->
      <p class="menu-section-title fade-in-delay-1">Select Service</p>

      <!-- Menu Grid -->
      <div class="menu-grid">

        <!-- Hire Purchase -->
        <a href="hirepurchase.php" class="menu-card fade-in-delay-2">
          <div class="menu-card-icon">
            <i class="fas fa-car"></i>
          </div>
          <span class="menu-card-title">สินเชื่อเช่าซื้อรถยนต์</span>
        </a>

        <!-- P-Loan -->
        <a href="ploan.php?prod=005" class="menu-card fade-in-delay-2">
          <div class="menu-card-icon" style="background: linear-gradient(135deg, #7209B7 0%, #F72585 100%);">
            <i class="fas fa-hand-holding-dollar"></i>
          </div>
          <span class="menu-card-title">สินชื่อจำนำทะเบียน</span>
        </a>

        <!-- TLife -->
        <a href="https://sales.tlife.co.th" target="_blank" class="menu-card fade-in-delay-3">
          <div class="menu-card-icon" style="background: linear-gradient(135deg, #06D6A0 0%, #118AB2 100%);">
            <i class="fas fa-shield-halved"></i>
          </div>
          <span class="menu-card-title">TLife ตรวจสอบรับทำประกัน</span>
        </a>

      </div>

    </div>
  </div>

  <script src="js/jquery-1.10.2.min.js"></script>
</body>

</html>