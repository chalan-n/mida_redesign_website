<?php
require_once 'admin/config/db.php';
$database = new Database();
$db = $database->getConnection();

@include_once 'track_visitor.php';

$settings = array();
try {
    $stmt_settings = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt_settings ? $stmt_settings->fetch(PDO::FETCH_ASSOC) : array();
} catch (PDOException $e) {
    $settings = array();
}

$site_phone = !empty($settings['site_phone']) ? $settings['site_phone'] : '02-574-6901';
$site_email = 'ir@mida-leasing.com';
$site_address = !empty($settings['site_address'])
    ? $settings['site_address']
    : '48/1-5 ซอยแจ้งวัฒนะ 14 ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง เขตหลักสี่ กรุงเทพฯ 10210';
$site_facebook = !empty($settings['site_facebook']) ? $settings['site_facebook'] : 'https://www.facebook.com/midaleasing.th';
$site_line = !empty($settings['site_line']) ? $settings['site_line'] : 'https://line.me/R/ti/p/@midaleasing';

$smtp_config = array(
    'host' => getenv('SMTP_HOST') ? getenv('SMTP_HOST') : 'smtp01.violin.co.th',
    'port' => (int) (getenv('SMTP_PORT') ? getenv('SMTP_PORT') : 25),
    'username' => getenv('SMTP_USERNAME') ? getenv('SMTP_USERNAME') : 'mida-leasing@violin.co.th',
    'password' => getenv('SMTP_PASSWORD') ? getenv('SMTP_PASSWORD') : 'UK2$f80v',
    'from_email' => getenv('SMTP_FROM_EMAIL') ? getenv('SMTP_FROM_EMAIL') : 'mida-leasing@violin.co.th',
    'from_name' => getenv('SMTP_FROM_NAME') ? getenv('SMTP_FROM_NAME') : 'MIDA Leasing Website',
);

function irLoadPhpMailer()
{
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') || class_exists('PHPMailer')) {
        return true;
    }

    $autoload_paths = array(
        __DIR__ . '/vendor/autoload.php',
        __DIR__ . '/PHPMailer/PHPMailerAutoload.php',
        __DIR__ . '/PHPMailer/class.phpmailer.php',
        __DIR__ . '/class.phpmailer.php',
    );

    foreach ($autoload_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') || class_exists('PHPMailer')) {
                return true;
            }
        }
    }

    return false;
}

function irSendSmtpCommand($socket, $command, $expected_codes)
{
    if ($command !== null) {
        fwrite($socket, $command . "\r\n");
    }

    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    $code = (int) substr($response, 0, 3);
    if (!in_array($code, (array) $expected_codes, true)) {
        throw new Exception('SMTP error: ' . trim($response));
    }

    return $response;
}

function irSendViaRawSmtp($to, $subject, $body, $reply_email, $reply_name, $config)
{
    $socket = @fsockopen($config['host'], $config['port'], $errno, $errstr, 15);
    if (!$socket) {
        throw new Exception('SMTP connection failed: ' . $errstr);
    }

    stream_set_timeout($socket, 15);

    try {
        irSendSmtpCommand($socket, null, 220);
        irSendSmtpCommand($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'mida-leasing.com'), 250);
        irSendSmtpCommand($socket, 'AUTH LOGIN', 334);
        irSendSmtpCommand($socket, base64_encode($config['username']), 334);
        irSendSmtpCommand($socket, base64_encode($config['password']), 235);
        irSendSmtpCommand($socket, 'MAIL FROM:<' . $config['from_email'] . '>', 250);
        irSendSmtpCommand($socket, 'RCPT TO:<' . $to . '>', array(250, 251));
        irSendSmtpCommand($socket, 'DATA', 354);

        $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $safe_reply_name = str_replace(array("\r", "\n"), '', $reply_name);
        $message = implode("\r\n", array(
            'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
            'Reply-To: ' . $safe_reply_name . ' <' . $reply_email . '>',
            'To: ' . $to,
            'Subject: ' . $encoded_subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        )) . "\r\n\r\n" . str_replace("\n.", "\n..", $body);

        fwrite($socket, $message . "\r\n.\r\n");
        irSendSmtpCommand($socket, null, 250);
        irSendSmtpCommand($socket, 'QUIT', 221);
    } finally {
        fclose($socket);
    }

    return true;
}

