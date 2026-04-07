<?php
// api.php
header('Content-Type: application/json');

// --- ตั้งค่า Database ---
$host = 'localhost';
$db   = 'zp12865_ea_control_db';
$user = 'zp12865_eacontrol'; 
$pass = '5YTLT6PGVpeJXkqERKNR'; 
/*$db   = 'ea_control_db';
$user = 'root'; 
$pass = ''; */

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Connection Failed']);
    exit;
}

// รับค่า Action (ดึงข้อมูล หรือ สั่งปิด)
$action = $_GET['action'] ?? '';

// --- Case 1: ดึงข้อมูลไปโชว์ที่หน้าเว็บ ---
if ($action == 'fetch_data') {
    $stmt = $pdo->query("
        SELECT a.*, v.vps_name 
        FROM mt_accounts a 
        LEFT JOIN vps_list v ON a.vps_id = v.id 
        ORDER BY v.vps_name ASC, a.account_number ASC
    ");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // คำนวณสถานะเพิ่มเติม
    foreach ($accounts as &$acc) {
        // เช็คว่า Online ไหม (ถ้า last_update เกิน 60 วิ ถือว่า Offline)
        $last_update = strtotime($acc['last_update']);
        $diff = time() - $last_update;
        $acc['is_online'] = ($diff < 60);
        
        // นับจำนวนออเดอร์จาก JSON
        $orders = json_decode($acc['orders_json'], true);
        $acc['order_count'] = is_array($orders) ? count($orders) : 0;
        
        // ไม่ส่ง JSON ก้อนใหญ่กลับไป เพื่อความเร็ว
        unset($acc['orders_json']); 
    }
    echo json_encode($accounts);
    exit;
}

// --- Case 2: สั่งปิดออเดอร์ (Close All) ---
if ($action == 'close_all' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $acc_num = $_POST['account_number'] ?? 0;
    
    // Insert คำสั่งลง Queue
    $stmt = $pdo->prepare("INSERT INTO mt_commands (account_number, command_type, status) VALUES (:acc, 'CLOSE_ALL', 'PENDING')");
    $stmt->execute([':acc' => $acc_num]);
    
    echo json_encode(['status' => 'success', 'msg' => "ส่งคำสั่งปิดออเดอร์บัญชี $acc_num แล้ว"]);
    exit;
}

// --- Case 3: ดึงรายการ VPS ทั้งหมด ---
if ($action == 'get_vps_list') {
    $stmt = $pdo->query("SELECT * FROM vps_list ORDER BY vps_name ASC");
    $vpsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($vpsList);
    exit;
}

// --- Case 4: เพิ่ม VPS ใหม่ ---
if ($action == 'add_vps' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vps_name = trim($_POST['vps_name'] ?? '');
    
    if (empty($vps_name)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาใส่ชื่อ VPS']);
        exit;
    }
    
    // เช็คซ้ำ
    $stmt = $pdo->prepare("SELECT id FROM vps_list WHERE vps_name = :name");
    $stmt->execute([':name' => $vps_name]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'ชื่อ VPS นี้มีอยู่แล้ว']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO vps_list (vps_name) VALUES (:name)");
    $stmt->execute([':name' => $vps_name]);
    
    echo json_encode(['status' => 'success', 'message' => 'เพิ่ม VPS แล้ว', 'id' => $pdo->lastInsertId()]);
    exit;
}

// --- Case 5: ลบ VPS ---
if ($action == 'delete_vps' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vps_id = intval($_POST['vps_id'] ?? 0);
    
    if ($vps_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'VPS ID ไม่ถูกต้อง']);
        exit;
    }
    
    // ลบ VPS และอัปเดตบัญชีที่เคยใช้ VPS นี้
    $stmt = $pdo->prepare("UPDATE mt_accounts SET vps_id = NULL WHERE vps_id = :id");
    $stmt->execute([':id' => $vps_id]);
    
    $stmt = $pdo->prepare("DELETE FROM vps_list WHERE id = :id");
    $stmt->execute([':id' => $vps_id]);
    
    echo json_encode(['status' => 'success', 'message' => 'ลบ VPS แล้ว']);
    exit;
}

