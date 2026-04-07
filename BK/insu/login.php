<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Loan Calculator</title>

  <!-- Google Fonts - Prompt -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Modern Style -->
  <link href="css/modern-style.css" rel="stylesheet">

  <style>
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--spacing-lg);
    }

    .login-card {
      width: 100%;
      max-width: 400px;
    }

    .login-logo {
      width: 80px;
      height: 80px;
      margin: 0 auto var(--spacing-lg);
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      border-radius: var(--radius-xl);
      font-size: 2.5rem;
      color: white;
      box-shadow: 0 10px 30px rgba(67, 97, 238, 0.4);
    }

    .login-title {
      text-align: center;
      margin-bottom: var(--spacing-xs);
    }

    .login-subtitle {
      text-align: center;
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: var(--spacing-xl);
    }

    .login-form .form-group {
      margin-bottom: var(--spacing-lg);
    }

    .login-btn {
      margin-top: var(--spacing-md);
    }

    .error-message {
      text-align: center;
      padding: var(--spacing-md);
      background: rgba(239, 71, 111, 0.1);
      border: 1px solid rgba(239, 71, 111, 0.3);
      border-radius: var(--radius-md);
      margin-top: var(--spacing-lg);
      display: none;
    }

    .error-message.show {
      display: block;
      animation: fadeInUp 0.3s ease-out;
    }

    .floating-shape {
      position: fixed;
      border-radius: 50%;
      opacity: 0.1;
      pointer-events: none;
    }

    .shape-1 {
      width: 300px;
      height: 300px;
      background: var(--primary);
      top: -100px;
      right: -100px;
      animation: float 8s ease-in-out infinite;
    }

    .shape-2 {
      width: 200px;
      height: 200px;
      background: var(--accent);
      bottom: -50px;
      left: -50px;
      animation: float 10s ease-in-out infinite reverse;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0) rotate(0deg);
      }

      50% {
        transform: translateY(-20px) rotate(10deg);
      }
    }
  </style>
</head>

<body>
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>

  <div class="login-container">
    <div class="login-card glass-card fade-in">

      <div class="login-logo">
        <i class="fas fa-calculator"></i>
      </div>

      <h1 class="login-title">เข้าสู่ระบบ</h1>
      <p class="login-subtitle">ระบบคำนวณสินเชื่อ</p>

      <form class="login-form" onsubmit="return false;">

        <div class="form-group fade-in-delay-1">
          <label class="form-label">ชื่อผู้ใช้</label>
          <div class="input-group">
            <i class="fas fa-user input-icon"></i>
            <input type="text" class="form-control" id="username" name="username" placeholder="กรอกชื่อผู้ใช้"
              autocomplete="username" required pattern="[A-Za-z0-9]+"
              oninput="this.value = this.value.replace(/[^A-Za-z0-9]/g, '')">
          </div>
        </div>

        <div class="form-group fade-in-delay-2">
          <label class="form-label">รหัสผ่าน</label>
          <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" class="form-control" id="passwd" name="passwd" placeholder="กรอกรหัสผ่าน"
              autocomplete="current-password" required pattern="[A-Za-z0-9]+"
              oninput="this.value = this.value.replace(/[^A-Za-z0-9]/g, '')">
          </div>
        </div>

        <button type="button" class="btn btn-primary btn-block btn-lg login-btn fade-in-delay-3" onclick="formlogin()">
          <i class="fas fa-sign-in-alt"></i>
          เข้าสู่ระบบ
        </button>

      </form>

      <div id="msg" class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        <span id="msgText">Wrong username or password</span>
      </div>

    </div>
  </div>

  <script src="js/jquery-1.10.2.min.js"></script>
  <script>
    function formlogin() {
      var msg = $("#msg");
      var msgText = $("#msgText");
      var btn = $(".login-btn");

      msg.removeClass("show");

      if ($("#username").val() !== "" && $("#passwd").val() !== "") {
        btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> กำลังเข้าสู่ระบบ...');

        $.ajax({
          url: "chkLogin.php",
          type: "POST",
          data: "rand=" + Math.random() + "&username=" + $("#username").val() + "&passwd=" + $("#passwd").val(),
          success: function (data) {
            console.log(data);
            var arrData = data.split("#");
            if (arrData[0] === "pass") {
              window.location.href = "index.php";
            } else if (arrData[0] === "db_error") {
              msgText.text(arrData[1] || "ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
              msg.addClass("show");
              btn.prop("disabled", false).html('<i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ');
            } else {
              msgText.text(arrData[1] || "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
              msg.addClass("show");
              btn.prop("disabled", false).html('<i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ');
            }
          },
          error: function () {
            msgText.text("เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง");
            msg.addClass("show");
            btn.prop("disabled", false).html('<i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ');
          }
        });
      } else {
        msgText.text("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน");
        msg.addClass("show");
      }
    }

    $(document).on("keypress", function (e) {
      if (e.which === 13) {
        formlogin();
      }
    });
  </script>
</body>

</html>