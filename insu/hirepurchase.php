<?php
session_start();
if ($_SESSION['sess_login'] == "") {
	header("Location: login.php");
	exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>คำนวณสินเชื่อ - Hire Purchase</title>

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
			padding-left: 0;
		}

		.form-section {
			padding: var(--spacing-md);
			background: rgba(99, 102, 241, 0.03);
			border-radius: var(--radius-md);
			margin-bottom: var(--spacing-md);
		}

		.form-section-title {
			font-size: 0.85rem;
			font-weight: 600;
			color: var(--text-secondary);
			margin-bottom: var(--spacing-sm);
			display: flex;
			align-items: center;
			gap: var(--spacing-sm);
		}

		.form-section-title i {
			color: var(--primary);
		}

		.output-container {
			display: none;
		}

		.output-container.show {
			display: block;
		}

		.btn-group {
			display: flex;
			gap: var(--spacing-md);
			margin-top: var(--spacing-xl);
		}

		.btn-group .btn {
			flex: 1;
		}

		/* Insurance Badge */
		.insurance-badge {
			display: inline-flex;
			align-items: center;
			gap: var(--spacing-xs);
			padding: var(--spacing-xs) var(--spacing-md);
			border-radius: 100px;
			font-size: 0.8rem;
			font-weight: 600;
		}

		.insurance-badge.tlife {
			background: linear-gradient(135deg, #f37020, #ff9800);
			color: white;
		}

		.insurance-badge.no-insurance {
			background: rgba(99, 102, 241, 0.1);
			color: var(--text-secondary);
		}

		/* Summary Cards */
		.summary-cards {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: var(--spacing-md);
			margin-bottom: var(--spacing-lg);
		}

		.summary-card {
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

		.summary-card.warning {
			background: linear-gradient(135deg, var(--warning) 0%, #D97706 100%);
		}

		.summary-card.info {
			background: linear-gradient(135deg, #06B6D4 0%, #0891B2 100%);
		}

		.summary-card-label {
			font-size: 0.7rem;
			opacity: 0.9;
			margin-bottom: var(--spacing-xs);
		}

		.summary-card-value {
			font-size: 0.95rem;
			font-weight: 700;
		}

		.info-grid {
			display: flex;
			justify-content: center;
			gap: var(--spacing-lg);
			margin-bottom: var(--spacing-lg);
			font-size: 0.9rem;
		}

		.info-item {
			display: flex;
			align-items: center;
			gap: var(--spacing-xs);
		}

		.info-item i {
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

		#msg {
			color: var(--danger);
			text-align: center;
			font-size: 0.9rem;
			margin-top: var(--spacing-md);
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
				<h1 class="page-title">สินเชื่อเช่าซื้อรถยนต์</h1>
				<a href="index.php" class="close-btn" title="กลับ">
					<i class="fas fa-times"></i>
				</a>
			</div>

			<form name="frm" id="frm" action="">
				<!-- Insurance Section -->
				<div class="form-section fade-in-delay-1">
					<div class="form-section-title">
						<i class="fas fa-shield-halved"></i> ประกันสินเชื่อ
					</div>
					<div class="radio-group">
						<label class="radio-item">
							<input type="radio" value="TLIFE" id="radioOne" name="LoanType" checked>
							<span class="radio-circle"></span>
							<span class="radio-label">TLife</span>
						</label>
						<label class="radio-item">
							<input type="radio" value="NO" id="radioTwo" name="LoanType">
							<span class="radio-circle"></span>
							<span class="radio-label">ไม่ทำประกัน</span>
						</label>
					</div>
				</div>

				<!-- Personal Info Section (for TLife) -->
				<div id="dvGB" class="form-section fade-in-delay-1">
					<div class="form-section-title">
						<i class="fas fa-user"></i> ข้อมูลผู้กู้
					</div>
					<div class="radio-group mb-2">
						<label class="radio-item">
							<input type="radio" value="male" id="male" name="Gender" checked>
							<span class="radio-circle"></span>
							<span class="radio-label">ชาย</span>
						</label>
						<label class="radio-item">
							<input type="radio" value="female" id="female" name="Gender">
							<span class="radio-circle"></span>
							<span class="radio-label">หญิง</span>
						</label>
					</div>
					<div class="form-group mb-0">
						<label class="form-label">วันเดือนปีเกิด</label>
						<div class="input-group">
							<i class="fas fa-cake-candles input-icon"></i>
							<input type="text" name="BirthDate" id="BirthDate" class="form-control"
								placeholder="วว-ดด-ปปปป" data-mask="00-00-0000" maxlength="10">
						</div>
						<div class="form-hint">
							<i class="fas fa-info-circle"></i> วัน/เดือน/ปี พ.ศ. เกิด เช่น 01-05-2533
						</div>
					</div>
				</div>

				<!-- Loan Details -->
				<div class="form-group fade-in-delay-2">
					<label class="form-label">เงินต้นเช่าซื้อ</label>
					<div class="input-group">
						<i class="fas fa-money-bill-wave input-icon"></i>
						<input type="text" name="LoanAmount" id="LoanAmount" class="form-control"
							placeholder="กรอกจำนวนเงิน" autocomplete="off">
					</div>
				</div>

				<div class="form-group fade-in-delay-2">
					<label class="form-label">อัตราดอกเบี้ย (%)</label>
					<div class="input-group">
						<i class="fas fa-percent input-icon"></i>
						<input type="text" name="InterestRate" id="InterestRate" class="form-control"
							placeholder="กรอกอัตราดอกเบี้ย" autocomplete="off">
					</div>
					<span id="xRate" style="color: var(--danger); font-size: 0.85rem; font-weight: 600;"></span>
				</div>

				<div class="form-group fade-in-delay-2">
					<label class="form-label">จำนวนงวด</label>
					<div class="input-group">
						<i class="fas fa-list-ol input-icon"></i>
						<select name="LoanTerm" id="LoanTerm" class="form-control">
							<option value="">เลือกจำนวนงวด</option>
							<?php for ($i = 1; $i <= 6; $i++): ?>
								<option value="<?= $i * 12 ?>"><?= $i * 12 ?> งวด</option>
							<?php endfor; ?>
						</select>
					</div>
				</div>

				<div class="form-group fade-in-delay-3">
					<label class="form-label">วันที่เซ็นสัญญา</label>
					<div class="input-group">
						<i class="fas fa-calendar-alt input-icon"></i>
						<input type="text" name="AppDate" id="AppDate" class="form-control" placeholder="วว-ดด-ปปปป"
							data-mask="00-00-0000" maxlength="10"
							value="<?= date("d") . "-" . date("m") . "-" . (date("Y") + 543) ?>">
					</div>
					<div class="form-hint">
						<i class="fas fa-info-circle"></i> วัน/เดือน/ปี พ.ศ. เช่น 01-05-2567
					</div>
				</div>

				<div id="msg"></div>

				<!-- Buttons -->
				<div class="btn-group fade-in-delay-3">
					<button type="button" id="btncal" class="btn btn-primary btn-lg">
						<i class="fas fa-calculator"></i> คำนวณสินเชื่อ
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
			$('#dvGB').show();
			$('#xRate').html('');
			$('#msg').html('');
		}

		function backform() {
			$('#dvoutput').removeClass('show').hide();
			$('#dvform').show();
			var arryData = $.trim($('#resdata').val()).split("|");
			$('#BirthDate').val(arryData[19]);
			$('#LoanAmount').val(arryData[2].replace(",", ""));
			$('#InterestRate').val(arryData[15]);
			$('#LoanTerm').val(arryData[16]);
			$('#xRate').html("");
		}

		function resultform() {
			var arryData = $.trim($('#resdata').val()).split("|");
			var EffRate = arryData[26];
			var sGender = (arryData[25] == "male") ? "ชาย" : "หญิง";

			var insuranceBadge = '';
			if (arryData[17] == "TLIFE") {
				insuranceBadge = '<span class="insurance-badge tlife"><i class="fas fa-shield-halved"></i> T Life</span>';
			} else if (arryData[17] == "NO") {
				insuranceBadge = '<span class="insurance-badge no-insurance"><i class="fas fa-times-circle"></i> ไม่มีประกัน</span>';
			}

			var resData = '<div class="page-header">' +
				'<h1 class="page-title"><i class="fas fa-file-invoice-dollar"></i> ข้อมูลสินเชื่อ</h1>' +
				'<a href="javascript:resetform()" class="close-btn" title="เริ่มใหม่"><i class="fas fa-redo"></i></a>' +
				'</div>' +
				'<div class="text-center mb-3">' + insuranceBadge + '</div>' +
				'<div class="info-grid">' +
				'<div class="info-item"><i class="fas fa-user"></i> ' + sGender + '</div>' +
				'<div class="info-item"><i class="fas fa-calendar"></i> ' + arryData[24] + ' ปี</div>' +
				'</div>' +
				'<div class="summary-cards">' +
				'<div class="summary-card primary"><div class="summary-card-label">เงินต้น</div><div class="summary-card-value">' + arryData[2] + '</div></div>' +
				'<div class="summary-card info"><div class="summary-card-label">ดอกเบี้ย</div><div class="summary-card-value">' + arryData[15] + '% (EF ' + EffRate + ')</div></div>' +
				'<div class="summary-card warning"><div class="summary-card-label">งวดแรก</div><div class="summary-card-value">' + arryData[14] + '</div></div>' +
				'<div class="summary-card success"><div class="summary-card-label">งวดสุดท้าย</div><div class="summary-card-value">' + arryData[20] + '</div></div>' +
				'</div>' +
				'<div class="result-box">' +
				'<div class="result-row"><span class="result-label">ยอดจัดเช่าซื้อ</span><span class="result-value">' + arryData[3] + '</span></div>' +
				'<div class="result-row"><span class="result-label">รวมยอดเช่าซื้อ + VAT</span><span class="result-value">' + arryData[11] + '</span></div>';

			if (arryData[17] != "NO") {
				resData += '<div class="result-row"><span class="result-label">ค่าเบี้ยประกัน</span><span class="result-value">' + arryData[1] + '</span></div>' +
					'<div class="result-row"><span class="result-label">ทุนประกัน</span><span class="result-value">' + arryData[28] + '</span></div>';
			}

			resData += '</div>' +
				'<div class="btn-group mt-3">' +
				'<button type="button" class="btn btn-secondary" onclick="backform()"><i class="fas fa-edit"></i> แก้ไข</button>' +
				'<button type="button" class="btn btn-primary" onclick="detailform()"><i class="fas fa-list"></i> รายละเอียด</button>' +
				'</div>';

			$('#output').html(resData);
		}

		function detailform() {
			var arryData = $.trim($('#resdata').val()).split("|");
			var EffRate = arryData[26];
			var sGender = (arryData[25] == "male") ? "ชาย" : "หญิง";

			var insuranceBadge = '';
			if (arryData[17] == "TLIFE") {
				insuranceBadge = '<span class="insurance-badge tlife"><i class="fas fa-shield-halved"></i> T Life - อัตรา ' + arryData[0] + '% | เบี้ย ' + arryData[1] + '</span>';
			}

			var resData = '<div class="page-header">' +
				'<h1 class="page-title"><i class="fas fa-file-invoice-dollar"></i> รายละเอียดสินเชื่อ</h1>' +
				'<a href="javascript:resetform()" class="close-btn" title="เริ่มใหม่"><i class="fas fa-redo"></i></a>' +
				'</div>' +
				'<div class="text-center mb-3">' + insuranceBadge + '</div>' +
				'<div class="info-grid">' +
				'<div class="info-item"><i class="fas fa-user"></i> ' + sGender + '</div>' +
				'<div class="info-item"><i class="fas fa-calendar"></i> ' + arryData[24] + ' ปี</div>' +
				'</div>';

			// Basic Info
			resData += '<div class="result-box mb-3">' +
				'<div class="result-row"><span class="result-label">เงินต้นเช่าซื้อ</span><span class="result-value">' + arryData[2] + '</span></div>' +
				'<div class="result-row"><span class="result-label">อัตราดอกเบี้ย</span><span class="result-value">' + arryData[15] + '% <small class="text-danger">(EF ' + EffRate + ')</small></span></div>' +
				'<div class="result-row"><span class="result-label">จำนวนงวด</span><span class="result-value">' + arryData[16] + ' งวด</span></div>';

			if (arryData[17] != "NO") {
				resData += '<div class="result-row"><span class="result-label">ทุนประกัน</span><span class="result-value">' + arryData[28] + '</span></div>';
			}
			resData += '</div>';

			// Loan Summary
			resData += '<div class="result-box mb-3">' +
				'<div class="result-row"><span class="result-label">ยอดจัดเช่าซื้อ</span><span class="result-value">' + arryData[3] + '</span></div>' +
				'<div class="result-row"><span class="result-label">VAT</span><span class="result-value">' + arryData[4] + '</span></div>' +
				'<div class="result-row"><span class="result-label">รวม</span><span class="result-value highlight">' + arryData[5] + '</span></div>' +
				'</div>';

			// Interest
			resData += '<div class="result-box mb-3">' +
				'<div class="result-row"><span class="result-label">ดอกเบี้ย</span><span class="result-value">' + arryData[6] + '</span></div>' +
				'<div class="result-row"><span class="result-label">VAT</span><span class="result-value">' + arryData[7] + '</span></div>' +
				'<div class="result-row"><span class="result-label">รวม</span><span class="result-value">' + arryData[8] + '</span></div>' +
				'</div>';

			// Total
			resData += '<div class="result-box mb-3">' +
				'<div class="result-row"><span class="result-label">รวมยอดเช่าซื้อ</span><span class="result-value">' + arryData[9] + '</span></div>' +
				'<div class="result-row"><span class="result-label">VAT</span><span class="result-value">' + arryData[10] + '</span></div>' +
				'<div class="result-row"><span class="result-label">รวมทั้งหมด</span><span class="result-value highlight">' + arryData[11] + '</span></div>' +
				'</div>';

			// Installments
			resData += '<div class="summary-cards">' +
				'<div class="summary-card warning"><div class="summary-card-label">ชำระงวดแรก</div><div class="summary-card-value">' + arryData[14] + '</div></div>' +
				'<div class="summary-card success"><div class="summary-card-label">ชำระงวดสุดท้าย</div><div class="summary-card-value">' + arryData[20] + '</div></div>' +
				'</div>';

			resData += '<div class="btn-group mt-3">' +
				'<button type="button" class="btn btn-secondary" onclick="resultform()"><i class="fas fa-arrow-left"></i> กลับ</button>' +
				'<button type="button" class="btn btn-secondary" onclick="backform()"><i class="fas fa-edit"></i> แก้ไข</button>' +
				'</div>';

			$('#output').html(resData);
		}

		$(document).ready(function () {
			// Input masks
			$('#BirthDate, #AppDate').mask('00-00-0000');

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

				if ($.trim($('#AppDate').val()) == "" || $.trim($('#AppDate').val()).length != 10) {
					flgerr = false;
					$("#AppDate").addClass('error');
				} else {
					$("#AppDate").removeClass('error');
				}

				if (flgerr) {
					$('#dvform').hide();
					$('#dvoutput').addClass('show').show();
					$('#output').html('<div class="loading-container"><i class="fas fa-spinner fa-spin"></i><p class="mt-2">กำลังคำนวณ...</p></div>');

					var LoanType = $("input[name='LoanType']:checked").val();
					var Gender = $("input[name='Gender']:checked").val();
					var BirthDate = $('#BirthDate').val();

					$.ajax({
						url: "getData.php",
						type: "POST",
						data: {
							rand: Math.random(),
							LoanType: LoanType,
							Gender: Gender,
							BirthDate: BirthDate,
							IDcard: $('#IDcard').val(),
							AppDate: $('#AppDate').val(),
							LoanAmount: $('#LoanAmount').val(),
							InterestRate: $('#InterestRate').val(),
							LoanTerm: $('#LoanTerm').val()
						},
						success: function (data) {
							$('#resdata').val(data);
							var arryData = $.trim(data).split("|");

							$("#msg").html("");
							$('#xRate').html("");

							resultform();
						},
						error: function () {
							$('#output').html('<div class="text-center text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>เกิดข้อผิดพลาด กรุณาลองใหม่</p></div>');
						}
					});
				}
				return false;
			});

			// Number validation
			$("#LoanAmount, #InterestRate").on("keypress keyup blur", function (event) {
				$(this).val($(this).val().replace(/[^0-9\.]/g, ''));
				if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
					event.preventDefault();
				}
			});

			// Insurance toggle
			$("#radioOne").click(function () {
				$("#dvGB").slideDown();
			});

			$("#radioTwo").click(function () {
				$("#dvGB").slideUp();
			});
		});
	</script>
</body>

</html>