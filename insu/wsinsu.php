<?php
session_start();
include("includes/config.php");
include("includes/dateThai.php");

function round_up($value, $places) 
{
    $mult = pow(10, abs($places)); 
     return $places < 0 ?
    ceil($value / $mult) * $mult :
        ceil($value * $mult) / $mult;
}

if($objConnect)
{
	/*
	LoanType
	Gender
	BirthDate
	IDcard
	LoanAmount
	InterestRate
	LoanTerm
	
	AIA
	rand=0.8857960675943353&LoanType=AIA&Gender=male&BirthDate=01-06-2522&IDcard=undefined&AppDate=15-08-2562&LoanAmount=200000&InterestRate=8&LoanTerm=60

	CHUBB
	rand=0.8857960675943353&LoanType=CHUBB&Gender=male&BirthDate=01-06-2522&IDcard=undefined&AppDate=15-08-2562&LoanAmount=200000&InterestRate=8&LoanTerm=60
	
	*/
	
	$LoanType = trim($_REQUEST["LoanType"]);
	$Gender = trim($_REQUEST["Gender"]);
	$BirthDate = trim($_REQUEST["BirthDate"]);	
	$IDcard = trim($_REQUEST["IDcard"]);
	$AppDate = trim($_REQUEST["AppDate"]);
	$sLoanTerm = trim($_REQUEST["LoanTerm"]);
	$LoanAmount = trim($_REQUEST["LoanAmount"]);
	$InterestRate = trim($_REQUEST["InterestRate"]);
	
	$Interest = ($LoanAmount*($InterestRate/100)*$sLoanTerm)/12;
	$vInterest = number_format(round($Interest,2),2,".","");
	//echo "<br>vInterest=".$vInterest;
	
	$LoanAmount_Interest = number_format(($LoanAmount+$vInterest),2,".","");
	//echo "<br>LoanAmount_Interest=".$LoanAmount_Interest;
	
	$LoanVat = ($LoanAmount_Interest*7)/100;
	$vLoanVat = number_format(round($LoanVat,2),2,".","");
	//echo "<br>vLoanVat=".$vLoanVat;
	
	$LoanAmount_LoanVat = number_format(($LoanAmount_Interest+$vLoanVat),2,".","");
	//echo "<br>LoanAmount_LoanVat=".$LoanAmount_LoanVat;
	
	$MonthlyInstallment = $LoanAmount_LoanVat/$sLoanTerm;
	$MonthlyInstallment = ceil($MonthlyInstallment);
	//echo "<br>MonthlyInstallment=".$MonthlyInstallment;
	
	//BirthDate=12-02-2523
	$age = 0;
	$sdd = substr($BirthDate,0,2);
	$smm = substr($BirthDate,3,2);
	//$syy = (int)substr($BirthDate,6,4)-543;
	$syy = substr($BirthDate,6,4);
	//echo $sdd."-".$smm."-".$syy;
	$iBirthDate = $smm."".$sdd;

	$ndd = substr($AppDate,0,2);
	$nmm = substr($AppDate,3,2);
	//$nyy = (int)substr($AppDate,6,4)-543;
	$nyy = substr($AppDate,6,4);
	//echo $ndd."-".$nmm."-".$nyy;
	$inowDate = $nmm."".$ndd;
	if($inowDate >= $iBirthDate){
		$age = $nyy-$syy;
	}else{
		$age = ($nyy-$syy)-1;
	}
	//echo "<br>age=".$age;
	
	// Get Rate
	if($LoanType == "CHUBB"){
		$insuType = "02";
	}else{
		$insuType = "01";
	}
	if($Gender == "male"){
		$sGender = "1";
		$textGender = "ชาย";
	}else{
		$sGender = "0";
		$textGender = "หญิง";
	}
	$sql = sqlQuery("SELECT Fnc_LoanProtect_Rate ('".$insuType."','".$sGender."','".$sLoanTerm."','".$age."','".$nyy.$nmm.$ndd."') AS RATE");
	//echo "<br>select dbo.fnc_LoanProtect_rate('".$insuType."','".$sGender."','".$sLoanTerm."','".$age."','".$nyy.$nmm.$ndd."') as RATE";
	$totalRows = sqlNumRows($sql);
	if($totalRows>0){
		while($rows = sqlFetch($sql)){
			$insuRate = number_format(trim($rows['RATE']),2,".",""); // 16
		}
	}
	//echo "<br>insuRate=".$insuRate;
	
	$PPIamount = ($LoanAmount_LoanVat*$insuRate)/100;
	$PPIamount = round($PPIamount,0);
	//echo "<br>PPIamount=".$PPIamount;
	
	$newLoanAmount = number_format(($LoanAmount+$PPIamount),2,".","");
	//echo "<br>newLoanAmount=".$newLoanAmount;
	$vat_newLoanAmount = ($newLoanAmount*7)/100;
	$svat_newLoanAmount = number_format($vat_newLoanAmount,2,".","");	
	$sum_newLoanAmount = $newLoanAmount+$svat_newLoanAmount;
	//echo "<br>svat_newLoanAmount=".$svat_newLoanAmount;
	
	$newInterest = ($newLoanAmount*($InterestRate/100)*$sLoanTerm)/12;
	$vNewInterest = number_format(round($newInterest,2),2,".","");
	//echo "<br>vNewInterest=".$vNewInterest;
	$vat_newInterest = ($newInterest*7)/100;
	$svat_newInterest = number_format(round($vat_newInterest,2),2,".","");	
	$sum_newInterest = $vNewInterest+$svat_newInterest;
	//echo "<br>sum_newInterest=".$sum_newInterest;
	
	$newLoanAmountWithInterest = $newLoanAmount+$vNewInterest;
	//echo "<br>newLoanAmountWithInterest=".$newLoanAmountWithInterest;
	$vat_newLoanAmountWithInterest = ($newLoanAmountWithInterest*7)/100;
	$svat_newLoanAmountWithInterest = number_format(round($vat_newLoanAmountWithInterest,2),2,".","");	
	$sum_newLoanAmountWithInterest = number_format(round(ceil($newLoanAmountWithInterest+$svat_newLoanAmountWithInterest),0),2,".","");
	//echo "<br>sum_newLoanAmountWithInterest=".$sum_newLoanAmountWithInterest;
	//=====
	$newLoanAmountwithVat = $sum_newLoanAmountWithInterest;
	//echo "<br>newLoanAmountwithVat=".$newLoanAmountwithVat;
	
	//echo $sum_newLoanAmountWithInterest."/".$sLoanTerm."<br>";
	$newMonthlyInstallment = $sum_newLoanAmountWithInterest/$sLoanTerm;	
	//echo "newMonthlyInstallment=".$newMonthlyInstallment."<br>";
	$newMonthlyInstallment = round_up(ceil($newMonthlyInstallment),-1);
	//echo "newMonthlyInstallment=".$newMonthlyInstallment."<br>";
	
	$vat_newMonthlyInstallmentNoVat = ($newMonthlyInstallment*7)/107;
	$svat_newMonthlyInstallmentNoVat = number_format(round($vat_newMonthlyInstallmentNoVat,2),2,".","");
	//echo "<br>svat_newMonthlyInstallmentNoVat=".$svat_newMonthlyInstallmentNoVat;
	
	$newMonthlyInstallmentNoVat = $newMonthlyInstallment-$vat_newMonthlyInstallmentNoVat;
	$newMonthlyInstallmentNoVat = round($newMonthlyInstallmentNoVat,2);
	//echo "<br>newMonthlyInstallmentNoVat=".$newMonthlyInstallmentNoVat;
	
	$newLastMonthlyInstallment = $sum_newLoanAmountWithInterest-($newMonthlyInstallment*($sLoanTerm-1));
	
	$vat_newLastMonthlyInstallmentNoVat = ($newLastMonthlyInstallment*7)/107;
	$svat_newLastMonthlyInstallmentNoVat = number_format(round($vat_newLastMonthlyInstallmentNoVat,2),2,".","");
	//echo "<br>svat_newMonthlyInstallmentNoVat=".$svat_newMonthlyInstallmentNoVat;
	
	$newLastMonthlyInstallmentNoVat = $newLastMonthlyInstallment-$vat_newLastMonthlyInstallmentNoVat;
	$newLastMonthlyInstallmentNoVat = round($newLastMonthlyInstallmentNoVat,2);
	//echo "<br>newMonthlyInstallmentNoVat=".$newMonthlyInstallmentNoVat;
	
	$arrData = array();
	$colData = array();

	$colData["insuRate"] = $insuRate;
	$colData["PPIamount"] = number_format($PPIamount);
	$colData["LoanAmount"] = number_format($LoanAmount);
	$colData["newLoanAmount"] = number_format($newLoanAmount,2);
	$colData["svat_newLoanAmount"] = number_format($svat_newLoanAmount,2);
	$colData["sum_newLoanAmount"] = number_format($sum_newLoanAmount,2);
	$colData["vNewInterest"] = number_format($vNewInterest,2);
	$colData["svat_newInterest"] = number_format($svat_newInterest,2);
	$colData["sum_newInterest"] = number_format($sum_newInterest,2);
	$colData["newLoanAmountWithInterest"] = number_format($newLoanAmountWithInterest,2);
	$colData["svat_newLoanAmountWithInterest"] = number_format($svat_newLoanAmountWithInterest,2);
	$colData["sum_newLoanAmountWithInterest"] = number_format($sum_newLoanAmountWithInterest,2);
	$colData["newMonthlyInstallmentNoVat"] = number_format($newMonthlyInstallmentNoVat,2);
	$colData["svat_newMonthlyInstallmentNoVat"] = number_format($svat_newMonthlyInstallmentNoVat,2);
	$colData["newMonthlyInstallment"] = number_format($newMonthlyInstallment,2);
	$colData["InterestRate"] = number_format($InterestRate,2);
	$colData["sLoanTerm"] = $sLoanTerm;
	$colData["LoanType"] = $LoanType;
	$colData["IDcard"] = $IDcard;
	$colData["BirthDate"] = $BirthDate;
	$colData["newLastMonthlyInstallment"] = number_format($newLastMonthlyInstallment,2);
	$colData["svat_newLastMonthlyInstallmentNoVat"] = number_format($svat_newLastMonthlyInstallmentNoVat,2);
	$colData["newLastMonthlyInstallmentNoVat"] = number_format($newLastMonthlyInstallmentNoVat,2);
	$colData["AppDate"] = $AppDate;
	$colData["age"] = $age;
	$colData["Gender"] = $textGender;

	$arrData[] = $colData;

	//$arryRep = array("[","]");	
	//echo str_replace($arryRep,"",json_encode($arrData));
	echo json_encode($arrData);

	/*echo $insuRate."|".number_format($PPIamount)."|".number_format($LoanAmount)."|".number_format($newLoanAmount,2)."|".number_format($svat_newLoanAmount,2)."|".number_format($sum_newLoanAmount,2)."|".number_format($vNewInterest,2)."|".number_format($svat_newInterest,2)."|".number_format($sum_newInterest,2)."|".number_format($newLoanAmountWithInterest,2)."|".number_format($svat_newLoanAmountWithInterest,2)."|".number_format($sum_newLoanAmountWithInterest,2)."|".number_format($newMonthlyInstallmentNoVat,2)."|".number_format($svat_newMonthlyInstallmentNoVat,2)."|".number_format($newMonthlyInstallment,2)."|".number_format($InterestRate,2)."|".$sLoanTerm."|".$LoanType."|".$IDcard."|".$BirthDate."|".number_format($newLastMonthlyInstallment,2)."|".number_format($svat_newLastMonthlyInstallmentNoVat,2)."|".number_format($newLastMonthlyInstallmentNoVat,2)."|".$AppDate."|".$age."|".$Gender;*/
	
	sqlClose($objConnect);
}
?>