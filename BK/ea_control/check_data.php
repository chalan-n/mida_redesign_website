<?php
// Check data in database
header('Content-Type: text/html; charset=utf-8');

// Database settings
$host = 'localhost';
$db   = 'zp12865_ea_control_db';
$user = 'zp12865_eacontrol'; 
$pass = '5YTLT6PGVpeJXkqERKNR';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 ตรวจสอบข้อมูลในฐานข้อมูล</h2>";
    
    // Check if table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'daily_trading_summary'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if(!$tableExists) {
        echo "<p style='color: red;'>❌ ตาราง daily_trading_summary ไม่มีอยู่!</p>";
        echo "<p><a href='create_table.php'>สร้างตาราง</a></p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ ตาราง daily_trading_summary มีอยู่</p>";
    
    // Check data for account 105411992
    $stmt = $pdo->prepare("SELECT * FROM daily_trading_summary WHERE account_number = 105411992 ORDER BY trade_date DESC");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 ข้อมูลบัญชี 105411992</h3>";
    
    if(empty($records)) {
        echo "<p style='color: red;'>❌ ไม่พบข้อมูลสำหรับบัญชี 105411992</p>";
        
        // Check all accounts
        $stmt = $pdo->prepare("SELECT DISTINCT account_number FROM daily_trading_summary ORDER BY account_number");
        $stmt->execute();
        $accounts = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if(!empty($accounts)) {
            echo "<p>บัญชีที่มีข้อมูล:</p>";
            echo "<ul>";
            foreach($accounts as $acc) {
                echo "<li><a href='dashboard3.php?account=" . $acc . "'>" . $acc . "</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ ไม่มีข้อมูลใดๆ ในตารางเลย</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ พบข้อมูล " . count($records) . " วัน</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>วันที่</th><th>Lot Size</th><th>Frequency</th><th>P&L</th><th>%</th><th>Balance</th><th>Win Rate</th></tr>";
        
        foreach($records as $record) {
            echo "<tr>";
            echo "<td>" . $record['trade_date'] . "</td>";
            echo "<td>" . $record['total_lot_size'] . "</td>";
            echo "<td>" . $record['frequency'] . "</td>";
            echo "<td>" . $record['profit_loss_amount'] . "</td>";
            echo "<td>" . $record['profit_loss_percentage'] . "%</td>";
            echo "<td>" . $record['balance'] . "</td>";
            echo "<td>" . $record['winning_rate'] . "%</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><a href='dashboard3.php?account=105411992' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>👉 ไปที่ Dashboard3</a>";
    }
    
    // Check recent logs
    echo "<h3>📋 ตรวจสอบ Error Log (10 บรรทัดล่าสุด)</h3>";
    
    $logFile = 'C:/xampp/apache/logs/error.log';
    if(file_exists($logFile)) {
        $errorLog = file_get_contents($logFile);
        $recentLogs = array_slice(explode("\n", $errorLog), -10);
        
        echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 12px; max-height: 300px; overflow-y: auto;'>";
        $foundDailySummary = false;
        
        foreach($recentLogs as $log) {
            if(strpos($log, 'Daily Summary Received') !== false) {
                echo "<span style='color: green; font-weight: bold;'>" . htmlspecialchars($log) . "</span>\n";
                $foundDailySummary = true;
            } elseif(strpos($log, 'Database insert') !== false) {
                echo "<span style='color: blue; font-weight: bold;'>" . htmlspecialchars($log) . "</span>\n";
            } elseif(strpos($log, 'save_daily_summary') !== false) {
                echo "<span style='color: orange; font-weight: bold;'>" . htmlspecialchars($log) . "</span>\n";
            } else {
                echo htmlspecialchars($log) . "\n";
            }
        }
        
        if(!$foundDailySummary) {
            echo "<span style='color: red; font-weight: bold;'>⚠️ ไม่พบ log จาก Daily Summary Received!</span>\n";
        }
        
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ ไม่พบไฟล์ error.log ที่: " . $logFile . "</p>";
        echo "<p>ลองตรวจสอบ path ของ XAMPP logs</p>";
    }
    
    // Check access log
    echo "<h3>🌐 ตรวจสอบ Access Log</h3>";
    $accessLog = 'C:/xampp/apache/logs/access.log';
    if(file_exists($accessLog)) {
        $accessContent = file_get_contents($accessLog);
        $recentAccess = array_slice(explode("\n", $accessContent), -20);
        
        echo "<pre style='background: #f0f8ff; padding: 10px; font-size: 12px; max-height: 200px; overflow-y: auto;'>";
        $foundApiCall = false;
        
        foreach($recentAccess as $log) {
            if(strpos($log, 'save_daily_summary') !== false || strpos($log, 'POST') !== false) {
                echo "<span style='color: blue; font-weight: bold;'>" . htmlspecialchars($log) . "</span>\n";
                $foundApiCall = true;
            } else {
                echo htmlspecialchars($log) . "\n";
            }
        }
        
        if(!$foundApiCall) {
            echo "<span style='color: orange; font-weight: bold;'>⚠️ ไม่พบการเรียก API ใน access log!</span>\n";
        }
        
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ ไม่พบไฟล์ access.log ที่: " . $accessLog . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
