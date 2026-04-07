<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Helper Function: Format Thai Date Short
function formatThaiDate($datetime)
{
    if (empty($datetime))
        return '-';
    $timestamp = strtotime($datetime);
    $months = [
        1 => 'ม.ค.',
        2 => 'ก.พ.',
        3 => 'มี.ค.',
        4 => 'เม.ย.',
        5 => 'พ.ค.',
        6 => 'มิ.ย.',
        7 => 'ก.ค.',
        8 => 'ส.ค.',
        9 => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.'
    ];

    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('y', $timestamp) + 43; // Short year + 43 (e.g. 2569 -> 69)
    $time = date('H:i', $timestamp);

    return "$day $month $year $time" . "น.";
}

// Seed Data Logic
if (isset($_POST['seed_data'])) {
    try {
        $stmt_check = $db->query("SELECT id FROM properties LIMIT 5");
        $props = $stmt_check->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($props)) {
            $samples = array(
                array('name' => 'คุณสมชาย ใจดี', 'phone' => '081-234-5678', 'line' => 'somchai.j', 'msg' => 'สนใจบ้านเดี่ยวหลังมุมครับ ขอรายละเอียดเพิ่มเติม'),
                array('name' => 'คุณวิภาวี รักสงบ', 'phone' => '089-987-6543', 'line' => 'wipa_love', 'msg' => 'คอนโดนี้เลี้ยงสัตว์ได้ไหมคะ?'),
                array('name' => 'คุณเอกพล คนขยัน', 'phone' => '090-111-2222', 'line' => 'ekkapet', 'msg' => 'นัดดูที่ดินวันเสาร์นี้ได้ไหมครับ'),
                array('name' => 'คุณนารี มีทรัพย์', 'phone' => '086-555-4444', 'line' => 'naree.m', 'msg' => 'ขอตารางผ่อนชำระเบื้องต้นค่ะ'),
                array('name' => 'คุณประดิษฐ์ คิดเลิศ', 'phone' => '092-333-8888', 'line' => 'pradit.k', 'msg' => 'สนใจทรัพย์นี้ครับ ติดต่อกลับด้วย')
            );

            $sql_insert = "INSERT INTO property_leads (property_id, name, phone, line_id, message, created_at) VALUES (:pid, :name, :phone, :lid, :msg, DATE_SUB(NOW(), INTERVAL :hr HOUR))";
            $stmt_insert = $db->prepare($sql_insert);

            foreach ($samples as $i => $s) {
                $rand_key = array_rand($props);
                $pid = $props[$rand_key];
                $stmt_insert->execute(array(
                    ':pid' => $pid,
                    ':name' => $s['name'],
                    ':phone' => $s['phone'],
                    ':lid' => $s['line'],
                    ':msg' => $s['msg'],
                    ':hr' => $i * 5
                ));
            }
            $success_msg = "เพิ่มข้อมูลตัวอย่างเรียบร้อยแล้ว (5 รายการ)";
        }
    } catch (PDOException $e) {
        $error_msg = $e->getMessage();
    }
}

// Fetch Leads
$sql = "SELECT l.*, p.title as property_title, p.property_code 
        FROM property_leads l 
        LEFT JOIN properties p ON l.property_id = p.id 
        ORDER BY l.created_at DESC";
$stmt = $db->query($sql);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1 class="page-title"><i class="fa-solid fa-address-book"></i> ผู้สนใจทรัพย์สิน (Leads)</h1>
    <?php if (count($leads) == 0): ?>
        <form method="POST" style="margin: 0;">
            <button type="submit" name="seed_data" class="btn btn-outline-secondary btn-sm"
                onclick="return confirm('ยืนยันการเพิ่มข้อมูลตัวอย่าง?');">
                <i class="fa-solid fa-database"></i> เพิ่มข้อมูลตัวอย่าง
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (isset($success_msg)): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">วันที่</th>
                        <th style="width: 25%;">ทรัพย์สิน</th>
                        <th style="width: 20%;">ชื่อลูกค้า</th>
                        <th style="width: 15%;">เบอร์โทร</th>
                        <th style="width: 10%;">Line ID</th>
                        <th style="width: 10%;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($leads) > 0): ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <?php echo $lead['id']; ?>
                                </td>
                                <td>
                                    <?php echo formatThaiDate($lead['created_at']); ?>
                                </td>
                                <td>
                                    <?php if ($lead['property_code']): ?>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($lead['property_code']); ?>
                                        </span><br>
                                    <?php endif; ?>
                                    <a href="../property_detail.php?id=<?php echo $lead['property_id']; ?>" target="_blank"
                                        style="text-decoration: none;">
                                        <?php echo htmlspecialchars($lead['property_title']); ?> <i
                                            class="fa-solid fa-external-link-alt" style="font-size: 0.8em;"></i>
                                    </a>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($lead['name']); ?>
                                    <?php if ($lead['message']): ?>
                                        <br><small class="text-muted"><i class="fa-solid fa-comment"></i>
                                            <?php echo htmlspecialchars($lead['message']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><a href="tel:<?php echo htmlspecialchars($lead['phone']); ?>">
                                        <?php echo htmlspecialchars($lead['phone']); ?>
                                    </a></td>
                                <td>
                                    <?php echo htmlspecialchars($lead['line_id']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">ใหม่</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">ยังไม่มีข้อมูลผู้สนใจ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>