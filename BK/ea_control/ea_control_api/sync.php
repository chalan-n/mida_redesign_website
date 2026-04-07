<?php
// ตั้งค่าการเชื่อมต่อ Database
$db_host = 'localhost';
$db_name = 'zp12865_ea_control_db'; // ใช้ชื่อที่คุณตั้ง
$db_user = 'zp12865_eacontrol';          // เปลี่ยนเป็น user ของคุณ
$db_pass = '5YTLT6PGVpeJXkqERKNR';              // เปลี่ยนเป็น password ของคุณ

header('Content-Type: application/json');

// รับ action จาก GET parameter
$action = $_GET['action'] ?? '';

// --- Case 1: สำหรับ Daily Trading Summary ---
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
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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

// --- Case 2: สำหรับ Regular Sync (เดิม) ---
try {
    // 1. เชื่อมต่อ Database ด้วย PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. รับข้อมูล JSON ที่ส่งมาจาก EA
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (!$data) {
        throw new Exception("No data received or invalid JSON");
    }

    // --- ส่วนที่ 1: อัปเดตข้อมูลบัญชี (Upsert) ---
    // ใช้ ON DUPLICATE KEY UPDATE เพื่อเช็คว่าถ้ามีเลขบัญชีนี้แล้วให้อัปเดต ถ้ายังไม่มีให้สร้างใหม่
    $sql = "INSERT INTO mt_accounts 
            (account_number, account_name, broker, platform, balance, equity, margin, free_margin, profit, daily_profit, orders_json, last_update) 
            VALUES 
            (:acc, :name, :broker, :plat, :bal, :eq, :marg, :free, :prof, :daily, :orders, NOW())
            ON DUPLICATE KEY UPDATE 
            account_name = VALUES(account_name),
            broker = VALUES(broker),
            platform = VALUES(platform),
            balance = VALUES(balance),
            equity = VALUES(equity),
            margin = VALUES(margin),
            free_margin = VALUES(free_margin),
            profit = VALUES(profit),
            daily_profit = VALUES(daily_profit),
            orders_json = VALUES(orders_json),
            last_update = NOW()";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':acc'    => $data['account_number'],
        ':name'   => $data['account_name'] ?? 'Unknown',
        ':broker' => $data['broker'] ?? 'Unknown',
        ':plat'   => $data['platform'] ?? 'MT4',
        ':bal'    => $data['balance'],
        ':eq'     => $data['equity'],
        ':marg'   => $data['margin'],
        ':free'   => $data['free_margin'],
        ':prof'   => $data['profit'],
        ':daily'  => $data['daily_profit'],
        ':orders' => json_encode($data['orders']) // แปลง Array ออเดอร์เป็น JSON String เก็บลง DB
    ]);

    // --- ส่วนที่ 2: เช็คว่ามีคำสั่งค้างอยู่ไหม (Command Check) ---
    $responseCommand = null;
    
    // ค้นหาคำสั่งที่สถานะ PENDING สำหรับบัญชีนี้ หรือคำสั่งรวม (account_number = 0)
    $cmdSql = "SELECT * FROM mt_commands 
               WHERE status = 'PENDING' 
               AND (account_number = :acc OR account_number = 0) 
               ORDER BY id ASC LIMIT 1";
               
    $cmdStmt = $pdo->prepare($cmdSql);
    $cmdStmt->execute([':acc' => $data['account_number']]);
    $command = $cmdStmt->fetch(PDO::FETCH_ASSOC);

    if ($command) {
        // ถ้ามีคำสั่ง:
        // 1. เปลี่ยนสถานะเป็น PICKED (เพื่อไม่ให้ส่งซ้ำ)
        $updateCmd = $pdo->prepare("UPDATE mt_commands SET status = 'PICKED', updated_at = NOW() WHERE id = :id");
        $updateCmd->execute([':id' => $command['id']]);
        
        // 2. เตรียมข้อมูลส่งกลับให้ EA
        $responseCommand = [
            'id'      => $command['id'],
            'type'    => $command['command_type'],
            'params'  => $command['params']
        ];
    }

    // --- ดึงค่า trading_enabled ของบัญชีนี้ (ถ้ามี column นี้) ---
    $tradingEnabled = 1; // default = เปิดใช้งาน
    try {
        $accStmt = $pdo->prepare("SELECT trading_enabled FROM mt_accounts WHERE account_number = :acc");
        $accStmt->execute([':acc' => $data['account_number']]);
        $accData = $accStmt->fetch(PDO::FETCH_ASSOC);
        if ($accData && isset($accData['trading_enabled'])) {
            $tradingEnabled = (int)$accData['trading_enabled'];
        }
    } catch (Exception $e) {
        // Column trading_enabled ยังไม่มี - ใช้ค่า default
        $tradingEnabled = 1;
    }

    // --- ส่วนที่ 3: ส่ง Response กลับหา EA ---
    echo json_encode([
        'status' => 'success',
        'message' => 'Data synced',
        'command' => $responseCommand, // ส่งคำสั่งไปด้วย (ถ้ามี) หรือเป็น null (ถ้าไม่มี)
        'trading_enabled' => $tradingEnabled // 1 = เทรดได้, 0 = หยุดเทรด
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>