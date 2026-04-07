<?php
/**
 * หน้าหลัก - แสดงตารางอัตราเบี้ยประกัน
 * รูปแบบ Matrix: แถว=ช่วงอายุ, คอลัมน์=ระยะเวลา (ปี)
 * พร้อมฟังก์ชันจัดการข้อมูล (เพิ่ม/แก้ไข/ลบ)
 */

session_start();

// ตรวจสอบ login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

$conn = getConnection();

// ข้อความแจ้งเตือน
$message = '';
$messageType = '';

// ลบข้อมูล
if (isset($_GET['delete']) && isset($_GET['confirm'])) {
    $parts = explode('|', $_GET['delete']);
    if (count($parts) >= 5) {
        $sql = "DELETE FROM loanprotectrate 
                WHERE Rate_date = :rate_date 
                AND CmpInsuranceID = :company 
                AND SexID = :sex 
                AND age_from = :age_from 
                AND Term_year = :term_year";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'rate_date' => $parts[0],
            'company' => $parts[1],
            'sex' => $parts[2],
            'age_from' => $parts[3],
            'term_year' => $parts[4]
        ]);

        if ($result) {
            $message = 'ลบข้อมูลสำเร็จ';
            $messageType = 'success';
        } else {
            $message = 'เกิดข้อผิดพลาดในการลบข้อมูล';
            $messageType = 'danger';
        }
    }
}

// ตั้งค่าสถานะชุดข้อมูล (Active/Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_status'])) {
    $statusCompany = $_POST['status_company'] ?? '';
    $statusDate = $_POST['status_date'] ?? '';
    $newStatus = $_POST['new_status'] ?? 'A';
    
    if ($statusCompany && $statusDate) {
        // ถ้าตั้งเป็น Active ให้ Cancel ชุดอื่นทั้งหมดของบริษัทนี้ก่อน
        if ($newStatus === 'A') {
            $cancelSql = "UPDATE loanprotectrate SET Status = 'C' 
                         WHERE CmpInsuranceID = :company AND Rate_date != :rate_date";
            $cancelStmt = $conn->prepare($cancelSql);
            $cancelStmt->execute(['company' => $statusCompany, 'rate_date' => $statusDate]);
        }
        
        // อัปเดตสถานะชุดที่เลือก
        $updateSql = "UPDATE loanprotectrate SET Status = :status 
                     WHERE CmpInsuranceID = :company AND Rate_date = :rate_date";
        $updateStmt = $conn->prepare($updateSql);
        $result = $updateStmt->execute([
            'status' => $newStatus,
            'company' => $statusCompany,
            'rate_date' => $statusDate
        ]);
        
        if ($result) {
            $statusText = $newStatus === 'A' ? 'ใช้งาน' : 'ไม่ใช้งาน';
            $message = "ตั้งค่าสถานะ \"{$statusText}\" สำเร็จ";
            $messageType = 'success';
        } else {
            $message = 'เกิดข้อผิดพลาดในการตั้งค่าสถานะ';
            $messageType = 'danger';
        }
    }
}

