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
    $bots = array('bot', 'spider', 'crawler', 'slurp', 'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduck');
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

function getClientIpAddress()
{
    $forwardedFor = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
    if (!empty($forwardedFor)) {
        $parts = explode(',', $forwardedFor);
        foreach ($parts as $part) {
            $candidate = trim($part);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }
    }

    $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '';
}

function isPrivateIpAddress($ip)
{
    if (empty($ip)) {
        return true;
    }

    if (in_array($ip, array('127.0.0.1', '::1', 'localhost'), true)) {
        return true;
    }

    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

// ดึงข้อมูลจังหวัดจาก IP (ใช้ ip-api.com - ฟรี 45 requests/minute)
function getLocationFromIP($ip)
{
    if (isPrivateIpAddress($ip)) {
        return array('province' => 'Local', 'city' => 'Local');
    }

    return array('province' => null, 'city' => null);
}

// ตรวจสอบว่าอยู่ในหน้า admin หรือไม่
$current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
if (strpos($current_url, '/admin/') !== false) {
    return; // ไม่เก็บสถิติหน้า admin
}

$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
if (isBot($userAgent)) {
    return; // ไม่เก็บสถิติ bot
}

// Use global $db if available, otherwise connect
if (!isset($db)) {
    require_once __DIR__ . '/admin/config/db.php';
    $database = new Database();
    $db = $database->getConnection();
}

if (!$db) {
    error_log('Visitor tracking skipped: database connection unavailable.');
    return;
}

try {
    // Get visitor info
    $page_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $page_title = '';
    $ip_address = getClientIpAddress();
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
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
    $stmt->execute(array(
        ':page_url' => $page_url,
        ':page_title' => $page_title,
        ':ip_address' => $ip_address,
        ':user_agent' => $userAgent,
        ':referrer' => $referrer,
        ':session_id' => $session_id,
        ':device_type' => $device_type,
        ':province' => $province,
        ':city' => $city
    ));

    // Update daily stats
    $today = date('Y-m-d');
    $sessionCounterKey = 'visitor_stats_counted_' . $today;
    $isFirstVisitTodayForSession = empty($_SESSION[$sessionCounterKey]);

    // Check if today's stats exist
    $stmt = $db->prepare("SELECT id FROM daily_stats WHERE stat_date = :today");
    $stmt->execute(array(':today' => $today));

    if ($stmt->fetch()) {
        // Update existing
        $sql = "
            UPDATE daily_stats 
            SET total_views = total_views + 1";

        if ($isFirstVisitTodayForSession) {
            $sql .= ",
                unique_visitors = unique_visitors + 1";
        }

        $sql .= "
            WHERE stat_date = :today";

        $db->prepare($sql)->execute(array(':today' => $today));
    } else {
        // Insert new
        $db->prepare("
            INSERT INTO daily_stats (stat_date, total_views, unique_visitors)
            VALUES (:today, 1, 1)
        ")->execute(array(':today' => $today));
    }

    $_SESSION[$sessionCounterKey] = true;

} catch (PDOException $e) {
    error_log("Visitor tracking error: " . $e->getMessage());
}
?>
