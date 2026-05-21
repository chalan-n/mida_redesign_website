<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings
$settings = array();
try {
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
}

// Fetch active auction brochure. Fallback keeps the page usable before admin adds data.
$auction_brochure = array(
    'title' => 'ดูรอบประมูลและเตรียมเข้าร่วมได้ในที่เดียว',
    'round_label' => 'รอบประมูลล่าสุด',
    'description' => 'ไมด้าลิสซิ่งรวบรวมข้อมูลรอบประมูล รถที่เปิดให้เข้าร่วม และช่องทางลงทะเบียน เพื่อให้ลูกค้าวางแผนดูรถและเข้าร่วมประมูลได้สะดวกขึ้น',
    'image_path' => 'assets/img/banners/auction-brochure-nakhon-pathom-25690520.jpg',
    'registration_link' => 'https://auction.mida-leasing.com',
    'line_link' => !empty($settings['site_line']) ? $settings['site_line'] : 'https://line.me/R/ti/p/@midaleasing'
);
try {
    $stmt_brochure = $db->query("SELECT * FROM auction_brochures WHERE is_active = 1 ORDER BY sort_order ASC, id DESC LIMIT 1");
    $brochure_row = $stmt_brochure->fetch(PDO::FETCH_ASSOC);
    if ($brochure_row && !empty($brochure_row['image_path'])) {
        $auction_brochure = array_merge($auction_brochure, $brochure_row);
        if (empty($auction_brochure['registration_link'])) {
            $auction_brochure['registration_link'] = 'https://auction.mida-leasing.com';
        }
        if (empty($auction_brochure['line_link'])) {
            $auction_brochure['line_link'] = !empty($settings['site_line']) ? $settings['site_line'] : 'https://line.me/R/ti/p/@midaleasing';
        }
    }
} catch (PDOException $e) {
}

// Fetch Auction Schedules with actual car count
$schedules = array();
try {
    $stmt = $db->query("
        SELECT s.*, 
               (SELECT COUNT(*) FROM auction_cars c WHERE c.schedule_id = s.id) as actual_car_count
        FROM auction_schedules s 
        WHERE s.is_active = 1 
        ORDER BY s.id DESC
    ");
    $schedules = $stmt->fetchAll();
} catch (PDOException $e) {
}

function auction_extract_date_parts($date_text)
{
    $date_text = trim((string) $date_text);
    $parts = array('day' => null, 'month' => null, 'year' => null);

    if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $date_text, $matches)) {
        $parts['year'] = (int) $matches[1];
        $parts['day'] = (int) $matches[3];
        $parts['month'] = (int) $matches[2];
        return $parts;
    }

    if (preg_match('/(\d{1,2})/', $date_text, $matches)) {
        $parts['day'] = (int) $matches[1];
    }

    if (preg_match('/(25\d{2}|20\d{2})/', $date_text, $matches)) {
        $year = (int) $matches[1];
        $parts['year'] = $year > 2400 ? $year - 543 : $year;
    }

    $thai_months = array(
        'มกราคม' => 1,
        'กุมภาพันธ์' => 2,
        'มีนาคม' => 3,
        'เมษายน' => 4,
        'พฤษภาคม' => 5,
        'มิถุนายน' => 6,
        'กรกฎาคม' => 7,
        'สิงหาคม' => 8,
        'กันยายน' => 9,
        'ตุลาคม' => 10,
        'พฤศจิกายน' => 11,
        'ธันวาคม' => 12
    );

    foreach ($thai_months as $month_name => $month_number) {
        if (strpos($date_text, $month_name) !== false) {
            $parts['month'] = $month_number;
            break;
        }
    }

    return $parts;
}

function auction_branch_label($branch_name)
{
    return trim(preg_replace('/^สาขา\s*/u', '', (string) $branch_name));
}

$calendar_month = (int) date('n');
$calendar_year = (int) date('Y');
if (!empty($schedules)) {
    $first_schedule_parts = auction_extract_date_parts($schedules[0]['auction_date']);
    if (!empty($first_schedule_parts['year'])) {
        $calendar_year = (int) $first_schedule_parts['year'];
    }
    if (!empty($first_schedule_parts['month'])) {
        $calendar_month = $first_schedule_parts['month'];
    }
}

$thai_month_labels = array(
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม'
);
$auction_calendar_days = array();
foreach ($schedules as $schedule) {
    $date_parts = auction_extract_date_parts($schedule['auction_date']);
    if (empty($date_parts['day'])) {
        continue;
    }
    if (!empty($date_parts['month']) && (int) $date_parts['month'] !== $calendar_month) {
        continue;
    }
    $day = (int) $date_parts['day'];
    if (!isset($auction_calendar_days[$day])) {
        $auction_calendar_days[$day] = array();
    }
    $auction_calendar_days[$day][] = $schedule;
}
$calendar_first_day = (int) date('N', strtotime($calendar_year . '-' . str_pad($calendar_month, 2, '0', STR_PAD_LEFT) . '-01'));
$calendar_days_in_month = (int) date('t', strtotime($calendar_year . '-' . str_pad($calendar_month, 2, '0', STR_PAD_LEFT) . '-01'));
$calendar_title = $thai_month_labels[$calendar_month] . ' ' . ($calendar_year + 543);
$today_datetime = new DateTime('now', new DateTimeZone('Asia/Bangkok'));
$today_key = (int) $today_datetime->format('Ymd');
$auction_calendar_events = array();
foreach ($schedules as $schedule) {
    $date_parts = auction_extract_date_parts($schedule['auction_date']);
    if (empty($date_parts['day'])) {
        continue;
    }

    $event_year = !empty($date_parts['year']) ? (int) $date_parts['year'] : $calendar_year;
    $event_month = !empty($date_parts['month']) ? (int) $date_parts['month'] : $calendar_month;
    $auction_calendar_events[] = array(
        'id' => (int) $schedule['id'],
        'day' => (int) $date_parts['day'],
        'month' => $event_month,
        'year' => $event_year,
        'branchName' => $schedule['branch_name'],
        'branchLabel' => auction_branch_label($schedule['branch_name']),
        'auctionDate' => $schedule['auction_date'],
        'timeRegister' => $schedule['time_register'],
        'timeStart' => $schedule['time_start'],
        'carCount' => (int) $schedule['actual_car_count'],
        'url' => 'auction_list.php?schedule_id=' . (int) $schedule['id']
    );
}

