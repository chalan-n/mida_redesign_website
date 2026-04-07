<?php
/**
 * Track Page View - เก็บสถิติการเข้าชมหน้าเว็บ
 * Include ไฟล์นี้ในทุกหน้าที่ต้องการเก็บสถิติ
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ไม่เก็บสถิติถ้าเป็น bot หรือ admin
function isBot($userAgent)
{
    $bots = ['bot', 'spider', 'crawler', 'slurp', 'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduck'];
    $userAgent = strtolower($userAgent);
    foreach ($bots as $bot) {
        if (strpos($userAgent, $bot) !== false) {
            return true;
        }
    }
    return false;
}

// ตรวจจับประเภทอุปกรณ์จาก User Agent
function detectDeviceType($userAgent)
{
    $userAgent = strtolower($userAgent);

    // Mobile devices
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
        return 'mobile';
    }

    // Tablets
    if (preg_match('/tablet|ipad|playbook|silk|(android(?!.*mobile))/i', $userAgent)) {
        return 'tablet';
    }

    // Desktop (default)
    return 'desktop';
}

// ดึงข้อมูลจังหวัดจาก IP (ใช้ ip-api.com - ฟรี 45 requests/minute)
function getLocationFromIP($ip)
{
    // Skip local IPs
    if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
        return ['province' => 'Local', 'city' => 'Local'];
    }

    // Cache location in session to reduce API calls
    if (isset($_SESSION['visitor_location']) && isset($_SESSION['visitor_ip']) && $_SESSION['visitor_ip'] === $ip) {
        return $_SESSION['visitor_location'];
    }

    try {
        $url = "http://ip-api.com/json/{$ip}?fields=status,regionName,city&lang=th";
        $context = stream_context_create([
            'http' => [
                'timeout' => 2 // 2 seconds timeout
            ]
        ]);
        $response = @file_get_contents($url, false, $context);

        if ($response) {
            $data = json_decode($response, true);
            if ($data && $data['status'] === 'success') {
                $location = [
                    'province' => $data['regionName'] ?? null,
                    'city' => $data['city'] ?? null
                ];
                $_SESSION['visitor_location'] = $location;
                $_SESSION['visitor_ip'] = $ip;
                return $location;
            }
        }
    } catch (Exception $e) {
        // Silently fail
    }

    return ['province' => null, 'city' => null];
}

// ตรวจสอบว่าอยู่ในหน้า admin หรือไม่
$current_url = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($current_url, '/admin/') !== false) {
    return; // ไม่เก็บสถิติหน้า admin
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (isBot($userAgent)) {
    return; // ไม่เก็บสถิติ bot
}

// Use global $db if available, otherwise connect
if (!isset($db)) {
    require_once __DIR__ . '/admin/config/db.php';
    $database = new Database();
    $db = $database->getConnection();
}

try {
    // Get visitor info
    $page_url = $_SERVER['REQUEST_URI'] ?? '/';
    $page_title = '';
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    $session_id = session_id();

    // Detect device type
    $device_type = detectDeviceType($userAgent);

    // Get location from IP
    $location = getLocationFromIP($ip_address);
    $province = $location['province'];
    $city = $location['city'];

    // Insert page view
    $stmt = $db->prepare("
        INSERT INTO page_views (page_url, page_title, ip_address, user_agent, referrer, session_id, device_type, province, city)
        VALUES (:page_url, :page_title, :ip_address, :user_agent, :referrer, :session_id, :device_type, :province, :city)
    ");
    $stmt->execute([
        ':page_url' => $page_url,
        ':page_title' => $page_title,
        ':ip_address' => $ip_address,
        ':user_agent' => $userAgent,
        ':referrer' => $referrer,
        ':session_id' => $session_id,
        ':device_type' => $device_type,
        ':province' => $province,
        ':city' => $city
    ]);

    // Update daily stats
    $today = date('Y-m-d');

    // Check if today's stats exist
    $stmt = $db->prepare("SELECT id FROM daily_stats WHERE stat_date = :today");
    $stmt->execute([':today' => $today]);

    if ($stmt->fetch()) {
        // Update existing
        $db->prepare("
            UPDATE daily_stats 
            SET total_views = total_views + 1,
                unique_visitors = (SELECT COUNT(DISTINCT session_id) FROM page_views WHERE DATE(visited_at) = :today)
            WHERE stat_date = :today2
        ")->execute([':today' => $today, ':today2' => $today]);
    } else {
        // Insert new
        $db->prepare("
            INSERT INTO daily_stats (stat_date, total_views, unique_visitors)
            VALUES (:today, 1, 1)
        ")->execute([':today' => $today]);
    }

} catch (PDOException $e) {
    // Silently fail - don't break the page
    error_log("Visitor tracking error: " . $e->getMessage());
}
?>