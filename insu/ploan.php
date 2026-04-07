<?php
session_start();
if ($_SESSION['sess_login'] == "") {
    header("Location: login.php");
    exit();
}

if ($_GET['prod'] == '001') {
    $prodName = "สินเชื่อเช่าซื้อรถยนต์";
} elseif ($_GET['prod'] == '002') {
    $prodName = "สินชื่อจำนำทะเบียน";
} elseif ($_GET['prod'] == '005') {
    $prodName = "สินชื่อจำนำทะเบียน";
} else {
    $prodName = "สินเชื่อบุคคล";
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณสินเชื่อ - <?= $prodName ?></title>

    <!-- Google Fonts - Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Modern Style -->
    <link href="css/modern-style.css" rel="stylesheet">

    <style>
        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }

        .main-card {
            width: 100%;
            max-width: 420px;
        }

        .close-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-full);
            color: var(--danger);
            font-size: 1rem;
            transition: all var(--transition-normal);
            text-decoration: none;
        }

        .close-btn:hover {
            background: var(--danger);
            color: white;
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--warning);
            margin-top: var(--spacing-xs);
            padding-left: 3rem;
        }

        .btn-group {
            display: flex;
            gap: var(--spacing-md);
            margin-top: var(--spacing-xl);
        }

        .btn-group .btn {
            flex: 1;
        }

        /* Output Section */
        .output-container {
            display: none;
        }

        .output-container.show {
            display: block;
        }

        .summary-cards {
            display: flex;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }

        .summary-card {
            flex: 1;
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            text-align: center;
            color: white;
        }

        .summary-card.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .summary-card.success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        }

        .summary-card-label {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-bottom: var(--spacing-xs);
        }

        .summary-card-value {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .schedule-table-container {
            max-height: 280px;
            overflow-y: auto;
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-lg);
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .schedule-table th {
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: var(--spacing-sm);
            font-weight: 600;
            font-size: 0.75rem;
        }

        .schedule-table td {
            padding: var(--spacing-sm);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
            text-align: right;
        }

        .schedule-table td:first-child {
            text-align: center;
        }

        .schedule-table tbody tr:nth-child(even) {
            background: rgba(99, 102, 241, 0.03);
        }

        .schedule-table tbody tr:hover {
            background: rgba(99, 102, 241, 0.08);
        }

        .text-interest {
            color: var(--danger);
        }

        .text-principal {
            color: var(--primary);
        }

        .loading-container {
            text-align: center;
            padding: var(--spacing-xl);
        }

        .loading-container i {
            font-size: 2rem;
            color: var(--primary);
            animation: pulse 1s infinite;
        }

        .floating-shape {
            position: fixed;
            border-radius: 50%;
            opacity: 0.08;
            pointer-events: none;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            top: -150px;
            right: -150px;
            animation: float 12s ease-in-out infinite;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            bottom: -100px;
            left: -100px;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(15deg);
            }
        }
    </style>
</head>