function irSendInvestorEmail($to, $subject, $body, $reply_email, $reply_name, $config)
{
    if (irLoadPhpMailer()) {
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new PHPMailer\PHPMailer\PHPMailer();
        } else {
            $mail = new PHPMailer();
        }

        $mail->Encoding = 'quoted-printable';
        $mail->CharSet = 'utf-8';
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPSecure = '';
        $mail->SMTPAuth = true;
        $mail->Host = $config['host'];
        $mail->Port = $config['port'];
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        if (method_exists($mail, 'setFrom')) {
            $mail->setFrom($config['from_email'], $config['from_name']);
        } else {
            $mail->SetFrom($config['from_email'], $config['from_name']);
        }
        if (method_exists($mail, 'addAddress')) {
            $mail->addAddress($to);
            $mail->addReplyTo($reply_email, $reply_name);
        } else {
            $mail->AddAddress($to);
            $mail->AddReplyTo($reply_email, $reply_name);
        }
        $mail->Subject = $subject;
        $mail->Body = $body;

        $sent = method_exists($mail, 'send') ? $mail->send() : $mail->Send();
        if (!$sent) {
            throw new Exception($mail->ErrorInfo);
        }

        return true;
    }

    return irSendViaRawSmtp($to, $subject, $body, $reply_email, $reply_name, $config);
}