// --- Case 6: ดึงข้อมูลบัญชีพร้อม Settings (VPS, EA Name) ---
if ($action == 'fetch_data_with_settings') {
    $stmt = $pdo->query("
        SELECT a.*, v.vps_name 
        FROM mt_accounts a 
        LEFT JOIN vps_list v ON a.vps_id = v.id 
        ORDER BY 
            CASE WHEN a.vps_id IS NULL OR a.ea_name IS NULL OR a.ea_name = '' THEN 0 ELSE 1 END ASC,
            a.account_number ASC
    ");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($accounts as &$acc) {
        $last_update = strtotime($acc['last_update']);
        $diff = time() - $last_update;
        $acc['is_online'] = ($diff < 60);
        unset($acc['orders_json']);
    }
    
    echo json_encode($accounts);
    exit;
}

// --- Case 7: บันทึกการตั้งค่าบัญชี (VPS, EA Name, MT Type, Account Mode) ---
if ($action == 'save_account_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $acc_num = intval($_POST['account_number'] ?? 0);
    $vps_id = $_POST['vps_id'] !== '' ? intval($_POST['vps_id']) : null;
    $ea_name = trim($_POST['ea_name'] ?? '');
    $mt_type = trim($_POST['mt_type'] ?? '');
    $account_mode = trim($_POST['account_mode'] ?? '');
    $initial_capital = isset($_POST['initial_capital']) && $_POST['initial_capital'] !== '' ? floatval($_POST['initial_capital']) : null;
    
    // ตรวจสอบค่า mt_type
    if ($mt_type !== '' && !in_array($mt_type, ['MT4', 'MT5'])) {
        $mt_type = '';
    }
    
    // ตรวจสอบค่า account_mode
    if ($account_mode !== '' && !in_array($account_mode, ['Demo', 'Real'])) {
        $account_mode = '';
    }
    
    if ($acc_num <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'หมายเลขบัญชีไม่ถูกต้อง']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE mt_accounts SET vps_id = :vps_id, ea_name = :ea_name, mt_type = :mt_type, account_mode = :account_mode, initial_capital = :initial_capital WHERE account_number = :acc_num");
    $stmt->execute([
        ':vps_id' => $vps_id,
        ':ea_name' => $ea_name,
        ':mt_type' => $mt_type ?: null,
        ':account_mode' => $account_mode ?: null,
        ':initial_capital' => $initial_capital,
        ':acc_num' => $acc_num
    ]);
    
    echo json_encode(['status' => 'success', 'message' => 'บันทึกแล้ว']);
    exit;
}

// --- Case 8: ลบบัญชี ---
if ($action == 'delete_account' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $acc_num = intval($_POST['account_number'] ?? 0);
    
    if ($acc_num <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'หมายเลขบัญชีไม่ถูกต้อง']);
        exit;
    }
    
    try {
        // ลบคำสั่งที่เกี่ยวข้องกับบัญชีนี้ก่อน
        $stmt = $pdo->prepare("DELETE FROM mt_commands WHERE account_number = :acc_num");
        $stmt->execute([':acc_num' => $acc_num]);

        // ลบประวัติการเทรด
        $stmt = $pdo->prepare("DELETE FROM daily_trading_summary WHERE account_number = :acc_num");
        $stmt->execute([':acc_num' => $acc_num]);

        // ลบบัญชี
        $stmt = $pdo->prepare("DELETE FROM mt_accounts WHERE account_number = :acc_num");
        $stmt->execute([':acc_num' => $acc_num]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'ลบบัญชีแล้ว']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบบัญชีนี้ในระบบ']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit;
}

