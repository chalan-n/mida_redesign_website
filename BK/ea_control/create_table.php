<?php
// Create daily_trading_summary table
header('Content-Type: text/html; charset=utf-8');

// Database settings
$host = 'localhost';
$db   = 'zp12865_ea_control_db';
$user = 'zp12865_eacontrol'; 
$pass = '5YTLT6PGVpeJXkqERKNR';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `daily_trading_summary` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `account_number` bigint(20) NOT NULL COMMENT 'เลขบัญชี MT4/MT5',
      `trade_date` date NOT NULL COMMENT 'วันที่เทรด',
      `total_lot_size` decimal(15,2) DEFAULT 0.00 COMMENT 'ขนาดลอทรวมทั้งวัน',
      `frequency` int(11) DEFAULT 0 COMMENT 'จำนวนครั้งที่เทรด',
      `profit_loss_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'กำไร/ขาดทุนรวม',
      `profit_loss_percentage` decimal(8,4) DEFAULT 0.0000 COMMENT 'เปอร์เซ็นต์กำไร/ขาดทุน',
      `balance` decimal(15,2) DEFAULT 0.00 COMMENT 'ยอด Balance ณ วันนั้น',
      `max_floating_loss` decimal(15,2) DEFAULT 0.00 COMMENT 'Floating Loss สูงสุด',
      `max_floating_profit` decimal(15,2) DEFAULT 0.00 COMMENT 'Floating Profit สูงสุด',
      `winning_rate` decimal(5,2) DEFAULT 0.00 COMMENT 'อัตราชนะ (%)',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'เวลาที่บันทึก',
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'เวลาที่อัปเดตล่าสุด',
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_account_date` (`account_number`, `trade_date`),
      KEY `idx_account_number` (`account_number`),
      KEY `idx_trade_date` (`trade_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='สรุปผลการเทรดรายวัน';
    ";
    
    $pdo->exec($sql);
    
    echo "<h2>✅ สร้างตาราง daily_trading_summary สำเร็จแล้ว!</h2>";
    echo "<p><a href='dashboard2.php'>กลับไปหน้า Dashboard 2</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</h2>";
}
?>