$contact_success = false;
$contact_error = '';
$contact_form = array(
    'name' => '',
    'email' => '',
    'phone' => '',
    'topic' => '',
    'message' => '',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ir_contact_form'])) {
    $contact_form['name'] = trim($_POST['name'] ?? '');
    $contact_form['email'] = trim($_POST['email'] ?? '');
    $contact_form['phone'] = trim($_POST['phone'] ?? '');
    $contact_form['topic'] = trim($_POST['topic'] ?? '');
    $contact_form['message'] = trim($_POST['message'] ?? '');
    $honeypot = trim($_POST['website'] ?? '');

    if ($honeypot !== '') {
        $contact_success = true;
    } elseif ($contact_form['name'] === '' || $contact_form['email'] === '' || $contact_form['message'] === '') {
        $contact_error = 'กรุณากรอกชื่อ อีเมล และข้อความที่ต้องการสอบถาม';
    } elseif (!filter_var($contact_form['email'], FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'กรุณากรอกอีเมลให้ถูกต้อง';
    } else {
        $subject = 'ติดต่อสอบถามนักลงทุนสัมพันธ์จากเว็บไซต์ MIDA Leasing';
        $body_lines = array(
            'มีข้อความติดต่อสอบถามนักลงทุนสัมพันธ์จากเว็บไซต์',
            '',
            'ชื่อ-นามสกุล: ' . $contact_form['name'],
            'อีเมล: ' . $contact_form['email'],
            'โทรศัพท์: ' . ($contact_form['phone'] !== '' ? $contact_form['phone'] : '-'),
            'หัวข้อที่ต้องการสอบถาม: ' . ($contact_form['topic'] !== '' ? $contact_form['topic'] : '-'),
            '',
            'ข้อความ:',
            $contact_form['message'],
            '',
            'ส่งจากหน้า: investor_contact.php',
            'วันที่ส่ง: ' . date('Y-m-d H:i:s'),
        );
        $headers = array(
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: MIDA Leasing Website <no-reply@mida-leasing.com>',
            'Reply-To: ' . $contact_form['name'] . ' <' . $contact_form['email'] . '>',
        );

        try {
            $contact_success = irSendInvestorEmail(
                $site_email,
                $subject,
                implode("\n", $body_lines),
                $contact_form['email'],
                $contact_form['name'],
                $smtp_config
            );
        } catch (Exception $e) {
            error_log('Investor contact email failed: ' . $e->getMessage());
            $contact_success = false;
        }
        if ($contact_success) {
            $contact_form = array('name' => '', 'email' => '', 'phone' => '', 'topic' => '', 'message' => '');
        } else {
            $contact_error = 'ไม่สามารถส่งข้อความได้ในขณะนี้ กรุณาติดต่อทางอีเมล ir@mida-leasing.com';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อนักลงทุนสัมพันธ์ - MIDA LEASING</title>
    <meta name="description" content="ช่องทางติดต่อนักลงทุนสัมพันธ์ บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน) สำหรับผู้ถือหุ้น นักลงทุน และผู้สนใจข้อมูลบริษัท">

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
        .ir-contact-page {
            background: #f6f8fb;
            color: #183153;
        }

        .ir-contact-hero {
            position: relative;
            overflow: hidden;
            padding: 132px 0 72px;
            background:
                radial-gradient(circle at 16% 20%, rgba(255, 199, 50, 0.24), transparent 28%),
                linear-gradient(108deg, #f9fbff 0%, #eef5ff 62%, #1f5fb8 62%, #17488f 100%);
        }

        .ir-contact-hero::after {
            content: "";
            position: absolute;
            inset: auto -8% -42% 44%;
            height: 58%;
            background: rgba(255, 255, 255, 0.12);
            transform: skewX(-12deg);
            pointer-events: none;
        }

        .ir-contact-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(340px, 0.78fr);
            gap: 42px;
            align-items: center;
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

        .ir-contact-hero h1 {
            max-width: 620px;
            margin: 0 0 18px;
            color: var(--primary-blue);
            font-size: clamp(2.25rem, 5vw, 4.4rem);
            line-height: 1;
            letter-spacing: -0.05em;
        }

        .ir-contact-hero p {
            max-width: 560px;
            margin: 0;
            color: #56677d;
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .ir-contact-card {
            position: relative;
            padding: 34px;
            border: 1px solid rgba(24, 49, 83, 0.1);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 24px 60px rgba(20, 58, 115, 0.14);
            backdrop-filter: blur(12px);
        }

        .ir-contact-card h2 {
            margin: 0 0 12px;
            color: var(--primary-blue);
            font-size: 1.45rem;
        }

        .ir-contact-card p {
            margin-bottom: 22px;
            color: #5a6b80;
            font-size: 1rem;
            line-height: 1.7;
        }

        .ir-contact-list {
            display: grid;
            gap: 14px;
        }

        .ir-contact-item {
            display: grid;
            grid-template-columns: 46px 1fr;
            gap: 14px;
            align-items: start;
            padding: 16px;
            border-radius: 20px;
            background: #f4f8ff;
        }

        .ir-contact-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 16px;
            color: var(--primary-blue);
            background: #fff2c8;
        }

        .ir-contact-item strong {
            display: block;
            margin-bottom: 4px;
            color: #12294a;
            font-size: 1rem;
        }

        .ir-contact-item a,
        .ir-contact-item span {
            color: #4e6178;
            line-height: 1.7;
            text-decoration: none;
        }

        .ir-contact-item a:hover {
            color: var(--primary-blue);
        }

        .ir-contact-section {
            padding: 72px 0;
        }

        .ir-contact-panel {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(320px, 0.75fr);
            gap: 24px;
            align-items: stretch;
        }

        .ir-info-box,
        .ir-note-box {
            padding: 30px;
            border: 1px solid #dce7f5;
            border-radius: 28px;
            background: #fff;
            box-shadow: 0 16px 45px rgba(18, 57, 105, 0.08);
        }

        .ir-info-box h2,
        .ir-note-box h2 {
            margin: 0 0 16px;
            color: var(--primary-blue);
            font-size: 1.6rem;
        }

        .ir-info-grid {
            display: grid;
            gap: 14px;
        }

        .ir-info-box > h2:not(.ir-form-title),
        .ir-info-box > .ir-info-grid {
            display: none;
        }

        .ir-contact-form {
            display: grid;
            gap: 16px;
            margin-top: 18px;
        }

        .ir-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .ir-form-field {
            display: grid;
            gap: 8px;
        }

        .ir-form-field.ir-form-field-full {
            grid-column: 1 / -1;
        }

        .ir-form-field label {
            color: #183153;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .ir-form-field label span {
            color: #d53f3f;
        }

        .ir-form-field input,
        .ir-form-field textarea,
        .ir-form-field select {
            width: 100%;
            border: 1px solid #d7e2f1;
            border-radius: 16px;
            background: #f8fbff;
            color: #183153;
            font: inherit;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .ir-form-field input,
        .ir-form-field select {
            min-height: 50px;
            padding: 0 16px;
        }

        .ir-form-field textarea {
            min-height: 136px;
            padding: 14px 16px;
            resize: vertical;
        }

        .ir-form-field input:focus,
        .ir-form-field textarea:focus,
        .ir-form-field select:focus {
            border-color: var(--primary-blue);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(23, 69, 143, 0.1);
        }

        .ir-form-submit {
            justify-self: start;
            border: 0;
            cursor: pointer;
        }

        .ir-form-note {
            margin: 0;
            color: #6a7b8f;
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .ir-form-alert {
            padding: 14px 16px;
            border-radius: 16px;
            font-weight: 700;
            line-height: 1.6;
        }

        .ir-form-alert-success {
            color: #0d6b3f;
            background: #e9f9f0;
            border: 1px solid #bfe8d1;
        }

        .ir-form-alert-error {
            color: #9c2828;
            background: #fff0f0;
            border: 1px solid #f2c3c3;
        }

        .ir-form-honeypot {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .ir-info-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #edf2f8;
        }

        .ir-info-row:last-child {
            border-bottom: 0;
        }

        .ir-info-row span {
            color: #6a7b8f;
            font-weight: 600;
        }

        .ir-info-row strong,
        .ir-info-row a {
            color: #183153;
            font-weight: 700;
            text-decoration: none;
        }

        .ir-note-box {
            background:
                radial-gradient(circle at 88% 12%, rgba(255, 199, 50, 0.24), transparent 32%),
                linear-gradient(150deg, #ffffff 0%, #eef5ff 100%);
        }

        .ir-note-list {
            display: grid;
            gap: 14px;
            margin: 18px 0 0;
            padding: 0;
            list-style: none;
        }

        .ir-note-list li {
            display: flex;
            gap: 10px;
            color: #53677e;
            line-height: 1.7;
        }

        .ir-note-list i {
            margin-top: 5px;
            color: #c89200;
        }

        .ir-contact-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .ir-primary-btn,
        .ir-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            padding: 0 22px;
            border-radius: 999px;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .ir-primary-btn {
            color: #10284c;
            background: linear-gradient(135deg, #ffc32f 0%, #ffd86b 100%);
            box-shadow: 0 12px 28px rgba(255, 195, 47, 0.26);
        }

        .ir-secondary-btn {
            color: var(--primary-blue);
            background: #fff;
            border: 1px solid #dce7f5;
        }

        .ir-primary-btn:hover,
        .ir-secondary-btn:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 992px) {
            .ir-contact-hero {
                padding: 118px 0 56px;
                background: linear-gradient(180deg, #fff8e4 0%, #eef5ff 100%);
            }

            .ir-contact-grid,
            .ir-contact-panel {
                grid-template-columns: 1fr;
            }

            .ir-contact-card {
                padding: 24px;
            }
        }

        @media (max-width: 640px) {
            .ir-contact-hero {
                padding: 104px 0 42px;
            }

            .ir-contact-hero h1 {
                font-size: 2.35rem;
            }

            .ir-info-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .ir-form-grid {
                grid-template-columns: 1fr;
            }

            .ir-contact-section {
                padding: 46px 0;
            }
        }
    </style>
</head>

<body class="ir-contact-page">
    <?php $active_page = '';
    include 'includes/nav.php'; ?>

    <main>
        <section class="ir-contact-hero">
            <div class="container">
                <div class="ir-contact-grid">
                    <div>
                        <span class="ir-kicker">MIDA LEASING INVESTOR RELATIONS</span>
                        <h1>ติดต่อนักลงทุนสัมพันธ์</h1>
                        <p>ช่องทางสำหรับผู้ถือหุ้น นักลงทุน นักวิเคราะห์ และผู้สนใจข้อมูลบริษัท สามารถติดต่อสอบถามข้อมูลด้านนักลงทุนสัมพันธ์ของไมด้าลิสซิ่งได้ที่นี่</p>
                    </div>

                    <article class="ir-contact-card">
                        <h2>Investor Relations Contact</h2>
                        <p>ทีมงานพร้อมรับเรื่องและประสานข้อมูลที่เกี่ยวข้องกับบริษัท เอกสารเผยแพร่ และข้อมูลสำหรับผู้ถือหุ้น</p>
                        <div class="ir-contact-list">
                            <div class="ir-contact-item">
                                <span class="ir-contact-icon"><i class="fa-solid fa-phone"></i></span>
                                <div>
                                    <strong>โทรศัพท์</strong>
                                    <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $site_phone)); ?>"><?php echo htmlspecialchars($site_phone); ?></a>
                                </div>
                            </div>
                            <div class="ir-contact-item">
                                <span class="ir-contact-icon"><i class="fa-solid fa-envelope"></i></span>
                                <div>
                                    <strong>อีเมล</strong>
                                    <a href="mailto:<?php echo htmlspecialchars($site_email); ?>"><?php echo htmlspecialchars($site_email); ?></a>
                                </div>
                            </div>
                            <div class="ir-contact-item">
                                <span class="ir-contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                                <div>
                                    <strong>ที่อยู่บริษัท</strong>
                                    <span><?php echo nl2br(htmlspecialchars($site_address)); ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="ir-contact-section">
            <div class="container">
                <div class="ir-contact-panel">
                    <article class="ir-info-box">
                        <h2 class="ir-form-title">ติดต่อสอบถามข้อมูล</h2>
                        <p class="ir-form-note">กรอกข้อมูลที่ต้องการสอบถาม ทีมงานนักลงทุนสัมพันธ์จะรับเรื่องและติดต่อกลับตามช่องทางที่แจ้งไว้</p>

                        <?php if ($contact_success): ?>
                            <div class="ir-form-alert ir-form-alert-success">
                                ส่งข้อความเรียบร้อยแล้ว ขอบคุณที่ติดต่อฝ่ายนักลงทุนสัมพันธ์
                            </div>
                        <?php elseif ($contact_error !== ''): ?>
                            <div class="ir-form-alert ir-form-alert-error">
                                <?php echo htmlspecialchars($contact_error, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>

                        <form class="ir-contact-form" method="post" action="investor_contact.php">
                            <input type="hidden" name="ir_contact_form" value="1">
                            <div class="ir-form-honeypot" aria-hidden="true">
                                <label for="irWebsite">เว็บไซต์</label>
                                <input id="irWebsite" type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="ir-form-grid">
                                <div class="ir-form-field">
                                    <label for="irName">ชื่อ - นามสกุล <span>*</span></label>
                                    <input id="irName" type="text" name="name" value="<?php echo htmlspecialchars($contact_form['name'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="ระบุชื่อและนามสกุล" required>
                                </div>
                                <div class="ir-form-field">
                                    <label for="irEmail">อีเมล <span>*</span></label>
                                    <input id="irEmail" type="email" name="email" value="<?php echo htmlspecialchars($contact_form['email'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="example@email.com" required>
                                </div>
                                <div class="ir-form-field">
                                    <label for="irPhone">เบอร์โทรศัพท์</label>
                                    <input id="irPhone" type="tel" name="phone" value="<?php echo htmlspecialchars($contact_form['phone'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="08x-xxx-xxxx">
                                </div>
                                <div class="ir-form-field">
                                    <label for="irTopic">หัวข้อที่ต้องการสอบถาม</label>
                                    <select id="irTopic" name="topic">
                                        <?php
                                        $topics = array(
                                            '' => 'เลือกหัวข้อ',
                                            'ข้อมูลบริษัท' => 'ข้อมูลบริษัท',
                                            'ข้อมูลทางการเงิน' => 'ข้อมูลทางการเงิน',
                                            'เอกสารเผยแพร่' => 'เอกสารเผยแพร่',
                                            'ข้อมูลผู้ถือหุ้น' => 'ข้อมูลผู้ถือหุ้น',
                                            'อื่น ๆ' => 'อื่น ๆ',
                                        );
                                        foreach ($topics as $value => $label):
                                            ?>
                                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $contact_form['topic'] === $value ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="ir-form-field ir-form-field-full">
                                    <label for="irMessage">ข้อความที่ต้องการสอบถาม <span>*</span></label>
                                    <textarea id="irMessage" name="message" placeholder="ระบุรายละเอียดที่ต้องการสอบถาม" required><?php echo htmlspecialchars($contact_form['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="ir-primary-btn ir-form-submit">
                                <i class="fa-solid fa-paper-plane"></i> ส่งข้อมูล
                            </button>
                            <p class="ir-form-note">
                                การกดส่งข้อมูล แสดงว่าคุณอ่านและรับทราบ
                                <a href="privacy_policy.php" target="_blank">นโยบายความเป็นส่วนตัว</a>
                                <br>เรียบร้อยแล้ว
                            </p>
                        </form>
                        <h2>ข้อมูลติดต่อบริษัท</h2>
                        <div class="ir-info-grid">
                            <div class="ir-info-row">
                                <span>ชื่อบริษัท</span>
                                <strong>บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</strong>
                            </div>
                            <div class="ir-info-row">
                                <span>ฝ่ายที่เกี่ยวข้อง</span>
                                <strong>นักลงทุนสัมพันธ์</strong>
                            </div>
                            <div class="ir-info-row">
                                <span>โทรศัพท์</span>
                                <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $site_phone)); ?>"><?php echo htmlspecialchars($site_phone); ?></a>
                            </div>
                            <div class="ir-info-row">
                                <span>อีเมล</span>
                                <a href="mailto:<?php echo htmlspecialchars($site_email); ?>"><?php echo htmlspecialchars($site_email); ?></a>
                            </div>
                            <div class="ir-info-row">
                                <span>เว็บไซต์</span>
                                <a href="https://www.mida-leasing.com" target="_blank">www.mida-leasing.com</a>
                            </div>
                            <div class="ir-info-row">
                                <span>ที่อยู่</span>
                                <strong><?php echo nl2br(htmlspecialchars($site_address)); ?></strong>
                            </div>
                        </div>
                    </article>

                    <aside class="ir-note-box">
                        <h2>ติดต่อเรื่องใดได้บ้าง</h2>
                        <ul class="ir-note-list">
                            <li><i class="fa-solid fa-circle-check"></i><span>สอบถามข้อมูลบริษัทและโครงสร้างธุรกิจ</span></li>
                            <li><i class="fa-solid fa-circle-check"></i><span>สอบถามรายงานทางการเงิน รายงานประจำปี และเอกสารเผยแพร่</span></li>
                            <li><i class="fa-solid fa-circle-check"></i><span>ประสานงานข้อมูลสำหรับผู้ถือหุ้น นักลงทุน และผู้สนใจทั่วไป</span></li>
                        </ul>
                        <div class="ir-contact-actions">
                            <a href="investor_financial.php" class="ir-primary-btn"><i class="fa-solid fa-chart-column"></i> ดูข้อมูลทางการเงิน</a>
                            <a href="investor_publications.php" class="ir-secondary-btn"><i class="fa-solid fa-folder-open"></i> เอกสารเผยแพร่</a>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </main>

    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">MIDA LEASING</div>
                    <p style="color: #ccc; margin-bottom: 10px;">บริษัท ไมด้าลิสซิ่ง จำกัด (มหาชน)</p>
                    <p style="color: #ccc; margin-bottom: 10px; font-size: 1rem;"><?php echo nl2br(htmlspecialchars($site_address)); ?></p>
                    <p style="color: #ccc; margin-bottom: 20px; font-size: 1rem;"><i class="fa-solid fa-phone" style="margin-right: 10px;"></i><?php echo htmlspecialchars($site_phone); ?></p>
                    <div style="display: flex; gap: 15px;">
                        <a href="<?php echo htmlspecialchars($site_facebook); ?>" target="_blank" style="text-decoration: none;">
                            <i class="fa-brands fa-facebook" style="font-size: 2rem; color: #1877F2;"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars($site_line); ?>" target="_blank" style="text-decoration: none;">
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
                        <li><a href="news.php">ข่าวสารและกิจกรรม</a></li>
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
