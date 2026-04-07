<?php
session_start();
include("includes/config.php");
include("includes/dateThai.php");

define('FINANCIAL_MAX_ITERATIONS', 128);
define('FINANCIAL_PRECISION', 0.0000001);

function RATE($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.01)
{

	$rate = $guess;
	if (abs($rate) < FINANCIAL_PRECISION) {
		$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
	} else {
		$f = exp($nper * log(1 + $rate));
		$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
	}
	$y0 = $pv + $pmt * $nper + $fv;
	$y1 = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;

	// find root by secant method 
	$i = 0;
	$x0 = 0.0;
	$x1 = $rate;
	while ((abs($y0 - $y1) > FINANCIAL_PRECISION) && ($i < FINANCIAL_MAX_ITERATIONS)) {
		$rate = ($y1 * $x0 - $y0 * $x1) / ($y1 - $y0);
		$x0 = $x1;
		$x1 = $rate;

		if (abs($rate) < FINANCIAL_PRECISION) {
			$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
		} else {
			$f = exp($nper * log(1 + $rate));
			$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
		}

		$y0 = $y1;
		$y1 = $y;
		++$i;
	}
	return $rate * 12 * 100;
}   //  function RATE()


/**
 * Copy of Excel's PMT function.
 * Credit: http://thoughts-of-laszlo.blogspot.nl/2012/08/complete-formula-behind-excels-pmt.html
 *
 * @param double $interest        The interest rate for the loan.
 * @param int    $num_of_payments The total number of payments for the loan in months.
 * @param double $PV              The present value, or the total amount that a series of future payments is worth now;
 *                                Also known as the principal.
 * @param double $FV              The future value, or a cash balance you want to attain after the last payment is made.
 *                                If fv is omitted, it is assumed to be 0 (zero), that is, the future value of a loan is 0.
 * @param int    $Type            Optional, defaults to 0. The number 0 (zero) or 1 and indicates when payments are due.
 *                                0 = At the end of period
 *                                1 = At the beginning of the period
 *
 * @return float
 */
function PMT($interest, $num_of_payments, $PV, $FV = 0.00, $Type = 0)
{
	$xp = pow((1 + $interest), $num_of_payments);
	return
		($PV * $interest * $xp / ($xp - 1) + $interest / ($xp - 1) * $FV) *
		($Type == 0 ? 1 : 1 / ($interest + 1));
}

function minus($num)
{
	$x = -1;
	$num = $num * $x;
	return $num;
}

function round_up($value, $places)
{
	$mult = pow(10, abs($places));
	return $places < 0 ?
		ceil($value / $mult) * $mult :
		ceil($value * $mult) / $mult;
}