// บันทึกข้อมูล (จาก Modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rate'])) {
    $rateDate = str_replace('-', '', $_POST['rate_date'] ?? '');
    $company = $_POST['company'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $ageFrom = intval($_POST['age_from'] ?? 0);
    $ageTo = intval($_POST['age_to'] ?? 0);
    $termYear = intval($_POST['term_year'] ?? 0);
    $termMonth = $termYear * 12; // แปลงปีเป็นเดือนอัตโนมัติ
    $rate = floatval($_POST['rate'] ?? 0);
    $status = $_POST['status'] ?? 'A';
    $perComm = floatval($_POST['per_comm'] ?? 0);

    // ถ้ามี original_id แสดงว่าเป็นการแก้ไข
    if (isset($_POST['original_id']) && $_POST['original_id']) {
        $origParts = explode('|', $_POST['original_id']);
        if (count($origParts) >= 5) {
            $delSql = "DELETE FROM loanprotectrate 
                       WHERE Rate_date = :rate_date 
                       AND CmpInsuranceID = :company 
                       AND SexID = :sex 
                       AND age_from = :age_from 
                       AND Term_year = :term_year";
            $delStmt = $conn->prepare($delSql);
            $delStmt->execute([
                'rate_date' => $origParts[0],
                'company' => $origParts[1],
                'sex' => $origParts[2],
                'age_from' => $origParts[3],
                'term_year' => $origParts[4]
            ]);
        }
    }

    // เพิ่มข้อมูลใหม่
    $sql = "INSERT INTO loanprotectrate 
            (Rate_date, CmpInsuranceID, SexID, age_from, age_to, Term_year, Term_month, Rate, Status, PerComm) 
            VALUES (:rate_date, :company, :sex, :age_from, :age_to, :term_year, :term_month, :rate, :status, :per_comm)";
    $stmt = $conn->prepare($sql);

    try {
        $result = $stmt->execute([
            'rate_date' => $rateDate,
            'company' => $company,
            'sex' => $sex,
            'age_from' => $ageFrom,
            'age_to' => $ageTo,
            'term_year' => $termYear,
            'term_month' => $termMonth,
            'rate' => $rate,
            'status' => $status,
            'per_comm' => $perComm
        ]);

        if ($result) {
            $message = isset($_POST['original_id']) && $_POST['original_id'] ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มข้อมูลสำเร็จ';
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = 'ข้อมูลซ้ำ: มีข้อมูลนี้อยู่ในระบบแล้ว';
        } else {
            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
        $messageType = 'danger';
    }
}

// โคลนข้อมูลจากวันที่เดิมเป็นวันที่ใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clone_data'])) {
    $sourceDate = $_POST['source_date'] ?? '';
    $targetDate = str_replace('-', '', $_POST['target_date'] ?? '');
    $cloneCompany = $_POST['clone_company'] ?? '';

    if ($sourceDate && $targetDate && $cloneCompany) {
        // ดึงข้อมูลจากวันที่เดิม
        $sql = "SELECT * FROM loanprotectrate WHERE Rate_date = :source_date AND CmpInsuranceID = :company";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['source_date' => $sourceDate, 'company' => $cloneCompany]);
        $sourceData = $stmt->fetchAll();

        if (!empty($sourceData)) {
            $insertCount = 0;
            $skipCount = 0;

            foreach ($sourceData as $row) {
                // ตรวจสอบว่ามีข้อมูลซ้ำหรือไม่
                $checkSql = "SELECT COUNT(*) FROM loanprotectrate 
                             WHERE Rate_date = :rate_date 
                             AND CmpInsuranceID = :company 
                             AND SexID = :sex 
                             AND age_from = :age_from 
                             AND Term_year = :term_year";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->execute([
                    'rate_date' => $targetDate,
                    'company' => $row['CmpInsuranceID'],
                    'sex' => $row['SexID'],
                    'age_from' => $row['age_from'],
                    'term_year' => $row['Term_year']
                ]);

                if ($checkStmt->fetchColumn() == 0) {
                    // ไม่ซ้ำ - เพิ่มข้อมูลใหม่
                    $insertSql = "INSERT INTO loanprotectrate 
                                  (Rate_date, CmpInsuranceID, SexID, age_from, age_to, Term_year, Term_month, Rate, Status, PerComm) 
                                  VALUES (:rate_date, :company, :sex, :age_from, :age_to, :term_year, :term_month, :rate, :status, :per_comm)";
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->execute([
                        'rate_date' => $targetDate,
                        'company' => $row['CmpInsuranceID'],
                        'sex' => $row['SexID'],
                        'age_from' => $row['age_from'],
                        'age_to' => $row['age_to'],
                        'term_year' => $row['Term_year'],
                        'term_month' => $row['Term_month'],
                        'rate' => $row['Rate'],
                        'status' => $row['Status'],
                        'per_comm' => $row['PerComm']
                    ]);
                    $insertCount++;
                } else {
                    $skipCount++;
                }
            }

            $message = "โคลนข้อมูลสำเร็จ: เพิ่ม {$insertCount} รายการ" . ($skipCount > 0 ? " (ข้าม {$skipCount} รายการที่ซ้ำ)" : "");
            $messageType = 'success';
        } else {
            $message = 'ไม่พบข้อมูลจากวันที่ต้นทาง';
            $messageType = 'warning';
        }
    } else {
        $message = 'กรุณาระบุข้อมูลให้ครบถ้วน';
        $messageType = 'danger';
    }
}

