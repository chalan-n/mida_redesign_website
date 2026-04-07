<?php
session_start();
date_default_timezone_set('Asia/Bangkok'); // ให้ผลลัพธ์ตรงกับ PHP 5
include("includes/config.php");
include("includes/dateThai.php");

define('FINANCIAL_MAX_ITERATIONS', 128);
define('FINANCIAL_PRECISION', 0.0000001);

function RATE($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1)
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
	$i = $x0 = 0.0;
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
	//return $rate;
	return $rate * 100;
}

function calPMT($apr, $term, $loan)
{
	$term = $term * 12;
	$apr = $apr / 1200;
	$amount = $apr * -$loan * pow((1 + $apr), $term) / (1 - pow((1 + $apr), $term));
	return round($amount);
}

function datediff($start, $end)
{
	$datediff = strtotime($end) - strtotime($start);
	return floor($datediff / (60 * 60 * 24));
}

function round_up($value, $places)
{
	$mult = pow(10, abs($places));
	return $places < 0 ?
		ceil($value / $mult) * $mult :
		ceil($value * $mult) / $mult;
}

$LoanAmount = trim($_REQUEST["LoanAmount"]);
$InterestRate = trim($_REQUEST["InterestRate"]);
$LoanTerm = trim($_REQUEST["LoanTerm"]);
$AppDate = trim($_REQUEST["AppDate"]);
if ($AppDate != "") {
	$ndd = substr($AppDate, 0, 2);
	$nmm = substr($AppDate, 3, 2);
	$nyy = (int) substr($AppDate, 6, 4) - 543;
	//echo $ndd."-".$nmm."-".$nyy;
	$date_current = $nyy . "-" . $nmm . "-" . $ndd;
}

$result1 = (1 / pow((1 + (($InterestRate / 100) / 12)), $LoanTerm));
$result2 = (1 - $result1) / (($InterestRate / 100) / 12);
//$LaonPmt = round_up(($LoanAmount/$result2),0);
$LaonPmt = ceil(($LoanAmount / $result2) / 10) * 10;
//echo $resultPmt;

//$xRate = calPMT($InterestRate,$LoanTerm,$LoanAmount);
$xRate = RATE($LoanTerm, -$LaonPmt, $LoanAmount);
$xRate = round($xRate, 2) * 12;
$xRate = round($xRate);
//echo $xRate."<br>";

//$date_current = date("Y-m-d");
$date_nextmonth = date('Y-m-d', strtotime($date_current . "+1 months"));
$totalday = datediff($date_current, $date_nextmonth);
//echo $totalday."<br>";

$sumInterest = 0;
$sLoanAmount = $LoanAmount;
$resultPmt = $LaonPmt;

$paymentdetail = "";

//echo "Start => ".$date_current."<br>";
//echo "งวดที่ , วันดิว , ค่างวด , ชำระเงินต้น , ชำระดอกเบี้ย , เงินต้นคงเหลือ <br>";

for ($i = 1; $i <= $LoanTerm; $i++) {

	$calInterest = $sLoanAmount * (($xRate / 100) / 365) * $totalday;
	$sumInterest += round($calInterest, 2);

	if ($LoanTerm == $i) {
		$payPrincipal = $sLoanAmount;
		$resultPmt = $payPrincipal + $calInterest;
		$sLoanAmount = 0;
		//echo $i."==".$LoanTerm." => ".$resultPmt."<br>";
	} else {
		$payPrincipal = round(($resultPmt - $calInterest), 2);
		$sLoanAmount = $sLoanAmount - $payPrincipal;
	}

	//งวดที่ , ค่างวด , ชำระเงินต้น , ชำระดอกเบี้ย , เงินต้นคงเหลือ
	//echo $i." , ".$date_nextmonth." => ".number_format($resultPmt, 2, '.', ',')." , ".number_format($payPrincipal, 2, '.', ',')." , ".number_format($calInterest, 2, '.', ',')." , ".number_format($sLoanAmount, 2, '.', ',')." , ".$totalday."<br>";
	$paymentdetail .= $i . ":" . number_format($resultPmt, 2, '.', ',') . ":" . number_format($payPrincipal, 2, '.', ',') . ":" . number_format($calInterest, 2, '.', ',') . ":" . number_format($sLoanAmount, 2, '.', ',') . "|";

	$date_current = $date_nextmonth;
	$date_nextmonth = date('Y-m-d', strtotime($date_current . "+1 months"));
	$totalday = datediff($date_current, $date_nextmonth);

}

//echo $LoanAmount." = ".$sumInterest;
echo number_format($LoanAmount, 2, '.', ',') . "#" . number_format($sumInterest, 2, '.', ',') . "#" . number_format(($LoanAmount + $sumInterest), 2, '.', ',') . "#" . number_format($LaonPmt, 2, '.', ',') . "@" . $paymentdetail;