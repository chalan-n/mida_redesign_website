

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
