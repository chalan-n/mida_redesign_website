<?php
/**
 * ฟอร์มเพิ่ม/แก้ไขข้อมูลอัตราเบี้ยประกัน
 */

require_once 'config/database.php';

$conn = getConnection();

$message = '';
$messageType = '';
$isEdit = false;
$editData = null;

// โหมดแก้ไข - ดึงข้อมูลเดิม
if (isset($_GET['edit'])) {
    $isEdit = true;
    $parts = explode('|', $_GET['edit']);
    if (count($parts) >= 5) {
        $sql = "SELECT * FROM loanprotectrate 
                WHERE Rate_date = :rate_date 
                AND CmpInsuranceID = :company 
                AND SexID = :sex 
                AND age_from = :age_from 
                AND Term_year = :term_year
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'rate_date' => $parts[0],
            'company' => $parts[1],
            'sex' => $parts[2],
            'age_from' => $parts[3],
            'term_year' => $parts[4]
        ]);
        $editData = $stmt->fetch();
    }
}

// บันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rateDate = str_replace('-', '', $_POST['rate_date'] ?? '');
    $company = $_POST['company'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $ageFrom = intval($_POST['age_from'] ?? 0);
    $ageTo = intval($_POST['age_to'] ?? 0);
    $termYear = intval($_POST['term_year'] ?? 0);
    $termMonth = intval($_POST['term_month'] ?? 0);
    $rate = floatval($_POST['rate'] ?? 0);
    $status = $_POST['status'] ?? 'A';
    $perComm = floatval($_POST['per_comm'] ?? 0);
    
    // ถ้าเป็นโหมดแก้ไข ให้ลบข้อมูลเก่าก่อน
    if ($isEdit && isset($_POST['original_id'])) {
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
            $message = $isEdit ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มข้อมูลสำเร็จ';
            $messageType = 'success';
            
            // รีเซ็ตฟอร์มหลังบันทึก (ถ้าไม่ใช่โหมดแก้ไข)
            if (!$isEdit) {
                $editData = null;
            }
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

// ค่าจาก query string (สำหรับ pre-fill)
$defaultCompany = $_GET['company'] ?? '';
$defaultDate = $_GET['rate_date'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'แก้ไข' : 'เพิ่ม' ?>ข้อมูล | Loan Protect Rate</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/modern-style.css">
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
                <a href="index.php" class="nav-link">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>ตารางอัตรา</span>
                </a>
                <a href="manage.php" class="nav-link">
                    <i class="fa-solid fa-list-check"></i>
                    <span>จัดการข้อมูล</span>
                </a>
                <a href="form.php" class="nav-link active">
                    <i class="fa-solid fa-plus"></i>
                    <span>เพิ่มข้อมูล</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container" style="max-width: 800px;">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fa-solid fa-<?= $isEdit ? 'pen-to-square' : 'plus-circle' ?>"></i>
                    <?= $isEdit ? 'แก้ไข' : 'เพิ่ม' ?>ข้อมูลอัตราเบี้ยประกัน
                </h1>
                <p class="page-subtitle">
                    <?= $isEdit ? 'แก้ไขข้อมูลอัตราเบี้ยประกันที่มีอยู่' : 'กรอกข้อมูลเพื่อเพิ่มอัตราเบี้ยประกันใหม่' ?>
                </p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <i class="fa-solid fa-<?= $messageType === 'success' ? 'check-circle' : 'circle-exclamation' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fa-solid fa-clipboard-list"></i>
                        ฟอร์มข้อมูล
                    </h2>
                </div>
                <div class="card-body">
                    <form method="POST" id="rateForm">
                        <?php if ($isEdit && $editData): ?>
                            <input type="hidden" name="original_id" value="<?= htmlspecialchars($_GET['edit']) ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <!-- วันที่มีผล -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-calendar-days"></i> วันที่มีผล <span class="text-danger">*</span>
                                </label>
                                <?php
                                $dateValue = '';
                                if ($editData) {
                                    $dateValue = substr($editData['Rate_date'], 0, 4) . '-' . 
                                                 substr($editData['Rate_date'], 4, 2) . '-' . 
                                                 substr($editData['Rate_date'], 6, 2);
                                } elseif ($defaultDate) {
                                    $dateValue = substr($defaultDate, 0, 4) . '-' . 
                                                 substr($defaultDate, 4, 2) . '-' . 
                                                 substr($defaultDate, 6, 2);
                                }
                                ?>
                                <input type="date" name="rate_date" class="form-control" required
                                       value="<?= htmlspecialchars($dateValue) ?>">
                            </div>
                            
                            <!-- บริษัทประกัน -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-building"></i> บริษัทประกัน <span class="text-danger">*</span>
                                </label>
                                <select name="company" class="form-control" required>
                                    <option value="">-- เลือกบริษัท --</option>
                                    <option value="00" <?= ($editData['CmpInsuranceID'] ?? $defaultCompany) === '00' ? 'selected' : '' ?>>ไม่มี (NO)</option>
                                    <option value="01" <?= ($editData['CmpInsuranceID'] ?? $defaultCompany) === '01' ? 'selected' : '' ?>>AIA</option>
                                    <option value="02" <?= ($editData['CmpInsuranceID'] ?? $defaultCompany) === '02' ? 'selected' : '' ?>>CHUBB</option>
                                    <option value="03" <?= ($editData['CmpInsuranceID'] ?? $defaultCompany) === '03' ? 'selected' : '' ?>>TLIFE</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <!-- เพศ -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-venus-mars"></i> เพศ <span class="text-danger">*</span>
                                </label>
                                <div style="display: flex; gap: 1rem; padding-top: 0.5rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="sex" value="1" required
                                               <?= ($editData['SexID'] ?? '') === '1' ? 'checked' : '' ?>>
                                        <i class="fa-solid fa-mars text-primary"></i> ชาย
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="sex" value="0" required
                                               <?= ($editData['SexID'] ?? '') === '0' ? 'checked' : '' ?>>
                                        <i class="fa-solid fa-venus" style="color: #EC4899;"></i> หญิง
                                    </label>
                                </div>
                            </div>
                            
                            <!-- สถานะ -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-toggle-on"></i> สถานะ <span class="text-danger">*</span>
                                </label>
                                <div style="display: flex; gap: 1rem; padding-top: 0.5rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="status" value="A" required
                                               <?= ($editData['Status'] ?? 'A') === 'A' ? 'checked' : '' ?>>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Active</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="status" value="I"
                                               <?= ($editData['Status'] ?? '') === 'I' ? 'checked' : '' ?>>
                                        <span class="badge badge-secondary"><i class="fa-solid fa-pause"></i> Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <!-- อายุเริ่มต้น -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-user"></i> อายุเริ่มต้น (ปี) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="age_from" class="form-control" required
                                       min="0" max="100"
                                       value="<?= htmlspecialchars($editData['age_from'] ?? '') ?>">
                            </div>
                            
                            <!-- อายุสิ้นสุด -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-user"></i> อายุสิ้นสุด (ปี) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="age_to" class="form-control" required
                                       min="0" max="100"
                                       value="<?= htmlspecialchars($editData['age_to'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <!-- ระยะเวลา (ปี) -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-clock"></i> ระยะเวลา (ปี) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="term_year" class="form-control" required
                                       min="0" max="30"
                                       value="<?= htmlspecialchars($editData['Term_year'] ?? '') ?>">
                            </div>
                            
                            <!-- ระยะเวลา (เดือน) -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-clock"></i> ระยะเวลา (เดือน)
                                </label>
                                <input type="number" name="term_month" class="form-control"
                                       min="0" max="11"
                                       value="<?= htmlspecialchars($editData['Term_month'] ?? '0') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <!-- อัตราเบี้ย -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-percent"></i> อัตราเบี้ยประกัน <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="rate" class="form-control" required
                                       step="0.0001" min="0"
                                       value="<?= htmlspecialchars($editData['Rate'] ?? '') ?>">
                            </div>
                            
                            <!-- % คอมมิชชั่น -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fa-solid fa-coins"></i> เปอร์เซ็นต์คอมมิชชั่น
                                </label>
                                <input type="number" name="per_comm" class="form-control"
                                       step="0.0001" min="0"
                                       value="<?= htmlspecialchars($editData['PerComm'] ?? '0') ?>">
                            </div>
                        </div>
                        
                        <!-- Buttons -->
                        <div class="form-group mt-lg" style="display: flex; gap: 1rem; justify-content: flex-end;">
                            <a href="manage.php" class="btn btn-secondary">
                                <i class="fa-solid fa-xmark"></i> ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-<?= $isEdit ? 'save' : 'plus' ?>"></i>
                                <?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูล' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tips Card -->
            <div class="card mt-lg" style="background: rgba(59, 130, 246, 0.05);">
                <div class="card-body">
                    <h4 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-lightbulb text-warning"></i>
                        คำแนะนำ
                    </h4>
                    <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-secondary);">
                        <li>อายุเริ่มต้น-สิ้นสุด สามารถใส่ค่าเดียวกันได้ (เช่น 20-20 สำหรับอายุ 20 ปีเท่านั้น)</li>
                        <li>อัตราเบี้ยประกันรองรับทศนิยม 4 ตำแหน่ง</li>
                        <li>ข้อมูลที่มีสถานะ Inactive จะไม่แสดงในตารางอัตรา</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer style="text-align: center; padding: 2rem; color: var(--text-muted);">
        <p>&copy; <?= date('Y') ?> Loan Protect Rate Management System</p>
    </footer>

    <script>
        // Validate age range
        document.getElementById('rateForm').addEventListener('submit', function(e) {
            const ageFrom = parseInt(document.querySelector('input[name="age_from"]').value);
            const ageTo = parseInt(document.querySelector('input[name="age_to"]').value);
            
            if (ageTo < ageFrom) {
                e.preventDefault();
                alert('อายุสิ้นสุดต้องมากกว่าหรือเท่ากับอายุเริ่มต้น');
                return false;
            }
        });
    </script>
</body>
</html>