<body>
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>

    <div class="main-container">
        <!-- Input Form Card -->
        <div id="dvform" class="main-card glass-card fade-in">
            <!-- Header -->
            <div class="page-header">
                <h1 class="page-title"><?= $prodName ?></h1>
                <a href="index.php" class="close-btn" title="กลับ">
                    <i class="fas fa-times"></i>
                </a>
            </div>

            <form name="frm" id="frm" action="">
                <!-- Loan Amount -->
                <div class="form-group fade-in-delay-1">
                    <label class="form-label">ยอดเงินกู้</label>
                    <div class="input-group">
                        <i class="fas fa-money-bill-wave input-icon"></i>
                        <input type="text" name="LoanAmount" id="LoanAmount" class="form-control"
                            placeholder="กรอกยอดเงินกู้" autocomplete="off">
                    </div>
                </div>

                <!-- Interest Rate -->
                <div class="form-group fade-in-delay-1">
                    <label class="form-label">อัตราดอกเบี้ย (% ต่อปี)</label>
                    <div class="input-group">
                        <i class="fas fa-percent input-icon"></i>
                        <input type="text" name="InterestRate" id="InterestRate" class="form-control"
                            placeholder="กรอกอัตราดอกเบี้ย" autocomplete="off">
                    </div>
                </div>

                <!-- Loan Term -->
                <div class="form-group fade-in-delay-2">
                    <label class="form-label">จำนวนงวด</label>
                    <div class="input-group">
                        <i class="fas fa-list-ol input-icon"></i>
                        <input type="text" name="LoanTerm" id="LoanTerm" class="form-control" placeholder="กรอกจำนวนงวด"
                            autocomplete="off">
                    </div>
                </div>

                <!-- App Date -->
                <div class="form-group fade-in-delay-2">
                    <label class="form-label">วันที่เริ่มสัญญา</label>
                    <div class="input-group">
                        <i class="fas fa-calendar-alt input-icon"></i>
                        <input type="text" name="AppDate" id="AppDate" class="form-control" placeholder="วว-ดด-ปปปป"
                            data-mask="00-00-0000" maxlength="10"
                            value="<?= date("d") . "-" . date("m") . "-" . (date("Y") + 543) ?>">
                    </div>
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i> รูปแบบ: วัน-เดือน-ปี พ.ศ. เช่น 01-05-2567
                    </div>
                </div>

                <!-- Buttons -->
                <div class="btn-group fade-in-delay-3">
                    <button type="button" id="btncal" class="btn btn-primary">
                        <i class="fas fa-calculator"></i> คำนวณ
                    </button>
                    <button type="button" id="btnclear" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> ล้าง
                    </button>
                </div>

                <input type="hidden" name="resdata" id="resdata">
            </form>
        </div>

        <!-- Output Card -->
        <div id="dvoutput" class="main-card glass-card output-container">
            <div id="output"></div>
        </div>
    </div>

    <script src="js/jquery-1.10.2.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/jquery.mask.min.js"></script>
    <script>
        function resetform() {
            $('#dvoutput').removeClass('show').hide();
            $('#dvform').show();
            $('#resdata').val('');
            document.forms[0].reset();
            $('#AppDate').val('<?= date("d") . "-" . date("m") . "-" . (date("Y") + 543) ?>');
        }

        function backform() {
            var resData = '';
            var data = $("#resdata").val();

            if ($.trim(data) != "") {
                var arryData = $.trim(data).split("@");
                if ($.trim(arryData[0]) != "") {
                    var arryData2 = $.trim(arryData[0]).split("#");
                    resData = '<div class="page-header">' +
                        '<h1 class="page-title"><i class="fas fa-calculator"></i> สินชื่อจำนำทะเบียน</h1>' +
                        '<a href="javascript:resetform()" class="close-btn" title="เริ่มใหม่"><i class="fas fa-redo"></i></a>' +
                        '</div>' +
                        '<div class="summary-cards">' +
                        '<div class="summary-card primary"><div class="summary-card-label">เงินกู้</div><div class="summary-card-value">' + arryData2[0] + '</div></div>' +
                        '<div class="summary-card success"><div class="summary-card-label">ผ่อน/เดือน</div><div class="summary-card-value">' + arryData2[3] + '</div></div>' +
                        '</div>' +
                        '<div class="result-box">' +
                        '<div class="result-row"><span class="result-label">จำนวนเงินกู้</span><span class="result-value">' + arryData2[0] + ' บาท</span></div>' +
                        '<div class="result-row"><span class="result-label">ดอกเบี้ยรวม</span><span class="result-value">' + arryData2[1] + ' บาท</span></div>' +
                        '<div class="result-row"><span class="result-label">เงินกู้ + ดอกเบี้ย</span><span class="result-value">' + arryData2[2] + ' บาท</span></div>' +
                        '<div class="result-row"><span class="result-label">ผ่อนเดือนละ</span><span class="result-value highlight">' + arryData2[3] + ' บาท</span></div>' +
                        '</div>' +
                        '<div class="btn-group mt-3">' +
                        '<button type="button" class="btn btn-primary" onclick="detailform()"><i class="fas fa-table"></i> ตารางผ่อนชำระ</button>' +
                        '</div>';
                }
            }
            $('#output').html(resData);
            return false;
        }

        function detailform() {
            var data = $.trim($('#resdata').val());
            var resData = '';

            if ($.trim(data) != "") {
                var arryData = $.trim(data).split("@");
                if ($.trim(arryData[0]) != "") {
                    var arryData2 = $.trim(arryData[0]).split("#");
                    var resDetail = '';

                    if ($.trim(arryData[1]) != "") {
                        var arryDetail = $.trim(arryData[1]).split("|");
                        resDetail = '<div class="schedule-table-container"><table class="schedule-table">' +
                            '<thead><tr><th>งวด</th><th>ค่างวด</th><th>เงินต้น</th><th>ดอกเบี้ย</th><th>คงเหลือ</th></tr></thead><tbody>';

                        for (var i = 0; i < arryDetail.length; i++) {
                            if ($.trim(arryDetail[i]) != "") {
                                var arryDetail2 = $.trim(arryDetail[i]).split(":");
                                resDetail += '<tr>' +
                                    '<td>' + arryDetail2[0] + '</td>' +
                                    '<td>' + arryDetail2[1] + '</td>' +
                                    '<td class="text-principal">' + arryDetail2[2] + '</td>' +
                                    '<td class="text-interest">' + arryDetail2[3] + '</td>' +
                                    '<td>' + arryDetail2[4] + '</td>' +
                                    '</tr>';
                            }
                        }
                        resDetail += '</tbody></table></div>';
                    }

                    resData = '<div class="page-header">' +
                        '<h1 class="page-title"><i class="fas fa-table"></i> ตารางผ่อนชำระ</h1>' +
                        '<a href="javascript:resetform()" class="close-btn" title="เริ่มใหม่"><i class="fas fa-redo"></i></a>' +
                        '</div>' +
                        '<div class="summary-cards">' +
                        '<div class="summary-card primary"><div class="summary-card-label">เงินกู้</div><div class="summary-card-value">' + arryData2[0] + '</div></div>' +
                        '<div class="summary-card success"><div class="summary-card-label">ผ่อน/เดือน</div><div class="summary-card-value">' + arryData2[3] + '</div></div>' +
                        '</div>' +
                        resDetail +
                        '<div class="btn-group mt-3">' +
                        '<button type="button" class="btn btn-secondary" onclick="backform()"><i class="fas fa-arrow-left"></i> กลับ</button>' +
                        '</div>';
                }
            }

            $('#output').html(resData);
        }

        $(document).ready(function () {
            // Calculate button
            $("#btncal").click(function () {
                var flgerr = true;

                if ($.trim($('#LoanAmount').val()) == "") {
                    flgerr = false;
                    $("#LoanAmount").addClass('error');
                } else {
                    $("#LoanAmount").removeClass('error');
                }

                if ($.trim($('#InterestRate').val()) == "") {
                    flgerr = false;
                    $("#InterestRate").addClass('error');
                } else {
                    $("#InterestRate").removeClass('error');
                }

                if ($.trim($('#LoanTerm').val()) == "") {
                    flgerr = false;
                    $("#LoanTerm").addClass('error');
                } else {
                    $("#LoanTerm").removeClass('error');
                }

                if (flgerr) {
                    $('#dvform').hide();
                    $('#dvoutput').addClass('show').show();
                    $('#output').html('<div class="loading-container"><i class="fas fa-spinner fa-spin"></i><p class="mt-2">กำลังคำนวณ...</p></div>');

                    $.ajax({
                        url: "getDataLoan.php",
                        type: "POST",
                        data: {
                            rand: Math.random(),
                            LoanAmount: $('#LoanAmount').val(),
                            InterestRate: $('#InterestRate').val(),
                            LoanTerm: $('#LoanTerm').val(),
                            AppDate: $('#AppDate').val()
                        },
                        success: function (data) {
                            console.log(data);
                            $("#resdata").val($.trim(data));
                            backform();
                        },
                        error: function () {
                            $('#output').html('<div class="text-center text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>เกิดข้อผิดพลาด กรุณาลองใหม่</p></div>');
                        }
                    });
                }
                return false;
            });

            // Clear button
            $("#btnclear").click(function () {
                resetform();
                return false;
            });

            // Input mask
            $('#AppDate').mask('00-00-0000');
        });
    </script>
</body>

</html>