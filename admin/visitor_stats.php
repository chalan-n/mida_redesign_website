<?php
session_start();
require_once 'config/db.php';

$database = new Database();
$db = $database->getConnection();

require_once 'includes/header.php';

// Get date range filter
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Fetch summary stats
$summary = [
    'total_views' => 0,
    'unique_visitors' => 0,
    'today_views' => 0,
    'today_visitors' => 0
];

try {
    // Total stats for date range
    $stmt = $db->prepare("
        SELECT 
            SUM(total_views) as total_views,
            SUM(unique_visitors) as unique_visitors
        FROM daily_stats 
        WHERE stat_date BETWEEN :start AND :end
    ");
    $stmt->execute([':start' => $start_date, ':end' => $end_date]);
    $result = $stmt->fetch();
    $summary['total_views'] = $result['total_views'] ?? 0;
    $summary['unique_visitors'] = $result['unique_visitors'] ?? 0;

    // Today's stats
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT total_views, unique_visitors FROM daily_stats WHERE stat_date = :today");
    $stmt->execute([':today' => $today]);
    $today_stats = $stmt->fetch();
    $summary['today_views'] = $today_stats['total_views'] ?? 0;
    $summary['today_visitors'] = $today_stats['unique_visitors'] ?? 0;

    // Daily stats for chart
    $stmt = $db->prepare("
        SELECT stat_date, total_views, unique_visitors 
        FROM daily_stats 
        WHERE stat_date BETWEEN :start AND :end
        ORDER BY stat_date ASC
    ");
    $stmt->execute([':start' => $start_date, ':end' => $end_date]);
    $daily_data = $stmt->fetchAll();

    // Top pages (ignore query string, group by page name)
    $stmt = $db->prepare("
        SELECT SUBSTRING_INDEX(page_url, '?', 1) as page_path, COUNT(*) as views
        FROM page_views
        WHERE DATE(visited_at) BETWEEN :start AND :end
        GROUP BY page_path
        ORDER BY views DESC
        LIMIT 10
    ");
    $stmt->execute([':start' => $start_date, ':end' => $end_date]);
    $top_pages = $stmt->fetchAll();

    // Device stats
    $device_stats = [];
    try {
        $stmt = $db->prepare("
            SELECT device_type, COUNT(*) as views
            FROM page_views
            WHERE DATE(visited_at) BETWEEN :start AND :end AND device_type IS NOT NULL
            GROUP BY device_type
            ORDER BY views DESC
        ");
        $stmt->execute([':start' => $start_date, ':end' => $end_date]);
        $device_stats = $stmt->fetchAll();
    } catch (PDOException $e) {
    }

    // Province stats
    $province_stats = [];
    try {
        $stmt = $db->prepare("
            SELECT province, COUNT(*) as views
            FROM page_views
            WHERE DATE(visited_at) BETWEEN :start AND :end AND province IS NOT NULL AND province != ''
            GROUP BY province
            ORDER BY views DESC
            LIMIT 15
        ");
        $stmt->execute([':start' => $start_date, ':end' => $end_date]);
        $province_stats = $stmt->fetchAll();
    } catch (PDOException $e) {
    }

    // Province name mapping (English to Thai)
    $province_names = [
        'Bangkok' => 'กรุงเทพมหานคร',
        'Samut Prakan' => 'สมุทรปราการ',
        'Nonthaburi' => 'นนทบุรี',
        'Pathum Thani' => 'ปทุมธานี',
        'Phra Nakhon Si Ayutthaya' => 'พระนครศรีอยุธยา',
        'Ang Thong' => 'อ่างทอง',
        'Lopburi' => 'ลพบุรี',
        'Sing Buri' => 'สิงห์บุรี',
        'Chai Nat' => 'ชัยนาท',
        'Saraburi' => 'สระบุรี',
        'Chon Buri' => 'ชลบุรี',
        'Rayong' => 'ระยอง',
        'Chanthaburi' => 'จันทบุรี',
        'Trat' => 'ตราด',
        'Chachoengsao' => 'ฉะเชิงเทรา',
        'Prachin Buri' => 'ปราจีนบุรี',
        'Nakhon Nayok' => 'นครนายก',
        'Sa Kaeo' => 'สระแก้ว',
        'Nakhon Ratchasima' => 'นครราชสีมา',
        'Buri Ram' => 'บุรีรัมย์',
        'Surin' => 'สุรินทร์',
        'Si Sa Ket' => 'ศรีสะเกษ',
        'Ubon Ratchathani' => 'อุบลราชธานี',
        'Yasothon' => 'ยโสธร',
        'Chaiyaphum' => 'ชัยภูมิ',
        'Amnat Charoen' => 'อำนาจเจริญ',
        'Bueng Kan' => 'บึงกาฬ',
        'Nong Bua Lam Phu' => 'หนองบัวลำภู',
        'Khon Kaen' => 'ขอนแก่น',
        'Udon Thani' => 'อุดรธานี',
        'Loei' => 'เลย',
        'Nong Khai' => 'หนองคาย',
        'Maha Sarakham' => 'มหาสารคาม',
        'Roi Et' => 'ร้อยเอ็ด',
        'Kalasin' => 'กาฬสินธุ์',
        'Sakon Nakhon' => 'สกลนคร',
        'Nakhon Phanom' => 'นครพนม',
        'Mukdahan' => 'มุกดาหาร',
        'Chiang Mai' => 'เชียงใหม่',
        'Lamphun' => 'ลำพูน',
        'Lampang' => 'ลำปาง',
        'Uttaradit' => 'อุตรดิตถ์',
        'Phrae' => 'แพร่',
        'Nan' => 'น่าน',
        'Phayao' => 'พะเยา',
        'Chiang Rai' => 'เชียงราย',
        'Mae Hong Son' => 'แม่ฮ่องสอน',
        'Nakhon Sawan' => 'นครสวรรค์',
        'Uthai Thani' => 'อุทัยธานี',
        'Kamphaeng Phet' => 'กำแพงเพชร',
        'Tak' => 'ตาก',
        'Sukhothai' => 'สุโขทัย',
        'Phitsanulok' => 'พิษณุโลก',
        'Phichit' => 'พิจิตร',
        'Phetchabun' => 'เพชรบูรณ์',
        'Ratchaburi' => 'ราชบุรี',
        'Kanchanaburi' => 'กาญจนบุรี',
        'Suphan Buri' => 'สุพรรณบุรี',
        'Nakhon Pathom' => 'นครปฐม',
        'Samut Sakhon' => 'สมุทรสาคร',
        'Samut Songkhram' => 'สมุทรสงคราม',
        'Phetchaburi' => 'เพชรบุรี',
        'Prachuap Khiri Khan' => 'ประจวบคีรีขันธ์',
        'Nakhon Si Thammarat' => 'นครศรีธรรมราช',
        'Krabi' => 'กระบี่',
        'Phang Nga' => 'พังงา',
        'Phuket' => 'ภูเก็ต',
        'Surat Thani' => 'สุราษฎร์ธานี',
        'Ranong' => 'ระนอง',
        'Chumphon' => 'ชุมพร',
        'Songkhla' => 'สงขลา',
        'Satun' => 'สตูล',
        'Trang' => 'ตรัง',
        'Phatthalung' => 'พัทลุง',
        'Pattani' => 'ปัตตานี',
        'Yala' => 'ยะลา',
        'Narathiwat' => 'นราธิวาส'
    ];

    // Page name mapping (URL to Thai name)
    $page_names = [
        '/' => 'หน้าแรก',
        '/index.php' => 'หน้าแรก',
        '/about.php' => 'เกี่ยวกับเรา',
        '/contact.php' => 'ติดต่อเรา',
        '/contact_branches.php' => 'สาขา',
        '/services.php' => 'บริการของเรา',
        '/service_hire_purchase.php' => 'บริการเช่าซื้อ',
        '/service_loan.php' => 'บริการสินเชื่อ',
        '/service_insurance.php' => 'บริการประกันภัย',
        '/loan_apply.php' => 'สมัครสินเชื่อ',
        '/hirepurchase.php' => 'สมัครเช่าซื้อ',
        '/auction.php' => 'ประมูลรถ',
        '/auction_list.php' => 'รายการรถประมูล',
        '/auction_detail.php' => 'รายละเอียดรถประมูล',
        '/properties.php' => 'บ้าน คอนโด ที่ดิน',
        '/property_detail.php' => 'รายละเอียดอสังหาฯ',
        '/careers.php' => 'ร่วมงานกับเรา',
        '/investor.php' => 'นักลงทุนสัมพันธ์',
        '/investor_financial.php' => 'ข้อมูลทางการเงิน',
        '/investor_publications.php' => 'เอกสารดาวน์โหลด',
        '/privacy.php' => 'นโยบายความเป็นส่วนตัว',
        '/terms.php' => 'ข้อกำหนดการใช้งาน'
    ];

    // Helper function to get Thai page name
    function getPageNameThai($url, $page_names)
    {
        // Extract path from URL (remove query string)
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path)
            $path = $url;

        // Remove /webmida_newdesign prefix if exists
        $path = preg_replace('#^/webmida_newdesign#', '', $path);

        // Check direct match
        if (isset($page_names[$path])) {
            return $page_names[$path];
        }

        // Check with .php extension
        foreach ($page_names as $key => $name) {
            if (strpos($path, $key) !== false) {
                return $name;
            }
        }

        // Return original if no match
        return basename($path) ?: $path;
    }

    // Recent visitors with device and province
    $stmt = $db->prepare("
        SELECT ip_address, page_url, device_type, province, visited_at
        FROM page_views
        ORDER BY visited_at DESC
        LIMIT 20
    ");
    $stmt->execute();
    $recent_visitors = $stmt->fetchAll();

} catch (PDOException $e) {
    // Tables might not exist
    $daily_data = [];
    $top_pages = [];
    $device_stats = [];
    $province_stats = [];
    $recent_visitors = [];
}

// Prepare chart data
$chart_labels = [];
$chart_views = [];
$chart_visitors = [];
foreach ($daily_data as $day) {
    $chart_labels[] = date('d M', strtotime($day['stat_date']));
    $chart_views[] = (int) $day['total_views'];
    $chart_visitors[] = (int) $day['unique_visitors'];
}
?>

<div class="page-header">
    <h1 class="page-title">สถิติผู้เข้าชมเว็บไซต์</h1>
</div>

<!-- Filter -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">วันเริ่มต้น</label>
            <input type="date" name="start" value="<?php echo $start_date; ?>"
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">วันสิ้นสุด</label>
            <input type="date" name="end" value="<?php echo $end_date; ?>"
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <button type="submit" class="btn btn-primary" style="height: 38px;">
            <i class="fa-solid fa-filter"></i> กรอง
        </button>
        <a href="visitor_stats.php" class="btn"
            style="height: 38px; padding: 8px 15px; background: #f0f0f0; border-radius: 5px; text-decoration: none; color: #333;">
            รีเซ็ต
        </a>
    </form>
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
    <div class="card" style="text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; font-weight: 700; color: #002D62;">
            <?php echo number_format($summary['total_views']); ?>
        </div>
        <div style="color: #666; margin-top: 5px;">การเข้าชมทั้งหมด</div>
    </div>
    <div class="card" style="text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; font-weight: 700; color: #28a745;">
            <?php echo number_format($summary['unique_visitors']); ?>
        </div>
        <div style="color: #666; margin-top: 5px;">ผู้เข้าชมที่ไม่ซ้ำ</div>
    </div>
    <div class="card" style="text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; font-weight: 700; color: #ffc107;">
            <?php echo number_format($summary['today_views']); ?>
        </div>
        <div style="color: #666; margin-top: 5px;">เข้าชมวันนี้</div>
    </div>
    <div class="card" style="text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; font-weight: 700; color: #17a2b8;">
            <?php echo number_format($summary['today_visitors']); ?>
        </div>
        <div style="color: #666; margin-top: 5px;">ผู้เข้าชมวันนี้</div>
    </div>
</div>

<!-- Chart -->
<div class="card" style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px;">กราฟสถิติการเข้าชม</h3>
    <canvas id="visitorChart" height="100"></canvas>
</div>

<!-- Device & Province Stats -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
    <!-- Device Stats -->
    <div class="card">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-mobile-screen"
                style="margin-right: 8px; color: #17a2b8;"></i>สถิติตามอุปกรณ์</h3>
        <div style="display: flex; gap: 20px; align-items: center;">
            <div style="width: 180px; height: 180px;">
                <canvas id="deviceChart"></canvas>
            </div>
            <div style="flex: 1;">
                <?php
                $device_icons = ['desktop' => 'fa-desktop', 'mobile' => 'fa-mobile-screen', 'tablet' => 'fa-tablet-screen-button', 'unknown' => 'fa-question'];
                $device_names = ['desktop' => 'คอมพิวเตอร์', 'mobile' => 'มือถือ', 'tablet' => 'แท็บเล็ต', 'unknown' => 'ไม่ทราบ'];
                $device_colors = ['desktop' => '#002D62', 'mobile' => '#28a745', 'tablet' => '#ffc107', 'unknown' => '#6c757d'];
                ?>
                <?php if (count($device_stats) > 0): ?>
                    <?php foreach ($device_stats as $device): ?>
                        <div
                            style="display: flex; align-items: center; margin-bottom: 10px; padding: 8px; background: #f8f9fa; border-radius: 6px;">
                            <i class="fa-solid <?php echo $device_icons[$device['device_type']] ?? 'fa-question'; ?>"
                                style="font-size: 1.2rem; width: 30px; color: <?php echo $device_colors[$device['device_type']] ?? '#666'; ?>;"></i>
                            <span
                                style="flex: 1;"><?php echo $device_names[$device['device_type']] ?? $device['device_type']; ?></span>
                            <span style="font-weight: 600;"><?php echo number_format($device['views']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888; text-align: center;">ยังไม่มีข้อมูล</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Province Stats -->
    <div class="card">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-map-location-dot"
                style="margin-right: 8px; color: #28a745;"></i>สถิติตามจังหวัด (Top 10)</h3>
        <div style="max-height: 250px; overflow-y: auto;">
            <?php if (count($province_stats) > 0): ?>
                <?php
                $max_views = $province_stats[0]['views'];
                foreach ($province_stats as $index => $prov):
                    $percentage = ($prov['views'] / $max_views) * 100;
                    ?>
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span
                                style="font-size: 0.9rem;"><?php echo htmlspecialchars($province_names[$prov['province']] ?? $prov['province'] ?: 'ไม่ทราบ'); ?></span>
                            <span
                                style="font-weight: 600; font-size: 0.9rem;"><?php echo number_format($prov['views']); ?></span>
                        </div>
                        <div style="background: #e9ecef; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div
                                style="background: linear-gradient(90deg, #002D62, #1c4587); height: 100%; width: <?php echo $percentage; ?>%; border-radius: 4px;">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #888; text-align: center; padding: 20px;">ยังไม่มีข้อมูล</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Top Pages -->
    <div class="card">
        <h3 style="margin-bottom: 15px;">หน้าที่เข้าชมมากที่สุด</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 10px; text-align: left;">หน้า</th>
                    <th style="padding: 10px; text-align: right;">จำนวนเข้าชม</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($top_pages) > 0): ?>
                    <?php foreach ($top_pages as $page): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">
                                <span style="font-size: 0.85rem; color: #333;">
                                    <?php echo htmlspecialchars(getPageNameThai($page['page_path'], $page_names)); ?>
                                </span>
                                <div style="font-size: 0.7rem; color: #888; word-break: break-all;">
                                    <?php echo htmlspecialchars($page['page_path']); ?>
                                </div>
                            </td>
                            <td style="padding: 10px; text-align: right; font-weight: 600;">
                                <?php echo number_format($page['views']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="padding: 20px; text-align: center; color: #888;">ยังไม่มีข้อมูล</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Visitors -->
    <div class="card">
        <h3 style="margin-bottom: 15px;">ผู้เข้าชมล่าสุด</h3>
        <div style="max-height: 400px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="position: sticky; top: 0; background: white;">
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 10px; text-align: left;">เวลา</th>
                        <th style="padding: 10px; text-align: left;">หน้า</th>
                        <th style="padding: 10px; text-align: center;">อุปกรณ์</th>
                        <th style="padding: 10px; text-align: left;">จังหวัด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_visitors) > 0): ?>
                        <?php foreach ($recent_visitors as $visitor):
                            $dev_icon = $device_icons[$visitor['device_type']] ?? 'fa-question';
                            $dev_color = $device_colors[$visitor['device_type']] ?? '#666';
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px; font-size: 0.8rem; white-space: nowrap;">
                                    <?php echo date('d/m H:i', strtotime($visitor['visited_at'])); ?>
                                </td>
                                <td style="padding: 8px; font-size: 0.8rem; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                    title="<?php echo htmlspecialchars($visitor['page_url']); ?>">
                                    <?php echo htmlspecialchars(getPageNameThai($visitor['page_url'], $page_names)); ?>
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <i class="fa-solid <?php echo $dev_icon; ?>" style="color: <?php echo $dev_color; ?>;"></i>
                                </td>
                                <td style="padding: 8px; font-size: 0.8rem;">
                                    <?php echo htmlspecialchars($province_names[$visitor['province']] ?? $visitor['province'] ?: '-'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #888;">ยังไม่มีข้อมูล</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('visitorChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'การเข้าชม',
                data: <?php echo json_encode($chart_views); ?>,
                borderColor: '#002D62',
                backgroundColor: 'rgba(0, 45, 98, 0.1)',
                fill: true,
                tension: 0.3
            }, {
                label: 'ผู้เข้าชมไม่ซ้ำ',
                data: <?php echo json_encode($chart_visitors); ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Device Pie Chart
    <?php if (count($device_stats) > 0): ?>
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function ($d) use ($device_names) {
                    return $device_names[$d['device_type']] ?? $d['device_type'];
                }, $device_stats)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function ($d) {
                        return (int) $d['views'];
                    }, $device_stats)); ?>,
                    backgroundColor: <?php echo json_encode(array_map(function ($d) use ($device_colors) {
                        return $device_colors[$d['device_type']] ?? '#666';
                    }, $device_stats)); ?>,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '60%'
            }
        });
    <?php endif; ?>
</script>

<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: repeat(4"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>