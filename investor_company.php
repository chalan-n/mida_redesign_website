<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

// Track visitor
@include_once 'track_visitor.php';

// Fetch Settings
$settings = array();
try {
    $stmt_settings = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt_settings->fetch();
} catch (PDOException $e) {
}

$company_info = array(
    'company_name' => 'บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)',
    'head_office' => '48/1-5 ซอยแจ้งวัฒนะ 14 ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง เขตหลักสี่ กรุงเทพฯ 10210',
    'business_type' => 'ให้บริการสินเชื่อเช่าซื้อรถยนต์ โดยเน้นรถยนต์มือสองและรถรับจ้าง รวมถึงสินเชื่อหมุนเวียนสำหรับผู้ประกอบการรถยนต์มือสอง พร้อมบริการหลังการขาย เช่น ต่อทะเบียนรถยนต์ พ.ร.บ. คุ้มครองผู้ประสบภัยจากรถยนต์ ประกันภัย และการบริหารสินทรัพย์ด้อยคุณภาพ',
    'registration_no' => '0107547000532',
    'phone' => '0-2574-6901',
    'fax' => '0-2574-6902, 0-2574-6903',
    'website' => 'www.mida-leasing.com',
    'registered_capital' => '665,498,289.00 บาท',
    'paid_up_capital' => '532,398,631.50 บาท',
    'shareholder_date' => '21 มี.ค. 2565'
);

try {
    $stmt_company = $db->query("SELECT * FROM company_info WHERE id = 1");
    $db_company_info = $stmt_company ? $stmt_company->fetch(PDO::FETCH_ASSOC) : false;
    if ($db_company_info) {
        $company_info = array_merge($company_info, array_filter($db_company_info, function ($value) {
            return $value !== null && $value !== '';
        }));
    }
} catch (PDOException $e) {
}

$shareholders = array();
$shareholder_columns = array();
$shareholder_error = '';
function companyQuoteIdentifier($identifier)
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function companyQuoteTable($table)
{
    $parts = explode('.', $table);
    $quoted_parts = array();
    foreach ($parts as $part) {
        $quoted_parts[] = companyQuoteIdentifier($part);
    }
    return implode('.', $quoted_parts);
}

function companyTableColumns($db, $table)
{
    try {
        $stmt = $db->query("SHOW COLUMNS FROM " . companyQuoteTable($table));
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : array();
    } catch (PDOException $e) {
        return array();
    }
}

function companyPickColumn($columns, $candidates)
{
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $columns, true)) {
            return $candidate;
        }
    }
    return null;
}

function companyBuildShareholderQuery($columns)
{
    $status_column = companyPickColumn($columns, array('status', 'is_active', 'show_status', 'display_status'));
    $sort_column = companyPickColumn($columns, array('shareholder_order', 'sort_order', 'display_order', 'rank', 'no', 'sequence', 'ID', 'id'));
    $date_column = companyPickColumn($columns, array('as_of_date', 'record_date', 'shareholder_date', 'created_at', 'updated_at'));

    $where_sql = '';
    if ($status_column) {
        $where_sql = " WHERE (`" . $status_column . "` = 1 OR `" . $status_column . "` = '1' OR `" . $status_column . "` = 'active' OR `" . $status_column . "` = 'show' OR `" . $status_column . "` = 'แสดงผล')";
    }

    $order_parts = array();
    if ($date_column) {
        $order_parts[] = "`" . $date_column . "` DESC";
    }
    if ($sort_column) {
        $order_parts[] = "`" . $sort_column . "` ASC";
    }

    return $where_sql . (!empty($order_parts) ? " ORDER BY " . implode(", ", $order_parts) : "") . " LIMIT 10";
}

function companyValue($row, $column, $fallback = '-')
{
    if ($column && isset($row[$column]) && $row[$column] !== '') {
        return $row[$column];
    }
    return $fallback;
}

function companyFormatNumber($value)
{
    if ($value === '-' || $value === null || $value === '') {
        return '-';
    }

    $numeric = str_replace(array(',', ' '), '', (string) $value);
    if (!is_numeric($numeric)) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    return number_format((float) $numeric, 0);
}

