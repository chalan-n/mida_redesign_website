<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mida Blue Book - ราคากลางรถยนต์</title>
  <link rel="shortcut icon" href="favicon.ico">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Modern Style CSS -->
  <link href="css/modern-style.css" rel="stylesheet">

  <meta name="description" content="ระบบราคากลางรถยนต์ Mida Blue Book สำหรับการประเมินมูลค่ารถยนต์">
  <meta name="theme-color" content="#1e293b">

  <!-- Open Graph -->
  <meta property="og:title" content="Mida Blue Book - ราคากลางรถยนต์">
  <meta property="og:description" content="ระบบราคากลางรถยนต์สำหรับการประเมินมูลค่ารถยนต์">
  <meta property="og:type" content="website">
</head>

<body>
  <!-- Navigation -->
  <nav class="navbar">
    <div class="navbar-content">
      <a href="#" class="navbar-brand" onclick="BluebookApp.loadHome(); return false;">
        <i class="fas fa-book navbar-icon"></i>
        <span id="webname">Mida Blue Book</span>
      </a>
      <span class="navbar-version" id="version">v.2025</span>
    </div>
  </nav>

  <!-- Main Container -->
  <main class="main-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb" id="breadcrumb">
      <a href="#" class="breadcrumb-item active" onclick="BluebookApp.loadHome(); return false;">
        <i class="fas fa-home"></i> หน้าแรก
      </a>
    </div>

    <!-- Dynamic Content Area -->
    <div id="content">
      <div class="loading">
        <div class="loading-spinner"></div>
        <span>กำลังโหลด...</span>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer style="text-align: center; padding: 20px; color: var(--gray-500); font-size: 0.875rem;">
    <p>&copy; 2025 Mida Leasing - Blue Book System</p>
  </footer>

  <!-- App JavaScript -->
  <script src="js/app.js"></script>
</body>

</html>