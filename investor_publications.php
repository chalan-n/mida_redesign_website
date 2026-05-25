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

$invitations = array();
$minutes = array();
$warrants = array();

try {
    $stmt = $db->query("SELECT * FROM publications ORDER BY id DESC");
    $publications = $stmt->fetchAll();

    foreach ($publications as $pub) {
        $cat = $pub['category'];
        if ($cat == 'หนังสือเชิญประชุมสามัญผู้ถือหุ้น') {
            $invitations[] = $pub;
        } elseif ($cat == 'รายงานการประชุมสามัญผู้ถือหุ้นประจำปี') {
            $minutes[] = $pub;
        } elseif ($cat == 'ข้อมูลใบสำคัญแสดงสิทธิ') {
            $warrants[] = $pub;
        } else {
            // Mapping old categories to new ones or default
            if ($cat == 'News')
                $invitations[] = $pub; // Fallback
            else
                $minutes[] = $pub; // Fallback
        }
    }
} catch (PDOException $e) {
    // Handle error
}

function renderPublicationCards($items, $emptyText, $iconClass, $label)
{
    if (count($items) > 0) {
        foreach ($items as $pub) {
            $href = '';
            $actionText = '';
            $actionIcon = '';
            if (!empty($pub['link_url'])) {
                $href = $pub['link_url'];
                $actionText = 'ดูรายละเอียด';
                $actionIcon = 'fa-solid fa-arrow-up-right-from-square';
            } elseif (!empty($pub['file_path'])) {
                $href = $pub['file_path'];
                $actionText = 'ดาวน์โหลดเอกสาร';
                $actionIcon = 'fa-solid fa-download';
            }
            ?>
            <article class="ir-publication-card">
                <div class="ir-publication-icon">
                    <i class="<?php echo $iconClass; ?>"></i>
                </div>
                <div class="ir-publication-content">
                    <span class="ir-publication-tag"><?php echo $label; ?></span>
                    <h3><?php echo htmlspecialchars($pub['title']); ?></h3>
                    <?php if (!empty($pub['publish_date'])): ?>
                        <p class="ir-publication-date">
                            <i class="fa-regular fa-calendar"></i>
                            <?php echo htmlspecialchars($pub['publish_date']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($href)): ?>
                        <a href="<?php echo htmlspecialchars($href); ?>" target="_blank" class="ir-publication-link">
                            <?php echo $actionText; ?>
                            <i class="<?php echo $actionIcon; ?>"></i>
                        </a>
                    <?php else: ?>
                        <span class="ir-publication-muted">ยังไม่มีไฟล์เอกสาร</span>
                    <?php endif; ?>
                </div>
            </article>
            <?php
        }
    } else {
        ?>
        <div class="ir-publication-empty">
            <i class="fa-regular fa-folder-open"></i>
            <p><?php echo $emptyText; ?></p>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เอกสารเผยแพร่สำหรับผู้ถือหุ้นและนักลงทุน - MIDA LEASING</title>
    <meta name="description" content="เอกสารเผยแพร่สำหรับผู้ถือหุ้น หนังสือเชิญประชุม รายงานการประชุม และข้อมูลใบสำคัญแสดงสิทธิของบริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)">

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

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .investor-publications-page {
            background: #f6f8fb;
            color: #183153;
        }

        .ir-publications-hero {
            position: relative;
            overflow: hidden;
            padding: 132px 0 72px;
            background:
                radial-gradient(circle at 16% 20%, rgba(255, 199, 50, 0.24), transparent 28%),
                linear-gradient(108deg, #f9fbff 0%, #eef5ff 62%, #1f5fb8 62%, #17488f 100%);
        }

        .ir-publications-hero::after {
            content: "";
            position: absolute;
            inset: auto -8% -42% 44%;
            height: 58%;
            background: rgba(255, 255, 255, 0.12);
            transform: skewX(-12deg);
            pointer-events: none;
        }

        .ir-publications-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(360px, 0.78fr);
            gap: 42px;
            align-items: center;
        }

        .ir-publications-grid > div:first-child {
            max-width: 560px;
        }

        .ir-kicker {
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

        .ir-kicker::before {
            content: "";
            width: 34px;
            height: 3px;
            border-radius: 999px;
            background: var(--accent-gold);
        }

        .ir-publications-hero h1 {
            max-width: 560px;
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: clamp(2.25rem, 5vw, 4.4rem);
            line-height: 0.98;
            letter-spacing: -0.05em;
        }

        .ir-publications-hero p {
            max-width: 520px;
            margin: 0;
            color: #56677d;
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .ir-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 30px;
        }

        .ir-primary-btn,
        .ir-secondary-btn,
        .ir-publication-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            border-radius: 999px;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .ir-primary-btn {
            padding: 0 24px;
            background: linear-gradient(135deg, #ffc830, #ffd966);
            color: #0f356d;
            box-shadow: 0 14px 30px rgba(255, 199, 50, 0.28);
        }

        .ir-secondary-btn {
            padding: 0 22px;
            background: #fff;
            color: var(--primary-blue);
            border: 1px solid rgba(24, 49, 83, 0.12);
            box-shadow: 0 12px 26px rgba(21, 63, 117, 0.08);
        }

        .ir-primary-btn:hover,
        .ir-secondary-btn:hover,
        .ir-publication-link:hover {
            transform: translateY(-2px);
        }

        .ir-publications-panel {
            position: relative;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 28px 70px rgba(16, 48, 95, 0.24);
            backdrop-filter: blur(14px);
        }

        .ir-publications-panel h2 {
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: 1.45rem;
        }

        .ir-panel-list {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .ir-panel-list li {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 14px;
            align-items: start;
            color: #253b58;
            line-height: 1.55;
        }

        .ir-panel-list i {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #fff4cf;
            color: #b98600;
        }

        .ir-publications-main {
            padding: 64px 0 78px;
            background: linear-gradient(180deg, #f6f8fb 0%, #fff 52%, #f8fbff 100%);
        }

        .ir-publications-intro {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(320px, 1.05fr);
            gap: 34px;
            align-items: end;
            margin-bottom: 36px;
        }

        .section-eyebrow {
            display: inline-flex;
            margin-bottom: 10px;
            color: #bc8600;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .ir-publications-intro h2 {
            margin: 0 0 12px;
            color: var(--primary-blue);
            font-size: clamp(1.8rem, 3vw, 2.7rem);
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .ir-publications-intro p {
            margin: 0;
            color: #617187;
            line-height: 1.75;
        }

        .ir-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .ir-summary {
            padding: 20px;
            border: 1px solid #dfe8f3;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 16px 36px rgba(21, 63, 117, 0.07);
        }

        .ir-summary strong {
            display: block;
            color: var(--primary-blue);
            font-size: 2rem;
            line-height: 1;
        }

        .ir-summary span {
            display: block;
            margin-top: 8px;
            color: #5f6f83;
            font-weight: 700;
        }

        .ir-publication-section {
            margin-top: 24px;
            border: 1px solid #dfe8f3;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 20px 52px rgba(21, 63, 117, 0.08);
            overflow: hidden;
        }

        .ir-publication-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 28px;
            background: linear-gradient(135deg, #ffffff 0%, #f0f6ff 100%);
            border-bottom: 1px solid #dfe8f3;
        }

        .ir-publication-heading h2 {
            margin: 0;
            color: var(--primary-blue);
            font-size: 1.35rem;
        }

        .ir-publication-heading p {
            margin: 6px 0 0;
            color: #68788e;
        }

        .ir-publication-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            padding: 10px 16px;
            border-radius: 999px;
            background: #fff4cf;
            color: #8a6400;
            font-weight: 800;
            white-space: nowrap;
        }

        .ir-publication-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            padding: 24px;
        }

        .ir-publication-card {
            display: grid;
            gap: 16px;
            padding: 22px;
            border: 1px solid #e4edf7;
            border-radius: 24px;
            background: #fff;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .ir-publication-card:hover {
            transform: translateY(-4px);
            border-color: rgba(25, 76, 150, 0.2);
            box-shadow: 0 18px 36px rgba(21, 63, 117, 0.1);
        }

        .ir-publication-icon {
            display: inline-flex;
            width: 54px;
            height: 54px;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: #fff4cf;
            color: #b98600;
            font-size: 1.35rem;
        }

        .ir-publication-tag {
            display: inline-flex;
            width: fit-content;
            margin-bottom: 10px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #edf5ff;
            color: var(--primary-blue);
            font-size: 0.82rem;
            font-weight: 800;
        }

        .ir-publication-content h3 {
            margin: 0 0 10px;
            color: #183153;
            font-size: 1.06rem;
            line-height: 1.55;
        }

        .ir-publication-date {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 16px;
            color: #718096;
            font-size: 0.92rem;
        }

        .ir-publication-link {
            width: fit-content;
            min-height: 42px;
            padding: 0 17px;
            background: #f3f7ff;
            color: var(--primary-blue);
            font-size: 0.95rem;
        }

        .ir-publication-link:hover {
            background: var(--primary-blue);
            color: #fff;
            box-shadow: 0 14px 28px rgba(25, 76, 150, 0.18);
        }

        .ir-publication-muted,
        .ir-publication-empty {
            color: #718096;
        }

        .ir-publication-empty {
            grid-column: 1 / -1;
            display: grid;
            place-items: center;
            gap: 10px;
            padding: 34px 20px;
            text-align: center;
        }

        .ir-publication-empty i {
            color: #a9b7c8;
            font-size: 2rem;
        }

        .ir-publications-cta {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: center;
            margin-top: 36px;
            padding: 28px;
            border-radius: 28px;
            background: linear-gradient(135deg, #183f7c, #2668bf);
            color: #fff;
            box-shadow: 0 22px 48px rgba(22, 73, 146, 0.2);
        }

        .ir-publications-cta h2 {
            margin: 0 0 8px;
            color: #fff;
            font-size: 1.55rem;
        }

        .ir-publications-cta p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.65;
        }

        @media (max-width: 992px) {
            .ir-publications-hero {
                padding: 110px 0 56px;
                background:
                    radial-gradient(circle at 18% 12%, rgba(255, 199, 50, 0.26), transparent 34%),
                    linear-gradient(160deg, #f9fbff 0%, #eef5ff 62%, #dfefff 100%);
            }

            .ir-publications-grid,
            .ir-publications-intro,
            .ir-publications-cta {
                grid-template-columns: 1fr;
            }

            .ir-publications-panel {
                box-shadow: 0 20px 46px rgba(16, 48, 95, 0.14);
            }

            .ir-publication-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .ir-publications-hero {
                padding: 96px 0 42px;
            }

            .ir-publications-hero h1 {
                font-size: 2.55rem;
            }

            .ir-summary-grid,
            .ir-publication-grid {
                grid-template-columns: 1fr;
            }

            .ir-publication-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .ir-publication-link {
                width: 100%;
            }

            .ir-publications-cta {
                padding: 24px;
            }
        }
    </style>
</head>

<body class="investor-publications-page">

    <!-- Header -->
    <?php include 'includes/nav.php'; ?>

    <!-- Page Header -->
    <section class="ir-publications-hero">
        <div class="container">
            <div class="ir-publications-grid">
                <div>
                    <span class="ir-kicker">MIDA LEASING INVESTOR RELATIONS</span>
                    <h1>เอกสารเผยแพร่</h1>
                    <p>
                        แหล่งรวมเอกสารสำคัญสำหรับผู้ถือหุ้นและนักลงทุน ครอบคลุมหนังสือเชิญประชุม รายงานการประชุม และข้อมูลสิทธิที่เกี่ยวข้องกับการลงทุนในบริษัท
                    </p>
                    <div class="ir-hero-actions">
                        <a href="#publication-documents" class="ir-primary-btn">
                            <i class="fa-solid fa-file-lines"></i>
                            ดูเอกสารเผยแพร่
                        </a>
                        <a href="investor_financial.php" class="ir-secondary-btn">
                            ข้อมูลทางการเงิน
                        </a>
                    </div>
                </div>

                <aside class="ir-publications-panel">
                    <h2>เอกสารสำหรับผู้ถือหุ้น</h2>
                    <ul class="ir-panel-list">
                        <li>
                            <i class="fa-solid fa-envelope-open-text"></i>
                            <span><strong>หนังสือเชิญประชุม</strong><br>ข้อมูลวาระการประชุมและรายละเอียดประกอบการเข้าร่วมประชุมผู้ถือหุ้น</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-file-signature"></i>
                            <span><strong>รายงานการประชุม</strong><br>สรุปมติและสาระสำคัญจากการประชุมสามัญผู้ถือหุ้นประจำปี</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-certificate"></i>
                            <span><strong>ข้อมูลสิทธิ</strong><br>เอกสารเกี่ยวกับใบสำคัญแสดงสิทธิและข้อมูลที่เกี่ยวข้องกับผู้ถือหุ้น</span>
                        </li>
                    </ul>
                </aside>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main id="publication-documents" class="ir-publications-main">
        <div class="container">
            <div class="ir-publications-intro">
                <div>
                    <span class="section-eyebrow">Shareholder Documents</span>
                    <h2>เอกสารสำคัญเพื่อความโปร่งใสของข้อมูล</h2>
                    <p>
                        บริษัทเผยแพร่เอกสารที่เกี่ยวข้องกับการประชุมผู้ถือหุ้นและสิทธิของผู้ลงทุน เพื่อให้ผู้ถือหุ้นสามารถติดตามข้อมูลและพิจารณาประเด็นสำคัญได้อย่างครบถ้วน
                    </p>
                </div>

                <div class="ir-summary-grid">
                    <div class="ir-summary">
                        <strong><?php echo count($invitations); ?></strong>
                        <span>หนังสือเชิญประชุม</span>
                    </div>
                    <div class="ir-summary">
                        <strong><?php echo count($minutes); ?></strong>
                        <span>รายงานการประชุม</span>
                    </div>
                    <div class="ir-summary">
                        <strong><?php echo count($warrants); ?></strong>
                        <span>ข้อมูลสิทธิ</span>
                    </div>
                </div>
            </div>

            <section class="ir-publication-section">
                <div class="ir-publication-heading">
                    <div>
                        <h2>หนังสือเชิญประชุมสามัญผู้ถือหุ้น</h2>
                        <p>เอกสารประกอบการประชุม วาระการประชุม และรายละเอียดสำหรับผู้ถือหุ้น</p>
                    </div>
                    <span class="ir-publication-count"><?php echo count($invitations); ?> รายการ</span>
                </div>
                <div class="ir-publication-grid">
                    <?php renderPublicationCards($invitations, 'ไม่พบหนังสือเชิญประชุมในขณะนี้', 'fa-solid fa-envelope-open-text', 'หนังสือเชิญประชุม'); ?>
                </div>
            </section>

            <section class="ir-publication-section">
                <div class="ir-publication-heading">
                    <div>
                        <h2>รายงานการประชุมสามัญผู้ถือหุ้นประจำปี</h2>
                        <p>ข้อมูลสรุปมติและสาระสำคัญจากการประชุมผู้ถือหุ้นของบริษัท</p>
                    </div>
                    <span class="ir-publication-count"><?php echo count($minutes); ?> รายการ</span>
                </div>
                <div class="ir-publication-grid">
                    <?php renderPublicationCards($minutes, 'ไม่พบรายงานการประชุมในขณะนี้', 'fa-solid fa-file-signature', 'รายงานการประชุม'); ?>
                </div>
            </section>

            <section class="ir-publication-section">
                <div class="ir-publication-heading">
                    <div>
                        <h2>ข้อมูลใบสำคัญแสดงสิทธิ</h2>
                        <p>เอกสารเกี่ยวกับสิทธิของผู้ถือหุ้นและข้อมูลประกอบที่เกี่ยวข้อง</p>
                    </div>
                    <span class="ir-publication-count"><?php echo count($warrants); ?> รายการ</span>
                </div>
                <div class="ir-publication-grid">
                    <?php renderPublicationCards($warrants, 'ไม่พบข้อมูลใบสำคัญแสดงสิทธิในขณะนี้', 'fa-solid fa-certificate', 'ข้อมูลสิทธิ'); ?>
                </div>
            </section>

            <section class="ir-publications-cta">
                <div>
                    <h2>ต้องการดูผลการดำเนินงานประกอบ?</h2>
                    <p>ผู้ลงทุนสามารถดูข้อมูลทางการเงินและภาพรวมธุรกิจเพิ่มเติม เพื่อประกอบความเข้าใจเกี่ยวกับการดำเนินงานของไมด้าลิสซิ่ง</p>
                </div>
                <div class="ir-hero-actions" style="margin-top: 0;">
                    <a href="investor_financial.php" class="ir-primary-btn">ข้อมูลทางการเงิน</a>
                    <a href="investor_business.php" class="ir-secondary-btn">ข้อมูลธุรกิจ</a>
                </div>
            </section>
        </div>
    </main>


    <!-- Footer -->
    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">MIDA LEASING</div>
                    <p style="color: #ccc; margin-bottom: 10px;">บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</p>
                    <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;">48/1-5 ซอยแจ้งวัฒนะ 14
                        ถนนแจ้งวัฒนะ
                        แขวงทุ่งสองห้อง
                        เขตหลักสี่ กรุงเทพฯ 10210</p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone"
                            style="margin-right: 10px;"></i>02-574-6901</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="https://www.facebook.com/midaleasing.th" target="_blank"
                            style="text-decoration: none;">
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