function companyFormatPercent($value)
{
    if ($value === '-' || $value === null || $value === '') {
        return '-';
    }

    $numeric = str_replace(array('%', ',', ' '), '', (string) $value);
    if (!is_numeric($numeric)) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    return number_format((float) $numeric, 2) . '%';
}

try {
    $shareholder_columns = companyTableColumns($db, 'shareholders');

    if (!empty($shareholder_columns)) {
        $stmt_shareholders = $db->query("SELECT * FROM " . companyQuoteTable('shareholders') . companyBuildShareholderQuery($shareholder_columns));
    } else {
        // Some production users can SELECT data but cannot run SHOW COLUMNS.
        $stmt_shareholders = $db->query("SELECT * FROM shareholders LIMIT 10");
    }

    $shareholders = $stmt_shareholders ? $stmt_shareholders->fetchAll(PDO::FETCH_ASSOC) : array();

    if (empty($shareholder_columns) && !empty($shareholders[0])) {
        $shareholder_columns = array_keys($shareholders[0]);
    }
} catch (PDOException $e) {
    $shareholders = array();
    $shareholder_error = $e->getMessage();
}

$shareholder_rank_column = companyPickColumn($shareholder_columns, array('shareholder_order', 'rank', 'no', 'sort_order', 'display_order', 'sequence', 'ลำดับ', 'ID', 'id'));
$shareholder_name_column = companyPickColumn($shareholder_columns, array('shareholder_name', 'shareholder', 'holder_name', 'name', 'name_th', 'full_name', 'title', 'ชื่อ-นามสกุล', 'ชื่อผู้ถือหุ้น'));
$shareholder_shares_column = companyPickColumn($shareholder_columns, array('shareholder_price', 'shares', 'share_amount', 'number_of_shares', 'amount', 'qty', 'quantity', 'จำนวนหุ้น'));
$shareholder_percent_column = companyPickColumn($shareholder_columns, array('shareholder_percent', 'percentage', 'percent', 'share_percent', 'ratio', 'holding_percent', 'ร้อยละ'));
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลบริษัท - MIDA LEASING</title>
    <meta name="description" content="ข้อมูลบริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน) พร้อมข้อมูลธุรกิจ ทุนจดทะเบียน และรายชื่อผู้ถือหุ้นรายใหญ่ 10 รายแรก">

    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($settings['site_favicon']); ?>" type="image/x-icon">
    <?php else: ?>
        <link rel="icon" href="favicon.ico" type="image/x-icon">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .company-page {
            background: #f6f8fb;
            color: #183153;
        }

        .company-hero {
            position: relative;
            overflow: hidden;
            padding: 132px 0 72px;
            background:
                radial-gradient(circle at 15% 18%, rgba(255, 199, 50, 0.24), transparent 28%),
                linear-gradient(108deg, #f9fbff 0%, #eef5ff 62%, #1f5fb8 62%, #17488f 100%);
        }

        .company-hero::after {
            content: "";
            position: absolute;
            inset: auto -8% -42% 44%;
            height: 58%;
            background: rgba(255, 255, 255, 0.12);
            transform: skewX(-12deg);
            pointer-events: none;
        }

        .company-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(340px, 0.78fr);
            gap: 42px;
            align-items: center;
        }

        .company-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: var(--primary-blue);
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .company-kicker::before {
            content: "";
            width: 34px;
            height: 3px;
            border-radius: 999px;
            background: var(--accent-gold);
        }

        .company-hero h1 {
            max-width: 620px;
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: clamp(2.25rem, 5vw, 4.4rem);
            line-height: 1;
            letter-spacing: -0.05em;
        }

        .company-hero p {
            max-width: 560px;
            margin: 0;
            color: #56677d;
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .company-hero-panel {
            position: relative;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 24px 58px rgba(13, 55, 116, 0.16);
            backdrop-filter: blur(12px);
        }

        .company-hero-panel h2 {
            margin: 0 0 16px;
            color: var(--primary-blue);
            font-size: 1.35rem;
        }

        .company-highlight-list {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .company-highlight-list li {
            display: flex;
            gap: 12px;
            color: #293b54;
            font-weight: 700;
            line-height: 1.55;
        }

        .company-highlight-list i {
            margin-top: 4px;
            color: var(--accent-gold);
        }

        .company-section {
            padding: 72px 0;
        }

        .company-section-title {
            max-width: 720px;
            margin: 0 0 28px;
        }

        .company-section-title span {
            display: inline-block;
            margin-bottom: 10px;
            color: var(--primary-blue);
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .company-section-title h2 {
            margin: 0 0 12px;
            color: var(--primary-blue);
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            line-height: 1.18;
        }

        .company-section-title p {
            margin: 0;
            color: #64748b;
            line-height: 1.8;
        }

        .company-info-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
            gap: 26px;
            align-items: start;
        }

        .company-info-card,
        .company-shareholder-card {
            border: 1px solid rgba(23, 69, 143, 0.1);
            border-radius: 28px;
            background: #fff;
            box-shadow: 0 18px 44px rgba(23, 69, 143, 0.08);
            overflow: hidden;
        }

        .company-info-card-header {
            padding: 20px 26px;
            background: linear-gradient(135deg, var(--primary-blue), #1f5fb8);
            color: #fff;
            font-weight: 800;
            text-align: center;
        }

        .company-info-list {
            display: grid;
            gap: 0;
            padding: 8px 26px 26px;
        }

        .company-info-row {
            display: grid;
            grid-template-columns: minmax(140px, 0.42fr) minmax(0, 1fr);
            gap: 18px;
            padding: 18px 0;
            border-bottom: 1px solid #edf1f7;
        }

        .company-info-row:last-child {
            border-bottom: 0;
        }

        .company-info-label {
            color: #52647a;
            font-weight: 700;
        }

        .company-info-value {
            color: var(--primary-blue);
            font-weight: 700;
            line-height: 1.75;
        }

        .company-info-value a {
            color: var(--primary-blue);
            text-decoration: none;
        }

        .company-business-copy {
            margin: 0;
            color: var(--primary-blue);
            font-weight: 600;
            line-height: 2;
        }

        .company-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 22px;
        }

        .company-stat {
            padding: 22px;
            border: 1px solid rgba(23, 69, 143, 0.08);
            border-radius: 22px;
            background: linear-gradient(135deg, #ffffff, #f7fbff);
        }

        .company-stat strong {
            display: block;
            margin-bottom: 6px;
            color: var(--primary-blue);
            font-size: 1.28rem;
        }

        .company-stat span {
            color: #64748b;
            font-weight: 600;
        }

        .company-shareholder-card {
            padding: 26px;
        }

        .company-shareholder-head {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-end;
            margin-bottom: 18px;
        }

        .company-shareholder-head h2 {
            margin: 0;
            color: var(--primary-blue);
            font-size: clamp(1.5rem, 2.6vw, 2.1rem);
        }

        .company-shareholder-date {
            color: #64748b;
            font-weight: 700;
            white-space: nowrap;
        }

        .company-shareholder-table-wrap {
            overflow-x: auto;
            border: 1px solid #e5edf7;
            border-radius: 20px;
        }

        .company-shareholder-table {
            width: 100%;
            min-width: 640px;
            border-collapse: collapse;
            background: #fff;
        }

        .company-shareholder-table th {
            padding: 15px 16px;
            background: var(--primary-blue);
            color: #fff;
            font-weight: 800;
            text-align: left;
        }

        .company-shareholder-table th:nth-child(1),
        .company-shareholder-table td:nth-child(1) {
            width: 76px;
            text-align: center;
        }

        .company-shareholder-table th:nth-child(3),
        .company-shareholder-table td:nth-child(3),
        .company-shareholder-table th:nth-child(4),
        .company-shareholder-table td:nth-child(4) {
            text-align: right;
        }

        .company-shareholder-table td {
            padding: 16px;
            border-bottom: 1px solid #edf1f7;
            color: #26384f;
            font-weight: 600;
        }

        .company-shareholder-table tr:last-child td {
            border-bottom: 0;
        }

        .company-shareholder-table tr:nth-child(even) td {
            background: #f8fbff;
        }

        .company-empty {
            padding: 34px 20px;
            text-align: center;
            color: #64748b;
            background: #f8fbff;
        }

        .company-empty i {
            display: block;
            margin-bottom: 12px;
            color: var(--accent-gold);
            font-size: 2rem;
        }

        @media (max-width: 992px) {
            .company-hero {
                padding: 118px 0 56px;
                background:
                    radial-gradient(circle at 18% 10%, rgba(255, 199, 50, 0.24), transparent 32%),
                    linear-gradient(180deg, #f9fbff 0%, #eef5ff 100%);
            }

            .company-hero-grid,
            .company-info-grid {
                grid-template-columns: 1fr;
            }

            .company-hero-panel {
                max-width: 620px;
            }
        }

        @media (max-width: 640px) {
            .company-hero {
                padding: 104px 0 42px;
            }

            .company-section {
                padding: 52px 0;
            }

            .company-hero-panel,
            .company-info-card,
            .company-shareholder-card {
                border-radius: 22px;
            }

            .company-info-row,
            .company-shareholder-head {
                grid-template-columns: 1fr;
                display: grid;
                gap: 6px;
            }

            .company-stat-grid {
                grid-template-columns: 1fr;
            }

            .company-shareholder-date {
                white-space: normal;
            }
        }
    </style>
</head>

<body class="company-page">
    <?php $active_page = 'investor'; include 'includes/nav.php'; ?>

    <main>
        <section class="company-hero">
            <div class="container">
                <div class="company-hero-grid">
                    <div>
                        <span class="company-kicker">MIDA LEASING COMPANY PROFILE</span>
                        <h1>ข้อมูลบริษัท</h1>
                        <p>
                            ภาพรวมของ<?php echo htmlspecialchars($company_info['company_name'], ENT_QUOTES, 'UTF-8'); ?> สำหรับผู้ถือหุ้น นักลงทุน
                            และผู้สนใจที่ต้องการทำความเข้าใจธุรกิจของบริษัทอย่างรวดเร็ว
                        </p>
                    </div>
                    <aside class="company-hero-panel">
                        <h2>สรุปข้อมูลสำคัญ</h2>
                        <ul class="company-highlight-list">
                            <li><i class="fa-solid fa-circle-check"></i><span>สรุปข้อมูลธุรกิจและข้อมูลจดทะเบียนของบริษัท</span></li>
                            <li><i class="fa-solid fa-circle-check"></i><span>แสดงทุนจดทะเบียนและทุนชำระแล้วสำหรับนักลงทุน</span></li>
                            <li><i class="fa-solid fa-circle-check"></i><span>แสดงรายชื่อผู้ถือหุ้นรายใหญ่เพื่อประกอบการติดตามข้อมูลบริษัท</span></li>
                        </ul>
                    </aside>
                </div>
            </div>
        </section>

        <section class="company-section">
            <div class="container">
                <div class="company-info-grid">
                    <article class="company-info-card">
                        <div class="company-info-card-header">ข้อมูลบริษัท</div>
                        <div class="company-info-list">
                            <div class="company-info-row">
                                <div class="company-info-label">ชื่อบริษัท</div>
                                <div class="company-info-value"><?php echo htmlspecialchars($company_info['company_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="company-info-row">
                                <div class="company-info-label">สำนักงานใหญ่</div>
                                <div class="company-info-value"><?php echo nl2br(htmlspecialchars($company_info['head_office'], ENT_QUOTES, 'UTF-8')); ?></div>
                            </div>
                            <div class="company-info-row">
                                <div class="company-info-label">ประเภทธุรกิจ</div>
                                <div class="company-info-value">
                                    <p class="company-business-copy">
                                        <?php echo nl2br(htmlspecialchars($company_info['business_type'], ENT_QUOTES, 'UTF-8')); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="company-info-row">
                                <div class="company-info-label">เลขทะเบียนบริษัท</div>
                                <div class="company-info-value"><?php echo htmlspecialchars($company_info['registration_no'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="company-info-row">
                                <div class="company-info-label">โทรศัพท์</div>
                                <div class="company-info-value"><a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $company_info['phone']); ?>"><?php echo htmlspecialchars($company_info['phone'], ENT_QUOTES, 'UTF-8'); ?></a></div>
                            </div>
                            <div class="company-info-row">
                                <div class="company-info-label">โทรสาร</div>
                                <div class="company-info-value"><?php echo htmlspecialchars($company_info['fax'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="company-info-row">
                                <div class="company-info-label">เว็บไซต์</div>
                                <div class="company-info-value"><a href="<?php echo htmlspecialchars((stripos($company_info['website'], 'http') === 0 ? $company_info['website'] : 'https://' . $company_info['website']), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($company_info['website'], ENT_QUOTES, 'UTF-8'); ?></a></div>
                            </div>
                        </div>
                    </article>

                    <div>
                        <div class="company-section-title">
                            <span>Registered Capital</span>
                            <h2>ทุนจดทะเบียนและทุนชำระแล้ว</h2>
                            <p>ข้อมูลทุนของบริษัทที่ใช้ประกอบการพิจารณาภาพรวมกิจการและฐานทุนของบริษัท</p>
                        </div>
                        <div class="company-stat-grid">
                            <div class="company-stat">
                                <strong><?php echo htmlspecialchars($company_info['registered_capital'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span>ทุนจดทะเบียน</span>
                            </div>
                            <div class="company-stat">
                                <strong><?php echo htmlspecialchars($company_info['paid_up_capital'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span>ทุนจดทะเบียนชำระแล้ว</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="company-section" style="padding-top: 0;">
            <div class="container">
                <article class="company-shareholder-card">
                    <div class="company-shareholder-head">
                        <h2>รายชื่อผู้ถือหุ้นรายใหญ่ 10 รายแรก</h2>
                        <div class="company-shareholder-date">ณ วันที่ <?php echo htmlspecialchars($company_info['shareholder_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>

                    <div class="company-shareholder-table-wrap">
                        <table class="company-shareholder-table">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>จำนวนหุ้น</th>
                                    <th>ร้อยละ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($shareholders)): ?>
                                    <?php foreach ($shareholders as $index => $shareholder): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(companyValue($shareholder, $shareholder_rank_column, $index + 1), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(companyValue($shareholder, $shareholder_name_column), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo companyFormatNumber(companyValue($shareholder, $shareholder_shares_column)); ?></td>
                                            <td><?php echo companyFormatPercent(companyValue($shareholder, $shareholder_percent_column)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="company-empty">
                                                <i class="fa-regular fa-folder-open"></i>
                                                <?php if (!empty($shareholder_error)): ?>
                                                    ไม่พบข้อมูลจากตาราง shareholders สำหรับแสดงผล
                                                <?php else: ?>
                                                    ยังไม่มีข้อมูลผู้ถือหุ้นสำหรับแสดงผล
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">MIDA LEASING</div>
                    <p style="color: #ccc; margin-bottom: 10px;">บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</p>
                    <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">48/1-5 ซอยแจ้งวัฒนะ 14 ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง เขตหลักสี่ กรุงเทพฯ 10210</p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone" style="margin-right: 10px;"></i>02-574-6901</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="https://www.facebook.com/midaleasing.th" target="_blank" style="text-decoration: none;">
                            <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                        </a>
                        <a href="https://line.me/R/ti/p/@midaleasing" target="_blank" style="text-decoration: none;">
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
                        <li><a href="investor_company.php">ข้อมูลบริษัท</a></li>
                        <li><a href="investor_financial.php">ข้อมูลทางการเงิน</a></li>
                        <li><a href="investor_publications.php">เอกสารเผยแพร่</a></li>
                            <li><a href="investor_contact.php">ติดต่อนักลงทุนสัมพันธ์</a></li>
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
                    <a href="privacy_policy.php" style="color: #888; text-decoration: none; margin: 0 10px;">นโยบายความเป็นส่วนตัว</a> |
                    <a href="cookie_policy.php" style="color: #888; text-decoration: none; margin: 0 10px;">นโยบายเกี่ยวกับ cookie</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>