// ดึงรายการบริษัทประกันที่มีข้อมูล
$companies = [];
$rateDates = [];

if ($conn) {
    // ดึงบริษัทประกัน
    $stmt = $conn->query("SELECT DISTINCT CmpInsuranceID FROM loanprotectrate ORDER BY CmpInsuranceID");
    $companies = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // ดึงวันที่มีผล
    $stmt = $conn->query("SELECT DISTINCT Rate_date FROM loanprotectrate ORDER BY Rate_date DESC");
    $rateDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ค่าเริ่มต้น - TLIFE (03) และวันที่ล่าสุด
$selectedCompany = isset($_GET['company']) ? $_GET['company'] : null;
if (!$selectedCompany) {
    // ถ้าไม่มี query string ให้ใช้ TLIFE เป็นค่าเริ่มต้น
    $selectedCompany = in_array('03', $companies) ? '03' : ($companies[0] ?? '03');
}
$selectedDate = $_GET['rate_date'] ?? ($rateDates[0] ?? '');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการอัตราเบี้ยประกัน | Loan Protect Rate</title>

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
        /* Editable Cell Styles */
        .cell-editable {
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        .cell-editable:hover {
            background: rgba(59, 130, 246, 0.15) !important;
            transform: scale(1.05);
        }

        .cell-editable:hover::after {
            content: '\f303';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.6rem;
            color: var(--primary);
            opacity: 0.7;
        }

        .cell-empty {
            color: var(--text-muted);
            font-style: italic;
        }

        .cell-empty:hover {
            background: rgba(16, 185, 129, 0.15) !important;
        }

        .cell-empty:hover::after {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.6rem;
            color: var(--success);
            opacity: 0.7;
        }

        /* Action buttons in header */
        .table-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Rate info tooltip */
        .rate-info {
            font-size: 0.7rem;
            color: var(--text-muted);
            display: block;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-shield-heart"></i>
                <span>Loan Protect Rate</span>
            </a>
            <nav class="nav">
                <a href="index.php" class="nav-link active">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>ตารางอัตราเบี้ยประกัน</span>
                </a>
                <a href="logout.php" class="nav-link" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa-solid fa-table-cells-large"></i>
                    ตารางอัตราเบี้ยประกัน
                </h1>
                <p class="page-subtitle">คลิกที่ตัวเลขในตารางเพื่อแก้ไข หรือคลิกช่องว่างเพื่อเพิ่มข้อมูล</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <i class="fa-solid fa-<?= $messageType === 'success' ? 'check-circle' : 'circle-exclamation' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <form class="filter-section" method="GET" id="filterForm">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fa-solid fa-building"></i> บริษัทประกัน
                    </label>
                    <select name="company" class="form-control" onchange="this.form.submit()">
                        <option value="01" <?= $selectedCompany === '01' ? 'selected' : '' ?>>AIA</option>
                        <option value="02" <?= $selectedCompany === '02' ? 'selected' : '' ?>>CHUBB</option>
                        <option value="03" <?= $selectedCompany === '03' ? 'selected' : '' ?>>TLIFE</option>
                        <option value="00" <?= $selectedCompany === '00' ? 'selected' : '' ?>>ไม่มี (NO)</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fa-solid fa-calendar-days"></i> วันที่มีผล
                    </label>
                    <select name="rate_date" class="form-control" onchange="this.form.submit()">
                        <?php if (empty($rateDates)): ?>
                            <option value="">-- ยังไม่มีข้อมูล --</option>
                        <?php endif; ?>
                        <?php foreach ($rateDates as $date): ?>
                            <?php
                            $displayDate = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
                            ?>
                            <option value="<?= htmlspecialchars($date) ?>" <?= $date === $selectedDate ? 'selected' : '' ?>>
                                <?= $displayDate ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group" style="justify-content: flex-end;">
                    <label class="filter-label">&nbsp;</label>
                    <div class="table-actions">
                        <button type="button" class="btn btn-success" onclick="openAddModal()">
                            <i class="fa-solid fa-plus"></i> เพิ่มข้อมูล
                        </button>
                        <button type="button" class="btn btn-outline" onclick="openCloneModal()">
                            <i class="fa-solid fa-copy"></i> โคลนข้อมูล
                        </button>
                    </div>
                </div>
            </form>

            <?php if ($conn && $selectedCompany): ?>
                <?php
                // ดึงสถานะของชุดข้อมูลปัจจุบัน
                $currentStatus = 'A';
                if ($selectedDate) {
                    $statusSql = "SELECT Status FROM loanprotectrate 
                                  WHERE CmpInsuranceID = :company AND Rate_date = :rate_date LIMIT 1";
                    $statusStmt = $conn->prepare($statusSql);
                    $statusStmt->execute(['company' => $selectedCompany, 'rate_date' => $selectedDate]);
                    $statusRow = $statusStmt->fetch();
                    $currentStatus = $statusRow['Status'] ?? 'A';
                }
                
                // ดึงข้อมูลอัตราเบี้ย (แสดงทั้งหมดไม่ว่าสถานะอะไร)
                $sql = "SELECT * FROM loanprotectrate 
                        WHERE CmpInsuranceID = :company 
                        " . ($selectedDate ? "AND Rate_date = :rate_date" : "") . "
                        ORDER BY SexID, age_from, Term_year";
                $stmt = $conn->prepare($sql);
                $params = ['company' => $selectedCompany];
                if ($selectedDate) {
                    $params['rate_date'] = $selectedDate;
                }
                $stmt->execute($params);
                $rates = $stmt->fetchAll();

                // จัดกลุ่มข้อมูลตามเพศ
                $maleRates = [];
                $femaleRates = [];
                $maleData = [];
                $femaleData = [];
                $termYears = [];
                $ageRanges = [];

                foreach ($rates as $rate) {
                    $ageKey = $rate['age_from'] . '-' . $rate['age_to'];
                    if ($rate['age_from'] == $rate['age_to']) {
                        $ageKey = (string) $rate['age_from'];
                    }
                    $termYear = $rate['Term_year'];

                    if (!in_array($termYear, $termYears)) {
                        $termYears[] = $termYear;
                    }
                    if (!in_array($ageKey, $ageRanges)) {
                        $ageRanges[] = $ageKey;
                    }

                    $rowId = $rate['Rate_date'] . '|' . $rate['CmpInsuranceID'] . '|' . $rate['SexID'] . '|' . $rate['age_from'] . '|' . $rate['Term_year'];

                    if ($rate['SexID'] === '1') {
                        $maleRates[$ageKey][$termYear] = $rate['Rate'];
                        $maleData[$ageKey][$termYear] = $rate;
                    } else {
                        $femaleRates[$ageKey][$termYear] = $rate['Rate'];
                        $femaleData[$ageKey][$termYear] = $rate;
                    }
                }

                sort($termYears);

                // ถ้าไม่มีข้อมูล ให้ใช้ค่าเริ่มต้น
                if (empty($termYears)) {
                    $termYears = [1, 2, 3, 4, 5, 6, 7];
                }
                if (empty($ageRanges)) {
                    $ageRanges = ['20', '21-30', '31-40', '41-45', '46-50', '51-55', '56-60', '61-65', '66-69'];
                }
                ?>

                <!-- Status Bar -->
                <div class="card mb-lg" style="background: <?= $currentStatus === 'A' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' ?>; border: 1px solid <?= $currentStatus === 'A' ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)' ?>;">
                    <div class="card-body" style="padding: 1rem 1.5rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <?php if ($currentStatus === 'A'): ?>
                                    <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #059669;">
                                        <i class="fa-solid fa-circle-check"></i>
                                        ชุดข้อมูลนี้กำลังใช้งาน
                                    </span>
                                <?php else: ?>
                                    <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #DC2626;">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        ชุดข้อมูลนี้ไม่ได้ใช้งาน
                                    </span>
                                <?php endif; ?>
                                <span style="color: var(--text-muted); font-size: 0.9rem;">
                                    (<?= getInsuranceCompanyName($selectedCompany) ?> - <?= substr($selectedDate, 0, 4) ?>-<?= substr($selectedDate, 4, 2) ?>-<?= substr($selectedDate, 6, 2) ?>)
                                </span>
                            </div>
                            <?php if ($currentStatus !== 'A'): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="set_status" value="1">
                                    <input type="hidden" name="status_company" value="<?= htmlspecialchars($selectedCompany) ?>">
                                    <input type="hidden" name="status_date" value="<?= htmlspecialchars($selectedDate) ?>">
                                    <input type="hidden" name="new_status" value="A">
                                    <button type="submit" class="btn btn-success" 
                                            onclick="return confirm('ยืนยันการใช้งานชุดข้อมูลนี้?\n\nหมายเหตุ: ชุดข้อมูลอื่นของบริษัทนี้จะถูกยกเลิกใช้งานโดยอัตโนมัติ')">
                                        <i class="fa-solid fa-check-circle"></i> ตั้งเป็นใช้งาน
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Male Rates Table -->
                <div class="card mb-xl">
                    <div class="card-header flex-between">
                        <h2 class="card-title">
                            <i class="fa-solid fa-mars text-primary"></i>
                            1. อัตราค่าเบี้ยประกัน เพศชาย
                        </h2>
                        <span class="badge badge-primary">
                            <?= count($maleRates) ?> ช่วงอายุ
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table table-matrix">
                                <thead>
                                    <tr>
                                        <th>อายุ</th>
                                        <?php foreach ($termYears as $year): ?>
                                            <th><?= $year ?> ปี</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ageRanges as $age): ?>
                                        <tr>
                                            <td><?= $age ?></td>
                                            <?php foreach ($termYears as $year): ?>
                                                <?php
                                                $hasData = isset($maleRates[$age][$year]);
                                                $rateValue = $hasData ? number_format($maleRates[$age][$year], 2) : '-';
                                                $cellData = $hasData ? $maleData[$age][$year] : null;

                                                // แยก age_from และ age_to
                                                $ageParts = explode('-', $age);
                                                $ageFrom = $ageParts[0];
                                                $ageTo = isset($ageParts[1]) ? $ageParts[1] : $ageParts[0];
                                                ?>
                                                <td class="cell-editable <?= !$hasData ? 'cell-empty' : '' ?>" onclick="openEditModal(<?= htmlspecialchars(json_encode([
                                                          'rate_date' => $selectedDate,
                                                          'company' => $selectedCompany,
                                                          'sex' => '1',
                                                          'age_from' => $ageFrom,
                                                          'age_to' => $ageTo,
                                                          'term_year' => $year,
                                                          'term_month' => $cellData['Term_month'] ?? 0,
                                                          'rate' => $cellData['Rate'] ?? '',
                                                          'status' => $cellData['Status'] ?? 'A',
                                                          'per_comm' => $cellData['PerComm'] ?? 0,
                                                          'is_new' => !$hasData
                                                      ])) ?>)">
                                                    <?= $rateValue ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Female Rates Table -->
                <div class="card">
                    <div class="card-header flex-between">
                        <h2 class="card-title">
                            <i class="fa-solid fa-venus" style="color: #EC4899;"></i>
                            2. อัตราค่าเบี้ยประกัน เพศหญิง
                        </h2>
                        <span class="badge" style="background: rgba(236, 72, 153, 0.1); color: #EC4899;">
                            <?= count($femaleRates) ?> ช่วงอายุ
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table table-matrix">
                                <thead>
                                    <tr>
                                        <th>อายุ</th>
                                        <?php foreach ($termYears as $year): ?>
                                            <th><?= $year ?> ปี</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ageRanges as $age): ?>
                                        <tr>
                                            <td><?= $age ?></td>
                                            <?php foreach ($termYears as $year): ?>
                                                <?php
                                                $hasData = isset($femaleRates[$age][$year]);
                                                $rateValue = $hasData ? number_format($femaleRates[$age][$year], 2) : '-';
                                                $cellData = $hasData ? $femaleData[$age][$year] : null;

                                                // แยก age_from และ age_to
                                                $ageParts = explode('-', $age);
                                                $ageFrom = $ageParts[0];
                                                $ageTo = isset($ageParts[1]) ? $ageParts[1] : $ageParts[0];
                                                ?>
                                                <td class="cell-editable <?= !$hasData ? 'cell-empty' : '' ?>" onclick="openEditModal(<?= htmlspecialchars(json_encode([
                                                          'rate_date' => $selectedDate,
                                                          'company' => $selectedCompany,
                                                          'sex' => '0',
                                                          'age_from' => $ageFrom,
                                                          'age_to' => $ageTo,
                                                          'term_year' => $year,
                                                          'term_month' => $cellData['Term_month'] ?? 0,
                                                          'rate' => $cellData['Rate'] ?? '',
                                                          'status' => $cellData['Status'] ?? 'A',
                                                          'per_comm' => $cellData['PerComm'] ?? 0,
                                                          'is_new' => !$hasData
                                                      ])) ?>)">
                                                    <?= $rateValue ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif (!$conn): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่า
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="fa-solid fa-database"></i>
                            <h3>ยังไม่มีข้อมูล</h3>
                            <p>กรุณาเลือกบริษัทประกันและวันที่มีผล หรือเพิ่มข้อมูลใหม่</p>
                            <button type="button" class="btn btn-primary mt-md" onclick="openAddModal()">
                                <i class="fa-solid fa-plus"></i> เพิ่มข้อมูล
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Legend -->
            <div class="card mt-lg" style="background: rgba(59, 130, 246, 0.03);">
                <div class="card-body" style="padding: 1rem;">
                    <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.9rem;">
                        <span><i class="fa-solid fa-hand-pointer text-primary"></i> คลิกที่ตัวเลขเพื่อแก้ไข</span>
                        <span><i class="fa-solid fa-plus text-success"></i> คลิกที่ "-" เพื่อเพิ่มข้อมูลใหม่</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit/Add Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">
                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                    แก้ไขอัตราเบี้ยประกัน
                </h3>
                <button type="button" class="modal-close" onclick="closeModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="save_rate" value="1">
                <input type="hidden" name="original_id" id="original_id">

                <div class="modal-body">
                    <!-- Hidden fields สำหรับเก็บค่า key -->
                    <input type="hidden" name="rate_date" id="edit_rate_date">
                    <input type="hidden" name="company" id="edit_company">
                    <input type="hidden" name="sex" id="edit_sex">
                    <input type="hidden" name="age_from" id="edit_age_from">
                    <input type="hidden" name="age_to" id="edit_age_to">
                    <input type="hidden" name="term_year" id="edit_term_year">
                    <input type="hidden" name="status" id="edit_status" value="A">
                    
                    <!-- ข้อมูล Key แสดงเป็น Info -->
                    <div id="edit_info_section" style="background: rgba(59, 130, 246, 0.05); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; font-size: 0.9rem;">
                            <div>
                                <span style="color: var(--text-muted);">วันที่มีผล:</span>
                                <strong id="info_rate_date"></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-muted);">บริษัท:</span>
                                <strong id="info_company"></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-muted);">เพศ:</span>
                                <strong id="info_sex"></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-muted);">อายุ:</span>
                                <strong id="info_age"></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-muted);">ระยะเวลา:</span>
                                <strong id="info_term"></strong>
                            </div>
                        </div>
                    </div>

                    <!-- ช่องแก้ไขได้ -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-percent"></i> อัตราเบี้ยประกัน <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="rate" id="edit_rate" class="form-control" required step="0.0001"
                            min="0" style="font-size: 1.5rem; font-weight: 700; text-align: center; padding: 1rem;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-coins"></i> % คอมมิชชั่น
                        </label>
                        <input type="number" name="per_comm" id="edit_per_comm" class="form-control" value="0"
                            step="0.0001" min="0">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="deleteBtn" class="btn btn-danger" style="margin-right: auto;"
                        onclick="confirmDelete()">
                        <i class="fa-solid fa-trash-can"></i> ลบ
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fa-solid fa-xmark"></i> ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                    ยืนยันการลบ
                </h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบข้อมูลนี้หรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                    ยกเลิก
                </button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fa-solid fa-trash-can"></i> ลบ
                </a>
            </div>
        </div>
    </div>

    <!-- Clone Data Modal -->
    <div class="modal-overlay" id="cloneModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fa-solid fa-copy text-primary"></i>
                    โคลนข้อมูลเป็นวันที่ใหม่
                </h3>
                <button type="button" class="modal-close" onclick="closeCloneModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="cloneForm">
                <input type="hidden" name="clone_data" value="1">
                <div class="modal-body">
                    <div class="alert alert-info" style="margin-bottom: 1rem;">
                        <i class="fa-solid fa-info-circle"></i>
                        คัดลอกข้อมูลทั้งหมดจากวันที่ต้นทางไปยังวันที่ใหม่
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-building"></i> บริษัทประกัน
                        </label>
                        <select name="clone_company" id="clone_company" class="form-control" required>
                            <option value="01" <?= $selectedCompany === '01' ? 'selected' : '' ?>>AIA</option>
                            <option value="02" <?= $selectedCompany === '02' ? 'selected' : '' ?>>CHUBB</option>
                            <option value="03" <?= $selectedCompany === '03' ? 'selected' : '' ?>>TLIFE</option>
                            <option value="00" <?= $selectedCompany === '00' ? 'selected' : '' ?>>ไม่มี (NO)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-calendar"></i> วันที่ต้นทาง (โคลนจาก)
                        </label>
                        <select name="source_date" id="source_date" class="form-control" required>
                            <?php foreach ($rateDates as $date): ?>
                                <?php
                                $displayDate = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
                                ?>
                                <option value="<?= htmlspecialchars($date) ?>" <?= $date === $selectedDate ? 'selected' : '' ?>>
                                    <?= $displayDate ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-calendar-plus"></i> วันที่ปลายทาง (วันที่ใหม่)
                        </label>
                        <input type="date" name="target_date" id="target_date" class="form-control" required
                            value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCloneModal()">
                        <i class="fa-solid fa-xmark"></i> ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-copy"></i> โคลนข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer style="text-align: center; padding: 2rem; color: var(--text-muted);">
        <p>&copy; <?= date('Y') ?> Loan Protect Rate Management System</p>
    </footer>

    <script>
        let currentOriginalId = '';

        function openEditModal(data) {
            const modal = document.getElementById('editModal');
            const title = document.getElementById('modalTitle');
            const deleteBtn = document.getElementById('deleteBtn');

            // Format date for input
            let dateValue = '';
            let displayDate = '';
            if (data.rate_date) {
                dateValue = data.rate_date.substring(0, 4) + '-' +
                    data.rate_date.substring(4, 6) + '-' +
                    data.rate_date.substring(6, 8);
                displayDate = dateValue;
            } else {
                dateValue = new Date().toISOString().split('T')[0];
                displayDate = dateValue;
            }

            // Set hidden form values
            document.getElementById('edit_rate_date').value = dateValue;
            document.getElementById('edit_company').value = data.company;
            document.getElementById('edit_sex').value = data.sex;
            document.getElementById('edit_age_from').value = data.age_from;
            document.getElementById('edit_age_to').value = data.age_to;
            document.getElementById('edit_term_year').value = data.term_year;
            document.getElementById('edit_rate').value = data.rate || '';
            document.getElementById('edit_status').value = data.status || 'A';
            document.getElementById('edit_per_comm').value = data.per_comm || 0;
            
            // Set info display
            const companyNames = {'00': 'ไม่มี', '01': 'AIA', '02': 'CHUBB', '03': 'TLIFE'};
            document.getElementById('info_rate_date').textContent = displayDate;
            document.getElementById('info_company').textContent = companyNames[data.company] || data.company;
            document.getElementById('info_sex').textContent = data.sex === '1' ? 'ชาย' : 'หญิง';
            document.getElementById('info_age').textContent = data.age_from + '-' + data.age_to + ' ปี';
            document.getElementById('info_term').textContent = data.term_year + ' ปี';
            
            // Show/hide info section based on is_new
            const infoSection = document.getElementById('edit_info_section');
            if (data.is_new) {
                infoSection.style.display = 'none';
            } else {
                infoSection.style.display = 'block';
            }

            if (data.is_new) {
                title.innerHTML = '<i class="fa-solid fa-plus-circle text-success"></i> เพิ่มอัตราเบี้ยประกัน';
                document.getElementById('original_id').value = '';
                deleteBtn.style.display = 'none';
                currentOriginalId = '';
            } else {
                title.innerHTML = '<i class="fa-solid fa-pen-to-square text-primary"></i> แก้ไขอัตราเบี้ยประกัน';
                const originalId = data.rate_date + '|' + data.company + '|' + data.sex + '|' + data.age_from + '|' + data.term_year;
                document.getElementById('original_id').value = originalId;
                deleteBtn.style.display = 'inline-flex';
                currentOriginalId = originalId;
            }

            modal.classList.add('active');
            document.getElementById('edit_rate').focus();
        }

        function openAddModal() {
            const today = new Date().toISOString().split('T')[0];
            openEditModal({
                rate_date: today.replace(/-/g, ''),
                company: '<?= $selectedCompany ?>',
                sex: '1',
                age_from: '',
                age_to: '',
                term_year: 1,
                term_month: 0,
                rate: '',
                status: 'A',
                per_comm: 0,
                is_new: true
            });
        }

        function openNewDateModal() {
            const today = new Date().toISOString().split('T')[0];
            openEditModal({
                rate_date: today.replace(/-/g, ''),
                company: '<?= $selectedCompany ?>',
                sex: '1',
                age_from: 20,
                age_to: 20,
                term_year: 1,
                term_month: 0,
                rate: '',
                status: 'A',
                per_comm: 0,
                is_new: true
            });
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function confirmDelete() {
            if (!currentOriginalId) return;

            const deleteModal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');

            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('delete', currentOriginalId);
            currentUrl.searchParams.set('confirm', '1');
            confirmBtn.href = currentUrl.toString();

            closeModal();
            deleteModal.classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        function openCloneModal() {
            document.getElementById('cloneModal').classList.add('active');
        }

        function closeCloneModal() {
            document.getElementById('cloneModal').classList.remove('active');
        }

        // Close modals on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Close modals on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        });

        // Validate form
        document.getElementById('editForm').addEventListener('submit', function (e) {
            const ageFrom = parseInt(document.getElementById('edit_age_from').value);
            const ageTo = parseInt(document.getElementById('edit_age_to').value);

            if (ageTo < ageFrom) {
                e.preventDefault();
                alert('อายุสิ้นสุดต้องมากกว่าหรือเท่ากับอายุเริ่มต้น');
                return false;
            }
        });
    </script>
</body>

</html>