if ($objConnect) {
	/*
	LoanType
	Gender
	BirthDate
	IDcard
	LoanAmount
	InterestRate
	LoanTerm

	rand=0.8857960675943353&LoanType=AIA&Gender=male&BirthDate=01-06-2522&IDcard=undefined&AppDate=15-08-2562&LoanAmount=200000&InterestRate=8&LoanTerm=60
	CHUBB
	*/

	/*$LoanType = trim($_POST["LoanType"]);
	$Gender = trim($_POST["Gender"]);
	$BirthDate = trim($_POST["BirthDate"]);
	$IDcard = trim($_POST["IDcard"]); $IDcard = "";
	$AppDate = trim($_POST["AppDate"]);
	$sLoanTerm = trim($_POST["LoanTerm"]);  //ӹǹǴ
	$LoanAmount = trim($_POST["LoanAmount"]);
	$InterestRate = trim($_POST["InterestRate"]);*/
	$LoanType = trim($_REQUEST["LoanType"] ?? '');
	$Gender = trim($_REQUEST["Gender"] ?? '');
	$BirthDate = trim($_REQUEST["BirthDate"] ?? '');
	$IDcard = trim($_REQUEST["IDcard"] ?? '');
	$IDcard = "";
	$AppDate = trim($_REQUEST["AppDate"] ?? '');
	$sLoanTerm = trim($_REQUEST["LoanTerm"] ?? '');  //จำนวนงวด
	$LoanAmount = trim($_REQUEST["LoanAmount"] ?? '');
	$InterestRate = trim($_REQUEST["InterestRate"] ?? '');

	$Interest = ($LoanAmount * ($InterestRate / 100) * $sLoanTerm) / 12;
	$vInterest = number_format(round($Interest, 2), 2, ".", "");
	//echo "<br>vInterest=".$vInterest;

	$LoanAmount_Interest = number_format(($LoanAmount + $vInterest), 2, ".", "");
	//echo "<br>LoanAmount_Interest=".$LoanAmount_Interest;

	$LoanVat = ($LoanAmount_Interest * 7) / 100;
	$vLoanVat = number_format(round($LoanVat, 2), 2, ".", "");
	//echo "<br>vLoanVat=".$vLoanVat;

	$LoanAmount_LoanVat = number_format(($LoanAmount_Interest + $vLoanVat), 2, ".", "");
	//echo "<br>LoanAmount_LoanVat=".$LoanAmount_LoanVat;

	$MonthlyInstallment = $LoanAmount_LoanVat / $sLoanTerm;
	$MonthlyInstallment = ceil($MonthlyInstallment);
	//echo "<br>MonthlyInstallment=".$MonthlyInstallment;

	//BirthDate=12-02-2523
	$age = 0;
	$sdd = substr($BirthDate, 0, 2);
	$smm = substr($BirthDate, 3, 2);
	$syy = (int) substr($BirthDate, 6, 4) - 543;
	//echo $sdd."-".$smm."-".$syy;
	$iBirthDate = $smm . "" . $sdd;

	$ndd = substr($AppDate, 0, 2);
	$nmm = substr($AppDate, 3, 2);
	$nyy = (int) substr($AppDate, 6, 4) - 543;
	//echo $ndd."-".$nmm."-".$nyy;
	$inowDate = $nmm . "" . $ndd;
	if ($inowDate >= $iBirthDate) {
		$age = $nyy - $syy;
	} else {
		$age = ($nyy - $syy) - 1;
	}
	//echo "<br>age=".$age;

	// Get Rate
	if ($LoanType == "NO") {
		$insuType = "00";
	} elseif ($LoanType == "CHUBB") {
		$insuType = "02";
	} elseif ($LoanType == "TLIFE") {
		$insuType = "03";
	} else {
		$insuType = "01";
	}
	if ($Gender == "male") {
		$sGender = "1";
	} else {
		$sGender = "0";
	}

	$loanInterest = ($LoanAmount * ($InterestRate / 100) * $sLoanTerm) / 12;  //�͡�����ʹ�Ѵ��ҫ���
	$vloanInterest = number_format(round($loanInterest, 2), 2, ".", "");
	//echo "<br>newLoanAmount=".$newLoanAmount;

	$sumLoanAmount = $LoanAmount + $vloanInterest; //�ʹ�Ѵ��ҫ�������͡����

	$vat_sumLoanAmount = ($sumLoanAmount * 7) / 100; //VAT�ʹ�Ѵ��ҫ�������͡����
	$sumLoanAmount = $LoanAmount + $vloanInterest; //ʹѴҫ͡

	$vat_sumLoanAmount = ($sumLoanAmount * 7) / 100; //VATʹѴҫ͡
	$svat_sumLoanAmount = number_format($vat_sumLoanAmount, 2, ".", "");

	$approveLoanAmount = $sumLoanAmount + $svat_sumLoanAmount; //ʹ͹ѵҫ (عСѹ)
	$sapproveLoanAmount = number_format(($approveLoanAmount), 2, ".", "");
	//echo "<br>approveLoanAmount=".$approveLoanAmount;

	### อัตราค้ำประกัน ###
	$sqlQuery = "SELECT Fnc_LoanProtect_Rate ('" . $insuType . "','" . $sGender . "','" . $sLoanTerm . "','" . $age . "','" . $nyy . $nmm . $ndd . "') AS RATE";
	//echo "<br>SQL Query: " . $sqlQuery . "<br>";
	$sql = sqlQuery($sqlQuery);

	// Debug: Check for errors
	if ($sql === false) {
		//echo "<br><b>SQL Error:</b> " . mysqli_error($objConnect) . "<br>";
	}

	$insuRate = 0; // Default value if query fails
	$totalRows = sqlNumRows($sql);
	//echo "<br>totalRows=" . $totalRows . "<br>";
	if ($totalRows > 0) {
		while ($rows = sqlFetch($sql)) {
			$insuRate = number_format(trim($rows['RATE']), 2, ".", ""); // 16
		}
	}
	//echo "<br>0-insuRate=".$insuRate;

	### ������»�Сѹ ###
	$PPIamount = ($LoanAmount_LoanVat * $insuRate) / 100;
	//$PPIamount = round($PPIamount,0);
	$PPIamount = round(round($PPIamount, 2), 0);
	//echo "<br>1-PPIamount=".$PPIamount;

	$newLoanAmount = number_format(($LoanAmount + $PPIamount), 2, ".", "");  //�ʹ�Ѵ��ҫ���������»�Сѹ
	//echo "<br>newLoanAmount=".$newLoanAmount;

	$vat_newLoanAmount = ($newLoanAmount * 7) / 100;
	$svat_newLoanAmount = number_format($vat_newLoanAmount, 2, ".", "");
	$sum_newLoanAmount = $newLoanAmount + $svat_newLoanAmount;
	//echo "<br>svat_newLoanAmount=".$svat_newLoanAmount;

	$newInterest = ($newLoanAmount * ($InterestRate / 100) * $sLoanTerm) / 12;  //�͡����-�ʹ�Ѵ��ҫ���������»�Сѹ
	$vNewInterest = number_format(round($newInterest, 2), 2, ".", "");
	//echo "<br>vNewInterest=".$vNewInterest;

	$vat_newInterest = ($newInterest * 7) / 100;
	$svat_newInterest = number_format(round($vat_newInterest, 2), 2, ".", "");
	$sum_newInterest = $vNewInterest + $svat_newInterest;
	//echo "<br>sum_newInterest=".$sum_newInterest;

	$newLoanAmountWithInterest = $newLoanAmount + $vNewInterest;  //�ʹ͹��ѵ���ҫ���������»�Сѹ
	//echo "<br>newLoanAmountWithInterest=".$newLoanAmountWithInterest;

	$vat_newLoanAmountWithInterest = ($newLoanAmountWithInterest * 7) / 100;
	$svat_newLoanAmountWithInterest = number_format(round($vat_newLoanAmountWithInterest, 2), 2, ".", "");
	$sum_newLoanAmountWithInterest = number_format(round(ceil($newLoanAmountWithInterest + $svat_newLoanAmountWithInterest), 0), 2, ".", "");
	//echo "<br>sum_newLoanAmountWithInterest=".$sum_newLoanAmountWithInterest;
	//=====
	$newLoanAmountwithVat = $sum_newLoanAmountWithInterest;
	//echo "<br>newLoanAmountwithVat=".$newLoanAmountwithVat;

	//echo $sum_newLoanAmountWithInterest."/".$sLoanTerm."<br>";
	$newMonthlyInstallment = $sum_newLoanAmountWithInterest / $sLoanTerm;  //��ҧǴ �Ǵ�á
	$newMonthlyInstallment_noROUND = $newMonthlyInstallment;
	//echo "newMonthlyInstallment=".$newMonthlyInstallment."<br>";
	$newMonthlyInstallment = round_up(ceil($newMonthlyInstallment), -1);
	//echo "newMonthlyInstallment=".$newMonthlyInstallment."<br>";

	$vat_newMonthlyInstallmentNoVat = ($newMonthlyInstallment * 7) / 107;
	$vat_newMonthlyInstallmentNoVat_noROUND = $vat_newMonthlyInstallmentNoVat;
	$svat_newMonthlyInstallmentNoVat = number_format(round($vat_newMonthlyInstallmentNoVat, 2), 2, ".", "");
	//echo "<br>svat_newMonthlyInstallmentNoVat=".$svat_newMonthlyInstallmentNoVat;

	$newMonthlyInstallmentNoVat = $newMonthlyInstallment - $vat_newMonthlyInstallmentNoVat;
	$newMonthlyInstallmentNoVat = round($newMonthlyInstallmentNoVat, 2);  //��ҧǴ������ VAT
	//echo "<br>newMonthlyInstallmentNoVat=".$newMonthlyInstallmentNoVat;

	$newLastMonthlyInstallment = $sum_newLoanAmountWithInterest - ($newMonthlyInstallment * ($sLoanTerm - 1));  // ��ҧǴ �Ǵ�ش����

	$vat_newLastMonthlyInstallmentNoVat = ($newLastMonthlyInstallment * 7) / 107;
	$svat_newLastMonthlyInstallmentNoVat = number_format(round($vat_newLastMonthlyInstallmentNoVat, 2), 2, ".", "");
	//echo "<br>svat_newMonthlyInstallmentNoVat=".$svat_newMonthlyInstallmentNoVat;

	$newLastMonthlyInstallmentNoVat = $newLastMonthlyInstallment - $vat_newLastMonthlyInstallmentNoVat;
	$newLastMonthlyInstallmentNoVat = round($newLastMonthlyInstallmentNoVat, 2);
	//echo "<br>newMonthlyInstallmentNoVat=".$newMonthlyInstallmentNoVat;

	$newMonthlyInstallmentNoVat_noROUND = $newLoanAmountWithInterest / $sLoanTerm;
	$xRate = RATE($sLoanTerm, -$newMonthlyInstallmentNoVat_noROUND, $newLoanAmount);
	$xRate = round($xRate, 4);

	echo $insuRate . "|" . number_format($PPIamount) . "|" . number_format($LoanAmount, 2) . "|" . number_format($newLoanAmount, 2) . "|" . number_format($svat_newLoanAmount, 2) . "|" . number_format($sum_newLoanAmount, 2) . "|" . number_format($vNewInterest, 2) . "|" . number_format($svat_newInterest, 2) . "|" . number_format($sum_newInterest, 2) . "|" . number_format($newLoanAmountWithInterest, 2) . "|" . number_format($svat_newLoanAmountWithInterest, 2) . "|" . number_format($sum_newLoanAmountWithInterest, 2) . "|" . number_format($newMonthlyInstallmentNoVat, 2) . "|" . number_format($svat_newMonthlyInstallmentNoVat, 2) . "|" . number_format($newMonthlyInstallment, 2) . "|" . number_format($InterestRate, 2) . "|" . $sLoanTerm . "|" . $LoanType . "|" . $IDcard . "|" . $BirthDate . "|" . number_format($newLastMonthlyInstallment, 2) . "|" . number_format($svat_newLastMonthlyInstallmentNoVat, 2) . "|" . number_format($newLastMonthlyInstallmentNoVat, 2) . "|" . $AppDate . "|" . $age . "|" . $Gender . "|" . number_format($xRate, 2, ".", "") . "|" . $newMonthlyInstallmentNoVat_noROUND . "|" . number_format($sapproveLoanAmount, 2);

	sqlClose($objConnect);
}
?>