// --- Case 9: Toggle สถานะการเทรด (หยุด/เริ่ม) ---
if ($action == 'toggle_trading' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $acc_num = intval($_POST['account_number'] ?? 0);
    
    if ($acc_num <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'หมายเลขบัญชีไม่ถูกต้อง']);
        exit;
    }
    
    try {
        // ดึงสถานะปัจจุบัน
        $stmt = $pdo->prepare("SELECT trading_enabled FROM mt_accounts WHERE account_number = :acc_num");
        $stmt->execute([':acc_num' => $acc_num]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบบัญชีนี้']);
            exit;
        }
        
        // Toggle สถานะ
        $newStatus = $current['trading_enabled'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE mt_accounts SET trading_enabled = :status WHERE account_number = :acc_num");
        $stmt->execute([':status' => $newStatus, ':acc_num' => $acc_num]);
        
        $statusText = $newStatus ? 'เปิดใช้งานการเทรด' : 'หยุดการเทรด';
        echo json_encode(['status' => 'success', 'trading_enabled' => $newStatus, 'message' => $statusText]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit;
}

// --- Case 10: Toggle สถานะ Favorite (ดาว) ---
if ($action == 'toggle_favorite' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $acc_num = intval($_POST['account_number'] ?? 0);
    
    if ($acc_num <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'หมายเลขบัญชีไม่ถูกต้อง']);
        exit;
    }
    
    try {
        // ดึงสถานะปัจจุบัน
        $stmt = $pdo->prepare("SELECT is_favorite FROM mt_accounts WHERE account_number = :acc_num");
        $stmt->execute([':acc_num' => $acc_num]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบบัญชีนี้']);
            exit;
        }
        
        // Toggle สถานะ
        $newStatus = $current['is_favorite'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE mt_accounts SET is_favorite = :status WHERE account_number = :acc_num");
        $stmt->execute([':status' => $newStatus, ':acc_num' => $acc_num]);
        
        $statusText = $newStatus ? 'มาร์กบัญชีสำคัญแล้ว' : 'ยกเลิกการมาร์กแล้ว';
        echo json_encode(['status' => 'success', 'is_favorite' => $newStatus, 'message' => $statusText]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit;
}
// --- Case 11: ดึงข้อมูลประวัติการเทรดสำหรับ dashboard3 ---
if ($action == 'get_trading_history') {
    $acc_num = intval($_GET['account'] ?? 0);
    
    if ($acc_num <= 0) {
        echo json_encode(['error' => 'หมายเลขบัญชีไม่ถูกต้อง']);
        exit;
    }
    
    try {
        // ดึงข้อมูลบัญชี
        $stmt = $pdo->prepare("SELECT account_number, ea_name, mt_type, account_mode FROM mt_accounts WHERE account_number = :acc_num");
        $stmt->execute([':acc_num' => $acc_num]);
        $accountInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$accountInfo) {
            echo json_encode(['error' => 'ไม่พบบัญชีนี้ในระบบ']);
            exit;
        }
        
        // ดึงข้อมูลประวัติการเทรด (ไม่รวมวันนี้)
        $stmt = $pdo->prepare("
            SELECT * FROM daily_trading_summary 
            WHERE account_number = :acc_num 
            AND trade_date < CURDATE()
            ORDER BY trade_date DESC
        ");
        $stmt->execute([':acc_num' => $acc_num]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'account_info' => $accountInfo,
            'history' => $history
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit;
}

// --- Case 12: รับข้อมูล daily trading summary จาก EA ---
if ($action == 'save_daily_summary' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (!$data || !isset($data['account_number']) || !isset($data['trade_date'])) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน', 'received_data' => $data]);
        exit;
    }
    
    // Log received data for debugging
    error_log("Daily Summary Received: " . json_encode($data));
    
    // Additional debugging
    error_log("Account Number: " . $data['account_number']);
    error_log("Trade Date: " . $data['trade_date']);
    error_log("Frequency: " . $data['frequency']);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO daily_trading_summary 
            (account_number, trade_date, total_lot_size, frequency, profit_loss_amount, 
             profit_loss_percentage, balance, max_floating_loss, max_floating_profit, winning_rate)
            VALUES 
            (:account_number, :trade_date, :total_lot_size, :frequency, :profit_loss_amount,
             :profit_loss_percentage, :balance, :max_floating_loss, :max_floating_profit, :winning_rate)
            ON DUPLICATE KEY UPDATE
            total_lot_size = VALUES(total_lot_size),
            frequency = VALUES(frequency),
            profit_loss_amount = VALUES(profit_loss_amount),
            profit_loss_percentage = VALUES(profit_loss_percentage),
            balance = VALUES(balance),
            max_floating_loss = VALUES(max_floating_loss),
            max_floating_profit = VALUES(max_floating_profit),
            winning_rate = VALUES(winning_rate),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        $result = $stmt->execute([
            ':account_number' => $data['account_number'],
            ':trade_date' => $data['trade_date'],
            ':total_lot_size' => $data['total_lot_size'] ?? 0,
            ':frequency' => $data['frequency'] ?? 0,
            ':profit_loss_amount' => $data['profit_loss_amount'] ?? 0,
            ':profit_loss_percentage' => $data['profit_loss_percentage'] ?? 0,
            ':balance' => $data['balance'] ?? 0,
            ':max_floating_loss' => $data['max_floating_loss'] ?? 0,
            ':max_floating_profit' => $data['max_floating_profit'] ?? 0,
            ':winning_rate' => $data['winning_rate'] ?? 0
        ]);
        
        if($result) {
            error_log("Database insert successful for account " . $data['account_number']);
            echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลสำเร็จ']);
        } else {
            error_log("Database insert failed for account " . $data['account_number']);
            echo json_encode(['status' => 'error', 'message' => 'บันทึกข้อมูลล้มเหลว']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit;
}

// ... (rest of the code remains the same)
?>