// Fetch Featured Cars (is_featured=1, fallback to random if less than 4)
$highlight_cars = array();
try {
    // First, try to get featured cars
    $stmt = $db->query("
        SELECT c.*, s.branch_name as schedule_name, s.auction_date as schedule_date
        FROM auction_cars c
        LEFT JOIN auction_schedules s ON c.schedule_id = s.id
        WHERE c.is_featured = 1 AND c.schedule_id IS NOT NULL
        ORDER BY CAST(c.queue_number AS UNSIGNED) ASC
        LIMIT 4
    ");
    $highlight_cars = $stmt->fetchAll();

    // If less than 4 featured, fill with random cars
    if (count($highlight_cars) < 4) {
        $featured_ids = array();
        foreach ($highlight_cars as $featured_car) {
            $featured_ids[] = (int) $featured_car['id'];
        }
        $exclude = !empty($featured_ids) ? "AND c.id NOT IN (" . implode(',', $featured_ids) . ")" : "";
        $remaining = 4 - count($highlight_cars);

        $stmt = $db->query("
            SELECT c.*, s.branch_name as schedule_name, s.auction_date as schedule_date
            FROM auction_cars c
            LEFT JOIN auction_schedules s ON c.schedule_id = s.id
            WHERE c.schedule_id IS NOT NULL $exclude
            ORDER BY RAND()
            LIMIT $remaining
        ");
        $highlight_cars = array_merge($highlight_cars, $stmt->fetchAll());
    }
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประมูลรถยนต์ - MIDA LEASING</title>
    <meta name="description"
        content="ศูนย์ประมูลรถยนต์มาตรฐานจาก ไมด้า ลิสซิ่ง รถมือสองสภาพดี มีรอบประมูลชัดเจน ราคาเปิดเผย และตรวจสอบรายการรถได้ก่อนเข้าร่วมประมูล">
    <meta name="keywords"
        content="รถประมูล, บ้านมือสองหลุดจำนำ, ทรัพย์สินรอการขาย">

    <!-- Favicon -->
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" href="<?php echo $settings['site_favicon']; ?>" type="image/x-icon">
    <?php else: ?>
        <link rel="icon" href="favicon.ico" type="image/x-icon">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.midaleasing.com/auction.php">
    <meta property="og:title" content="ประมูลรถยนต์ - MIDA LEASING">
    <meta property="og:description"
        content="ศูนย์ประมูลรถยนต์มาตรฐาน รถยึดสภาพดี ราคาเริ่มต้นต่ำกว่าท้องตลาด ประมูลอย่างเปิดเผยและโปร่งใส">
    <meta property="og:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.midaleasing.com/auction.php">
    <meta property="twitter:title" content="ประมูลรถยนต์ - MIDA LEASING">
    <meta property="twitter:description"
        content="ศูนย์ประมูลรถยนต์มาตรฐาน รถยึดสภาพดี ราคาเริ่มต้นต่ำกว่าท้องตลาด ประมูลอย่างเปิดเผยและโปร่งใส">
    <meta property="twitter:image" content="https://www.midaleasing.com/img/mida_logo_5.png">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .auction-hero {
            background:
                radial-gradient(circle at 18% 8%, rgba(116, 169, 255, 0.22), transparent 34%),
                radial-gradient(circle at 86% 18%, rgba(255, 255, 255, 0.12), transparent 28%),
                linear-gradient(135deg, #0f356f 0%, #174b99 46%, #2b68c8 100%);
            color: white;
            padding: 112px 0 34px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auction-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(180deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            opacity: 0.22;
            pointer-events: none;
        }

        .auction-hero .container {
            position: relative;
            z-index: 1;
        }

        .auction-hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #ffe08a;
            font-size: 0.92rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .auction-hero-kicker::before,
        .auction-hero-kicker::after {
            content: "";
            width: 28px;
            height: 3px;
            border-radius: 999px;
            background: #fec435;
        }

        .auction-hero h1 {
            max-width: 820px;
            margin: 0 auto 10px;
            color: #fec435;
            font-size: clamp(2rem, 4vw, 3.25rem);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.04em;
        }

        .auction-hero p {
            max-width: 760px;
            margin: 0 auto;
            color: #d9e6f7;
            font-size: 1.05rem;
            line-height: 1.65;
        }

        .auction-hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
        }

        .auction-brochure-section {
            background:
                radial-gradient(circle at 14% 8%, rgba(255, 199, 44, 0.18), transparent 30%),
                linear-gradient(180deg, #ffffff 0%, #f3f8ff 100%);
        }

        .auction-brochure-grid {
            display: grid;
            grid-template-columns: minmax(280px, 0.88fr) minmax(0, 1.12fr);
            gap: 34px;
            align-items: center;
        }

        .auction-brochure-frame {
            position: relative;
            padding: 12px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid rgba(23, 69, 143, 0.12);
            box-shadow: 0 28px 70px rgba(18, 72, 148, 0.16);
        }

        .auction-brochure-frame::after {
            content: "";
            position: absolute;
            inset: 22px -14px -14px 22px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(23, 69, 143, 0.1), rgba(255, 199, 44, 0.22));
            z-index: 0;
        }

        .auction-brochure-frame img {
            position: relative;
            z-index: 1;
            display: block;
            width: 100%;
            max-height: 640px;
            object-fit: contain;
            border-radius: 22px;
            background: #e8f4ff;
        }

        .auction-brochure-copy {
            position: relative;
            z-index: 1;
        }

        .auction-brochure-copy .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #b98500;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .auction-brochure-copy h2 {
            margin: 0 0 14px;
            color: var(--primary-blue);
            font-size: clamp(1.9rem, 3vw, 3rem);
            line-height: 1.12;
            letter-spacing: -0.03em;
        }

        .auction-brochure-copy > p {
            max-width: 680px;
            margin: 0 0 22px;
            color: #536274;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .auction-benefit-list {
            display: grid;
            gap: 12px;
            margin: 0 0 26px;
            padding: 0;
            list-style: none;
        }

        .auction-benefit-list li {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            color: #24364d;
            font-weight: 700;
            line-height: 1.55;
        }

        .auction-benefit-list i {
            width: 30px;
            height: 30px;
            display: inline-flex;
            flex: 0 0 30px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #fff4cf;
            color: #b98500;
        }

        .auction-channel-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .auction-channel-heading {
            margin: 0 0 14px;
            color: #0f2d5c;
            font-size: 1.28rem;
            font-weight: 900;
        }

        .auction-channel-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 190px;
            padding: 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(23, 69, 143, 0.1);
            box-shadow: 0 16px 36px rgba(18, 72, 148, 0.08);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .auction-channel-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 22px 44px rgba(18, 72, 148, 0.13);
        }

        .auction-channel-icon {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: #edf5ff;
            color: var(--primary-blue);
            font-size: 1.25rem;
        }

        .auction-channel-card h3 {
            margin: 0;
            color: #0f2d5c;
            font-size: 1.08rem;
        }

        .auction-channel-card p {
            margin: 0;
            color: #617187;
            line-height: 1.6;
            font-size: 0.94rem;
        }

        .auction-channel-card a,
        .auction-hero-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: fit-content;
            min-height: 42px;
            margin-top: auto;
            padding: 0 17px;
            border-radius: 999px;
            background: var(--primary-blue);
            color: #ffffff;
            font-weight: 800;
            text-decoration: none;
        }

        .auction-channel-card a.is-gold,
        .auction-hero-actions a.is-gold {
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            box-shadow: 0 10px 22px rgba(255, 199, 44, 0.24);
        }

        .auction-channel-card a.is-line {
            background: #00b900;
        }

        .schedule-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eee;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .auction-calendar-wrap {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
            gap: 24px;
            align-items: stretch;
        }

        .auction-calendar-card,
        .auction-list-panel {
            background: #ffffff;
            border: 1px solid rgba(23, 69, 143, 0.09);
            border-radius: 24px;
            box-shadow: 0 18px 42px rgba(23, 69, 143, 0.08);
            overflow: hidden;
        }

        .auction-calendar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 22px;
            background: linear-gradient(135deg, #17458f 0%, #2f6bc6 100%);
            color: #ffffff;
        }

        .auction-calendar-head h3 {
            margin: 0;
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .calendar-nav-dot {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .calendar-nav-dot:hover {
            background: rgba(255, 255, 255, 0.28);
            transform: translateY(-1px);
        }

        .auction-calendar-weekdays,
        .auction-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }

        .auction-calendar-weekdays {
            padding: 12px 14px 8px;
            color: var(--primary-blue);
            font-weight: 800;
            text-align: center;
            font-size: 0.9rem;
        }

        .auction-calendar-grid {
            padding: 0 14px 14px;
        }

        .calendar-day,
        .calendar-empty {
            min-height: 74px;
            padding: 9px 8px;
            border: 1px solid rgba(23, 69, 143, 0.08);
            background: #ffffff;
            text-align: right;
            color: #24364d;
            font-size: 0.9rem;
        }

        .calendar-empty {
            background: #f8fbff;
        }

        .calendar-day.has-auction {
            background: linear-gradient(180deg, #fff8df 0%, #fffdf4 100%);
            border-color: rgba(255, 199, 44, 0.45);
            color: var(--primary-blue);
            font-weight: 800;
        }

        .calendar-day.is-active {
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            border-color: var(--accent-gold);
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.62);
        }

        .calendar-day.is-past {
            background: #f1f4f8;
            border-color: rgba(122, 135, 152, 0.16);
            color: #8b96a6;
            box-shadow: none;
        }

        .calendar-day.is-clickable {
            padding: 0;
        }

        .calendar-day-link {
            display: block;
            min-height: 74px;
            padding: 9px 8px;
            color: inherit;
            text-decoration: none;
        }

        .calendar-day-link:hover {
            background: rgba(255, 255, 255, 0.34);
        }

        .calendar-count {
            display: block;
            margin-top: 12px;
            color: #0f2d5c;
            font-size: 0.68rem;
            font-weight: 800;
            text-align: right;
            line-height: 1.25;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .calendar-day.is-past .calendar-count {
            color: #8b96a6;
        }

        .auction-list-panel {
            padding: 24px;
        }

        .auction-list-panel h3 {
            margin: 0 0 6px;
            color: #0f2d5c;
            font-size: 1.3rem;
            font-weight: 800;
        }

        .auction-list-date {
            margin: 0 0 22px;
            color: #536274;
            font-size: 0.95rem;
        }

        .auction-round-list {
            display: grid;
            gap: 16px;
            max-height: 410px;
            overflow: auto;
            padding-right: 6px;
        }

        .auction-round-item {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 14px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(23, 69, 143, 0.1);
        }

        .auction-round-count {
            width: 54px;
            height: 54px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.1;
            box-shadow: 0 10px 22px rgba(255, 199, 44, 0.24);
        }

        .auction-round-count span {
            font-size: 0.62rem;
            font-weight: 700;
        }

        .auction-round-count.is-pending {
            width: 68px;
            height: 54px;
            border-radius: 18px;
            background: linear-gradient(135deg, #eef4ff 0%, #ffffff 100%);
            color: var(--primary-blue);
            box-shadow: inset 0 0 0 1px rgba(23, 69, 143, 0.12);
            font-size: 0.78rem;
        }

        .auction-round-count.is-pending span {
            font-size: 0.66rem;
        }

        .auction-round-copy strong {
            display: block;
            margin-bottom: 5px;
            color: #0f2d5c;
            font-size: 1rem;
        }

        .auction-round-copy p {
            margin: 0 0 10px;
            color: #536274;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .auction-round-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .auction-register-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 15px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, #ffe07a 100%);
            color: #0f2d5c;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 10px 22px rgba(255, 199, 44, 0.24);
        }

        .auction-register-main {
            width: 100%;
            margin: 0 0 22px;
            padding: 13px 18px;
            font-size: 1rem;
        }

        .auction-empty-month {
            padding: 28px 18px;
            border-radius: 18px;
            background: #f8fbff;
            color: #536274;
            text-align: center;
            font-weight: 700;
        }

        .auction-detail-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--primary-blue);
            font-weight: 800;
            text-decoration: none;
        }

        .auction-detail-link.is-disabled {
            color: #7a8798;
            cursor: default;
            pointer-events: none;
        }

        .auction-calendar-note {
            margin: 18px 0 0;
            color: #7a5b08;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .schedule-header {
            background: var(--primary-blue);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-body {
            padding: 20px;
        }

        .schedule-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #eee;
        }

        .schedule-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .car-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
            transition: all 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .car-img {
            height: 180px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 3rem;
            position: relative;
        }

        .car-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-gold);
            color: #000;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .car-info {
            padding: 15px;
        }

        .car-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .car-details {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
        }

        .car-price {
            color: var(--primary-blue);
            font-size: 1.2rem;
            font-weight: 700;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        .auction-steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .auction-legacy-schedule {
            display: none;
        }

        @media (max-width: 900px) {
            .auction-brochure-grid,
            .auction-calendar-wrap {
                grid-template-columns: 1fr;
            }

            .auction-channel-grid {
                grid-template-columns: 1fr;
            }

            .auction-brochure-frame img {
                max-height: none;
            }

            .calendar-day,
            .calendar-empty {
                min-height: 58px;
                padding: 7px 6px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php $active_page = 'auction';
    include 'includes/nav.php'; ?>

    <!-- Hero Section -->
    <section class="auction-hero">
        <div class="container">
            <span class="auction-hero-kicker">MIDA AUCTION</span>
            <h1>ประมูลรถมือสองไมด้า</h1>
            <p>
                เช็กรอบประมูล ดูรายการรถ และเลือกช่องทางเข้าร่วมได้ในที่เดียว
            </p>
            <div class="auction-hero-actions">
                <a href="#auction-brochure" class="is-gold"><i class="fa-solid fa-image"></i> ดูโบรชัวร์รอบล่าสุด</a>
                <a href="#auction-calendar-section"><i class="fa-solid fa-calendar-days"></i> ดูปฏิทินประมูล</a>
            </div>
        </div>
    </section>

    <!-- Auction Brochure & Channels -->
    <section class="section auction-brochure-section" id="auction-brochure">
        <div class="container">
            <div class="auction-brochure-grid">
                <div class="auction-brochure-frame">
                    <img src="<?php echo htmlspecialchars($auction_brochure['image_path']); ?>"
                        alt="<?php echo htmlspecialchars($auction_brochure['title']); ?>">
                </div>

                <div class="auction-brochure-copy">
                    <span class="eyebrow"><i class="fa-solid fa-bullhorn"></i> <?php echo htmlspecialchars($auction_brochure['round_label']); ?></span>
                    <h2><?php echo htmlspecialchars($auction_brochure['title']); ?></h2>
                    <p>
                        <?php echo htmlspecialchars($auction_brochure['description']); ?>
                    </p>

                    <ul class="auction-benefit-list">
                        <li><i class="fa-solid fa-car-side"></i> ดูรถมือสองที่เปิดประมูล พร้อมตรวจสอบรายการรถก่อนตัดสินใจ</li>
                        <li><i class="fa-solid fa-calendar-check"></i> เช็กวัน เวลา สาขา และรอบประมูลที่สนใจได้ล่วงหน้า</li>
                        <li><i class="fa-solid fa-handshake"></i> มีเจ้าหน้าที่ให้คำแนะนำช่องทางเข้าร่วมและขั้นตอนประมูล</li>
                    </ul>

                    <h3 class="auction-channel-heading">ช่องทางเข้าร่วมประมูล</h3>
                    <div class="auction-channel-grid">
                        <div class="auction-channel-card">
                            <span class="auction-channel-icon"><i class="fa-solid fa-user-plus"></i></span>
                            <h3>ลงทะเบียนออนไลน์</h3>
                            <p>ลงทะเบียนเข้าร่วมประมูลผ่านระบบของไมด้าได้โดยตรง</p>
                            <a href="<?php echo htmlspecialchars($auction_brochure['registration_link']); ?>" target="_blank" rel="noopener" class="is-gold">
                                ลงทะเบียน
                            </a>
                        </div>

                        <div class="auction-channel-card">
                            <span class="auction-channel-icon"><i class="fa-solid fa-list-check"></i></span>
                            <h3>ดูรายการรถ</h3>
                            <p>เลือกดูรายการรถของแต่ละรอบก่อนเดินทางไปดูรถหรือเข้าร่วมประมูล</p>
                            <a href="#auction-calendar-section">
                                ดูรอบประมูล
                            </a>
                        </div>

                        <div class="auction-channel-card">
                            <span class="auction-channel-icon"><i class="fa-brands fa-line"></i></span>
                            <h3>สอบถามเจ้าหน้าที่</h3>
                            <p>ติดต่อเจ้าหน้าที่เพื่อสอบถามรอบประมูล เอกสาร และรายละเอียดเพิ่มเติม</p>
                            <a href="<?php echo htmlspecialchars($auction_brochure['line_link']); ?>" target="_blank" rel="noopener" class="is-line">
                                คุยผ่าน LINE
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Auction Calendar Section -->
    <section class="section" id="auction-calendar-section" style="background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-title">
                <h2>ปฏิทินการประมูล</h2>
                <p>ตรวจสอบวัน เวลา และรายการรถก่อนเข้าร่วมประมูล</p>
            </div>

            <div class="auction-calendar-wrap">
                <div class="auction-calendar-card">
                    <div class="auction-calendar-head">
                        <button class="calendar-nav-dot" type="button" id="auctionCalendarPrev" aria-label="เดือนก่อนหน้า"><i class="fa-solid fa-chevron-left"></i></button>
                        <h3 id="auctionCalendarTitle"><?php echo htmlspecialchars($calendar_title); ?></h3>
                        <button class="calendar-nav-dot" type="button" id="auctionCalendarNext" aria-label="เดือนถัดไป"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                    <div class="auction-calendar-weekdays">
                        <div>จ.</div>
                        <div>อ.</div>
                        <div>พ.</div>
                        <div>พฤ.</div>
                        <div>ศ.</div>
                        <div>ส.</div>
                        <div>อา.</div>
                    </div>
                    <div class="auction-calendar-grid" id="auctionCalendarGrid">
                        <?php $first_active_day = 0; ?>
                        <?php for ($empty = 1; $empty < $calendar_first_day; $empty++): ?>
                            <div class="calendar-empty" aria-hidden="true"></div>
                        <?php endfor; ?>
                        <?php for ($day = 1; $day <= $calendar_days_in_month; $day++):
                            $day_schedules = isset($auction_calendar_days[$day]) ? $auction_calendar_days[$day] : array();
                            $day_count = count($day_schedules);
                            $day_branch_label = '';
                            $day_url = '';
                            $day_key = (int) ($calendar_year . str_pad($calendar_month, 2, '0', STR_PAD_LEFT) . str_pad($day, 2, '0', STR_PAD_LEFT));
                            $is_past_day = $day_count > 0 && $day_key < $today_key;
                            if ($day_count > 0) {
                                $day_branch_label = auction_branch_label($day_schedules[0]['branch_name']);
                                $day_url = 'auction_list.php?schedule_id=' . (int) $day_schedules[0]['id'];
                                if ($day_count > 1) {
                                    $day_branch_label .= ' +' . ($day_count - 1) . ' รอบ';
                                }
                            }
                            if ($day_count > 0 && !$is_past_day && $first_active_day === 0) {
                                $first_active_day = $day;
                            }
                            $day_class = $day_count > 0 ? 'calendar-day has-auction' : 'calendar-day';
                            if ($is_past_day) {
                                $day_class .= ' is-past';
                            } elseif ($day_count > 0) {
                                $day_class .= ' is-clickable';
                            }
                            if ($day_count > 0 && !$is_past_day && $first_active_day === $day) {
                                $day_class .= ' is-active';
                            }
                            ?>
                            <div class="<?php echo $day_class; ?>">
                                <?php if ($day_count > 0 && !$is_past_day): ?>
                                    <a href="<?php echo htmlspecialchars($day_url); ?>" class="calendar-day-link">
                                        <?php echo $day; ?>
                                        <span class="calendar-count"><?php echo htmlspecialchars($day_branch_label); ?></span>
                                    </a>
                                <?php else: ?>
                                    <?php echo $day; ?>
                                    <?php if ($day_count > 0): ?>
                                        <span class="calendar-count"><?php echo htmlspecialchars($day_branch_label); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="auction-list-panel">
                    <h3>รายการประมูล</h3>
                    <p class="auction-list-date">รอบประมูลล่าสุดและรายการรถที่เปิดให้เข้าร่วม</p>

                    <a href="https://auction.mida-leasing.com" target="_blank" rel="noopener" class="auction-register-btn auction-register-main">
                        <i class="fa-solid fa-user-plus"></i> ลงทะเบียนเข้าร่วมประมูล
                    </a>

                    <?php if (count($schedules) > 0): ?>
                        <div class="auction-round-list" id="auctionRoundList">
                            <?php foreach ($schedules as $schedule):
                                $count = (int) $schedule['actual_car_count'];
                                ?>
                                <div class="auction-round-item">
                                    <?php if ($count > 0): ?>
                                    <div class="auction-round-count">
                                        <?php echo $count; ?>
                                        <span>คัน</span>
                                    </div>
                                    <?php else: ?>
                                    <div class="auction-round-count is-pending">
                                        รอ
                                        <span>อัปเดต</span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="auction-round-copy">
                                        <strong><?php echo htmlspecialchars($schedule['branch_name']); ?></strong>
                                        <p>
                                            <i class="fa-solid fa-calendar-check"></i>
                                            <?php echo htmlspecialchars($schedule['auction_date']); ?>
                                            <?php if (!empty($schedule['time_start'])): ?>
                                                · เริ่ม <?php echo htmlspecialchars($schedule['time_start']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($schedule['time_register'])): ?>
                                                · ลงทะเบียน <?php echo htmlspecialchars($schedule['time_register']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <div class="auction-round-actions">
                                            <?php if ($count > 0): ?>
                                                <a href="auction_list.php?schedule_id=<?php echo $schedule['id']; ?>" class="auction-detail-link">
                                                    <i class="fa-solid fa-eye"></i> ดูรายการรถ
                                                </a>
                                            <?php else: ?>
                                                <span class="auction-detail-link is-disabled">
                                                    <i class="fa-solid fa-clock"></i> รออัปเดตรายการรถ
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 34px 18px; background: #f8fbff; border-radius: 18px;">
                            <p style="color: #536274; margin: 0;">ยังไม่มีรอบประมูลในขณะนี้</p>
                        </div>
                    <?php endif; ?>

                    <p class="auction-calendar-note">* หมายเหตุ: ปฏิทินการประมูลอาจมีการเปลี่ยนแปลงได้</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Section -->
    <section class="section auction-legacy-schedule" style="background-color: #f8f9fa;">
        <div class="container">
            <div class="section-title">
                <h2>ตารางการประมูล</h2>
            </div>

            <div class="row"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
                <?php if (count($schedules) > 0): ?>
                    <?php foreach ($schedules as $index => $schedule):
                        $count = $schedule['actual_car_count'];
                        ?>
                        <div class="schedule-card"
                            style="border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
                            <div class="schedule-header"
                                style="background: linear-gradient(135deg, <?php echo $index % 2 == 0 ? '#002D62, #1c4587' : '#1c4587, #2e5d9e'; ?>); padding: 20px 25px;">
                                <div>
                                    <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">
                                        <i class="fa-solid fa-calendar-check"></i> รอบประมูล
                                    </div>
                                    <div style="font-weight: 600; font-size: 1.3rem;">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?php echo htmlspecialchars($schedule['branch_name']); ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div
                                        style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; font-size: 1rem; font-weight: 500;">
                                        <?php echo htmlspecialchars($schedule['auction_date']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="schedule-body" style="padding: 25px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <div style="color: #888; font-size: 0.85rem; margin-bottom: 5px;">
                                            <i class="fa-solid fa-user-plus"></i> ลงทะเบียน
                                        </div>
                                        <div style="font-weight: 600; font-size: 1.1rem; color: #333;">
                                            <?php echo htmlspecialchars($schedule['time_register']); ?>
                                        </div>
                                    </div>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <div style="color: #888; font-size: 0.85rem; margin-bottom: 5px;">
                                            <i class="fa-solid fa-gavel"></i> เริ่มประมูล
                                        </div>
                                        <div style="font-weight: 600; font-size: 1.1rem; color: #333;">
                                            <?php echo htmlspecialchars($schedule['time_start']); ?>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    style="display: flex; align-items: center; justify-content: center; gap: 15px; padding: 20px; border-radius: 12px; margin-bottom: 20px; <?php echo $count > 0 ? 'background: #f8f9fa;' : 'background: #f5f5f5; color: #999; border: 2px dashed #ddd;'; ?>">
                                    <div style="text-align: center;">
                                        <?php if ($count > 0): ?>
                                            <i class="fa-solid fa-car" style="font-size: 2rem; opacity: 0.8;"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-clock" style="font-size: 1.5rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.85rem; opacity: 0.8;">จำนวนรถในรอบนี้</div>
                                        <div style="font-size: 1.6rem; font-weight: 700;">
                                            <?php echo $count > 0 ? $count . ' คัน' : 'เร็วๆ นี้'; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($count > 0): ?>
                                    <a href="auction_list.php?schedule_id=<?php echo $schedule['id']; ?>" class="btn btn-primary"
                                        style="width: 100%; text-align: center; padding: 12px; font-size: 1rem; border-radius: 10px;">
                                        <i class="fa-solid fa-eye"></i> ดูรายการรถ
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div
                        style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 10px;">
                        <p style="color: #888;">ยังไม่มีตารางการประมูลในขณะนี้</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Hightlight Cars -->
    <section class="section" id="featured-auction-cars">
        <div class="container">
            <div class="section-title">
                <h2>รถเด่นประจำรอบ</h2>
                <p>รถสวยคัดพิเศษ สภาพพร้อมใช้งาน</p>
            </div>

            <div class="car-grid">
                <?php if (count($highlight_cars) > 0): ?>
                    <?php foreach ($highlight_cars as $car): ?>
                        <div class="car-card">
                            <div class="car-img">
                                <?php if (!empty($car['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($car['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($car['title']); ?>"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fa-solid fa-car-side"></i>
                                <?php endif; ?>
                                <?php if (!empty($car['queue_number'])): ?>
                                    <span
                                        style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">
                                        คันที่: <?php echo htmlspecialchars($car['queue_number']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="car-info">
                                <h3 class="car-title"><?php echo htmlspecialchars($car['title']); ?></h3>
                                <div class="car-details">
                                    <span><i class="fa-solid fa-gauge"></i>
                                        <?php echo htmlspecialchars($car['mileage']); ?></span>
                                    <span><i class="fa-solid fa-gear"></i>
                                        <?php echo htmlspecialchars($car['transmission']); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: end;">
                                    <div>
                                        <div style="font-size: 0.8rem; color: #888;">ราคาเปิดประมูล</div>
                                        <?php if (!empty($car['no_starting_price']) && $car['no_starting_price'] == 1): ?>
                                            <div class="car-price" style="color: #e74c3c;">ไม่มีราคาเริ่มต้น</div>
                                        <?php else: ?>
                                            <div class="car-price"><?php echo htmlspecialchars($car['price']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="auction_detail.php?id=<?php echo $car['id']; ?>" class="btn btn-accent"
                                        style="padding: 5px 15px; font-size: 0.9rem;">ดูรูป</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div
                        style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 10px;">
                        <p style="color: #888;">ยังไม่มีรถแนะนำในขณะนี้</p>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 40px;">
                <a href="auction_list.php" class="btn btn-primary btn-outline">ดูรายการรถทั้งหมด <i
                        class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const auctionEvents = <?php echo json_encode($auction_calendar_events); ?>;
        const monthLabels = [
            '',
            'มกราคม',
            'กุมภาพันธ์',
            'มีนาคม',
            'เมษายน',
            'พฤษภาคม',
            'มิถุนายน',
            'กรกฎาคม',
            'สิงหาคม',
            'กันยายน',
            'ตุลาคม',
            'พฤศจิกายน',
            'ธันวาคม'
        ];
        let currentMonth = <?php echo (int) $calendar_month; ?>;
        let currentYear = <?php echo (int) $calendar_year; ?>;

        const title = document.getElementById('auctionCalendarTitle');
        const grid = document.getElementById('auctionCalendarGrid');
        const list = document.getElementById('auctionRoundList');
        const prev = document.getElementById('auctionCalendarPrev');
        const next = document.getElementById('auctionCalendarNext');
        const todayDateParts = new Intl.DateTimeFormat('en-CA', {
            timeZone: 'Asia/Bangkok',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date()).split('-').map(Number);
        const todayKey = (todayDateParts[0] * 10000) + (todayDateParts[1] * 100) + todayDateParts[2];

        function getEventsForMonth(year, month) {
            return auctionEvents
                .filter(function(event) {
                    return Number(event.year) === year && Number(event.month) === month;
                })
                .sort(function(a, b) {
                    return Number(a.day) - Number(b.day);
                });
        }

        function getDateKey(year, month, day) {
            return (Number(year) * 10000) + (Number(month) * 100) + Number(day);
        }

        function getBranchLabel(event) {
            return String(event.branchLabel || event.branchName || '').replace(/^สาขา\s*/u, '').trim();
        }

        function renderCalendar() {
            if (!title || !grid) return;

            const events = getEventsForMonth(currentYear, currentMonth);
            const eventsByDay = events.reduce(function(groups, event) {
                const day = Number(event.day);
                if (!groups[day]) groups[day] = [];
                groups[day].push(event);
                return groups;
            }, {});
            const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
            const leadingEmpty = (firstDay + 6) % 7;
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const firstActiveEvent = events.find(function(event) {
                return getDateKey(Number(event.year), Number(event.month), Number(event.day)) >= todayKey;
            });
            const firstActiveDay = firstActiveEvent ? Number(firstActiveEvent.day) : 0;
            let html = '';

            title.textContent = monthLabels[currentMonth] + ' ' + (currentYear + 543);

            for (let i = 0; i < leadingEmpty; i++) {
                html += '<div class="calendar-empty" aria-hidden="true"></div>';
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayEvents = eventsByDay[day] || [];
                let branchLabel = '';
                let dayUrl = '';
                const isPastDay = dayEvents.length > 0 && getDateKey(currentYear, currentMonth, day) < todayKey;
                const classes = ['calendar-day'];

                if (dayEvents.length) classes.push('has-auction');
                if (isPastDay) {
                    classes.push('is-past');
                } else if (dayEvents.length) {
                    classes.push('is-clickable');
                }
                if (!isPastDay && firstActiveDay === day) classes.push('is-active');
                if (dayEvents.length) {
                    branchLabel = getBranchLabel(dayEvents[0]);
                    dayUrl = dayEvents[0].url || '';
                    if (dayEvents.length > 1) {
                        branchLabel += ' +' + (dayEvents.length - 1) + ' รอบ';
                    }
                }

                html += '<div class="' + classes.join(' ') + '">';
                if (dayEvents.length && !isPastDay) {
                    html += '<a href="' + escapeHtml(dayUrl) + '" class="calendar-day-link">' + day +
                        '<span class="calendar-count">' + escapeHtml(branchLabel) + '</span></a>';
                } else {
                    html += day;
                    if (dayEvents.length) {
                        html += '<span class="calendar-count">' + escapeHtml(branchLabel) + '</span>';
                    }
                }
                html += '</div>';
            }

            grid.innerHTML = html;
            renderRoundList(events);
        }

        function renderRoundList(events) {
            if (!list) return;

            if (!events.length) {
                list.innerHTML = '<div class="auction-empty-month">ยังไม่มีรอบประมูลในเดือนนี้</div>';
                return;
            }

            list.innerHTML = events.map(function(event) {
                const carCount = Number(event.carCount || 0);
                const countBadge = carCount > 0
                    ? '<div class="auction-round-count">' + carCount + '<span>คัน</span></div>'
                    : '<div class="auction-round-count is-pending">รอ<span>อัปเดต</span></div>';
                const detailLink = carCount > 0
                    ? '<a href="' + event.url + '" class="auction-detail-link"><i class="fa-solid fa-eye"></i> ดูรายการรถ</a>'
                    : '<span class="auction-detail-link is-disabled"><i class="fa-solid fa-clock"></i> รออัปเดตรายการรถ</span>';

                return [
                    '<div class="auction-round-item">',
                        countBadge,
                        '<div class="auction-round-copy">',
                            '<strong>' + escapeHtml(event.branchName || '') + '</strong>',
                            '<p><i class="fa-solid fa-calendar-check"></i> ' + escapeHtml(event.auctionDate || '') +
                                (event.timeStart ? ' · เริ่ม ' + escapeHtml(event.timeStart) : '') +
                                (event.timeRegister ? ' · ลงทะเบียน ' + escapeHtml(event.timeRegister) : '') +
                            '</p>',
                            '<div class="auction-round-actions">' + detailLink + '</div>',
                        '</div>',
                    '</div>'
                ].join('');
            }).join('');
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function shiftMonth(offset) {
            currentMonth += offset;
            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear -= 1;
            }
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear += 1;
            }
            renderCalendar();
        }

        if (prev) prev.addEventListener('click', function() { shiftMonth(-1); });
        if (next) next.addEventListener('click', function() { shiftMonth(1); });

        renderCalendar();
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var featuredTitle = document.querySelector('#featured-auction-cars .section-title h2');
        var featuredSubtitle = document.querySelector('#featured-auction-cars .section-title p');

        if (featuredTitle) {
            featuredTitle.textContent = 'รถสวยคัดพิเศษ สภาพพร้อมใช้งาน';
        }

        if (featuredSubtitle) {
            featuredSubtitle.textContent = 'คัดจากรายการรถประมูลที่น่าสนใจ พร้อมดูรายละเอียดก่อนเข้าร่วมประมูล';
        }
    });
    </script>

    <!-- How to -->
    <section class="section" style="background-color: #f0f4f8;">
        <div class="container">
            <div class="section-title">
                <h2>ขั้นตอนการประมูล</h2>
                <p>ง่ายๆ ใครก็ประมูลได้</p>
            </div>

            <div class="auction-steps-grid">
                <div>
                    <div class="step-circle">1</div>
                    <h4 style="margin-bottom: 10px;">ลงทะเบียน</h4>
                    <p class="text-secondary">นำบัตรประชาชนมาลงทะเบียน<br>และวางเงินมัดจำป้าย</p>
                </div>
                <div>
                    <div class="step-circle">2</div>
                    <h4 style="margin-bottom: 10px;">ตรวจดูสภาพรถ</h4>
                    <p class="text-secondary">เดินชมรถที่ลานประมูล<br>สตาร์ทเครื่องยนต์ ตรวจสอบสภาพ</p>
                </div>
                <div>
                    <div class="step-circle">3</div>
                    <h4 style="margin-bottom: 10px;">ยกป้ายสู้ราคา</h4>
                    <p class="text-secondary">เมื่อถึงคิวรถที่ชอบ<br>ยกป้ายเสนอราคาแข่งกัน</p>
                </div>
                <div>
                    <div class="step-circle">4</div>
                    <h4 style="margin-bottom: 10px;">ชำระเงินและรับรถ</h4>
                    <p class="text-secondary">ชนะประมูล ชำระเงินส่วนที่เหลือ<br>และรับรถกลับบ้านได้เลย</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">MIDA LEASING</div>
                    <p style="color: #ccc; margin-bottom: 10px;">บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</p>
                    <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">
                        <?php echo nl2br(htmlspecialchars($settings['site_address'])); ?>
                    </p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone"
                            style="margin-right: 10px;"></i>
                        <?php echo htmlspecialchars($settings['site_phone']); ?>
                    </p>
                    <div style="display: flex; gap: 15px;">
                        <a href="<?php echo htmlspecialchars($settings['site_facebook']); ?>" target="_blank"
                            style="text-decoration: none;">
                            <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars($settings['site_line']); ?>" target="_blank"
                            style="text-decoration: none;">
                            <i class="fa-brands fa-line" style="font-size: 2rem; color: #00B900;"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-links">
                    <h4>บริการของเรา</h4>
                    <ul>
                        <li><a href="service_hire_purchase.php">สินเชื่อเช่าซื้อ</a></li>
                        <li><a href="service_title_loan.php">สินเชื่อจำนำทะเบียน</a></li>
                        <li><a href="service_personal_loan.php">สินเชื่อส่วนบุคคล</a></li>
                        <li><a href="service_insurance.php">ต่อภาษีและประกันภัย</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>นักลงทุนสัมพันธ์</h4>
                    <ul>
                        <li><a href="investor_business.php">วิสัยทัศน์และพันธกิจ</a></li>
                        <li><a href="investor_financial.php">ข้อมูลทางการเงิน</a></li>
                        <li><a href="investor_publications.php">เอกสารเผยแพร่</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>ติดต่อเรา</h4>
                    <ul>
                        <li><a href="contact_branches.php">แผนที่สาขา</a></li>
                        <li><a href="contact_career.php">ร่วมงานกับเรา</a></li>
                        <li><a href="contact_us.php">ติดต่อสอบถาม</a></li>
                    </ul>
                </div>
            </div>

            <div class="copyright">
                &copy; 2026 Mida Leasing Public Company Limited. All Rights Reserved.
                <br>
                <div style="margin-top: 10px;">
                    <a href="privacy_policy.php"
                        style="color: #888; text-decoration: none; margin: 0 10px;">นโยบายความเป็นส่วนตัว</a> |
                    <a href="cookie_policy.php"
                        style="color: #888; text-decoration: none; margin: 0 10px;">นโยบายเกี่ยวกับ
                        cookie</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="assets/js/main.js"></script>

</body>

</html>
