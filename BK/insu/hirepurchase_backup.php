<?php
session_start();
if($_SESSION['sess_login']==""){ header("Location: login.php"); exit();}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="windows-874">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>คำนวณสินเชื่อ</title> 
    <link rel="stylesheet" href="css/normalize.min.css">    
	<link rel="stylesheet" href="css/jquery-ui.css">
    <!--[if lt IE 9]><script src="js/ie8-responsive-file-warning.js"></script><![endif]-->
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="js/html5shiv.min.js"></script>
  <script src="js/respond.min.js"></script>
<![endif]-->
  	<link rel="stylesheet" href="css/datepicker3.css">
    <link href='css/style.css' rel='stylesheet' type='text/css'>
	<link href="css/font-awesome.css" rel="stylesheet">
      <style>
      /* NOTE: The styles were added inline because Prefixfree needs access to your styles and they must be inlined if they are on local disk! */
      body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, 
pre, form, fieldset, input, textarea, p, blockquote, th, td { 
  padding:0;
  margin:0;}

fieldset, img {border:0}

ol, ul, li {list-style:none}

:focus {outline:none}

body,
input,
textarea,
select {
  font-family: 'Open Sans', sans-serif;
  font-size: 16px;
  color: #4c4c4c;
}

p {
  font-size: 12px;
  width: 150px;
  display: inline-block;
  margin-left: 18px;
}
h1 {
  font-size: 32px;
  /*font-weight: 300;*/
  color: #4c4c4c;
  text-align: center;
  padding-top: 10px;
  margin-bottom: 10px;
}

html{
  background-color: #ffffff;
}

.databox {
  margin: 20px auto;
    width: 343px;
   height: 635px;
  -webkit-border-radius: 8px/7px; 
  -moz-border-radius: 8px/7px; 
  border-radius: 8px/7px; 
  background-color: #ebebeb; 
  -webkit-box-shadow: 1px 2px 5px rgba(0,0,0,.31); 
  -moz-box-shadow: 1px 2px 5px rgba(0,0,0,.31); 
  box-shadow: 1px 2px 5px rgba(0,0,0,.31); 
  border: solid 1px #cbc9c9;
}

input[type=radio] {
  visibility: hidden;
}

form{
  margin: 0 25px;
}

label.radio {
	cursor: pointer;
  text-indent: 35px;
  overflow: visible;
  display: inline-block;
  position: relative;
  margin-bottom: 15px;
}

label.radio:before {
  background: #3a57af;
  content:'';
  position: absolute;
  top:2px;
  left: 0;
  width: 20px;
  height: 20px;
  border-radius: 100%;
}

label.radio:after {
	opacity: 0;
	content: '';
	position: absolute;
	width: 0.5em;
	height: 0.25em;
	background: transparent;
	top: 7.5px;
	left: 4.5px;
	border: 3px solid #ffffff;
	border-top: none;
	border-right: none;

	-webkit-transform: rotate(-45deg);
	-moz-transform: rotate(-45deg);
	-o-transform: rotate(-45deg);
	-ms-transform: rotate(-45deg);
	transform: rotate(-45deg);
}

input[type=radio]:checked + label:after {
	opacity: 1;
}

hr{
  color: #a9a9a9;
  opacity: 0.3;
}

input[type=text],input[type=password],input[type=number],select{
  width: 200px; 
  height: 39px; 
  -webkit-border-radius: 0px 4px 4px 0px/5px 5px 4px 4px; 
  -moz-border-radius: 0px 4px 4px 0px/0px 0px 4px 4px; 
  border-radius: 0px 4px 4px 0px/5px 5px 4px 4px; 
  background-color: #fff; 
  -webkit-box-shadow: 1px 2px 5px rgba(0,0,0,.09); 
  -moz-box-shadow: 1px 2px 5px rgba(0,0,0,.09); 
  box-shadow: 1px 2px 5px rgba(0,0,0,.09); 
  border: solid 1px #cbc9c9;
  margin-left: -5px;
  margin-top: 13px; 
  padding-left: 10px;
}

input[type=password]{
  margin-bottom: 25px;
}

#icon {
  display: inline-block;
  width: 30px;
  background-color: #3a57af;
  padding: 8px 0px 8px 15px;
  margin-left: 15px;
  -webkit-border-radius: 4px 0px 0px 4px; 
  -moz-border-radius: 4px 0px 0px 4px; 
  border-radius: 4px 0px 0px 4px;
  color: white;
  -webkit-box-shadow: 1px 2px 5px rgba(0,0,0,.09);
  -moz-box-shadow: 1px 2px 5px rgba(0,0,0,.09); 
  box-shadow: 1px 2px 5px rgba(0,0,0,.09); 
  border: solid 0px #cbc9c9;
}

.gender {
  margin-left: 30px;
  margin-bottom: 30px;
}

.accounttype{
  margin-left: 8px;
  margin-top: 15px;
  margin-bottom: 15px;
}

.button {
  font-size: 15px;
  font-weight: 600;
  color: white;
  padding: 6px 25px 0px 20px;
  margin: auto 8px 20px 0px;
  display: inline-block;
  float: right;
  text-decoration: none;
  width: 98px; 
  height: 27px; 
  -webkit-border-radius: 5px; 
  -moz-border-radius: 5px; 
  border-radius: 5px; 
  background-color: #3a57af; 
  -webkit-box-shadow: 0 3px rgba(58,87,175,.75); 
  -moz-box-shadow: 0 3px rgba(58,87,175,.75); 
  box-shadow: 0 3px rgba(58,87,175,.75);
  transition: all 0.1s linear 0s; 
  top: 0px;
  position: relative;
}

.button:hover {
  top: 3px;
  background-color:#2e458b;
  -webkit-box-shadow: none; 
  -moz-box-shadow: none; 
  box-shadow: none;
  
}

.inputbutton {
  font-size: 15px;
  font-weight: 600;
  color: white;
  padding: 5px 25px;
  margin: 20px 55px;
  display: inline-block;
  /*float: right;*/
  text-decoration: none;
 /* width: 98px; */
  line-height: 25px; 
  -webkit-border-radius: 5px; 
  -moz-border-radius: 5px; 
  border-radius: 5px; 
  background-color: #3a57af; 
  -webkit-box-shadow: 0 3px rgba(58,87,175,.75); 
  -moz-box-shadow: 0 3px rgba(58,87,175,.75); 
  box-shadow: 0 1px rgba(58,87,175,.75);
  transition: all 0.1s linear 0s; 
  top: 0px;
  position: relative;
}

.inputbutton:hover {
  top: 3px;
  background-color:#2e458b;
  -webkit-box-shadow: none; 
  -moz-box-shadow: none; 
  box-shadow: none;
  
}

.outputbox {
  margin: 20px auto;
  width: 343px; 
  /*height: 554px; */
  -webkit-border-radius: 8px/7px; 
  -moz-border-radius: 8px/7px; 
  border-radius: 8px/7px; 
  background-color: #ebebeb; 
  -webkit-box-shadow: 1px 2px 5px rgba(0,0,0,.31); 
  -moz-box-shadow: 1px 2px 5px rgba(0,0,0,.31); 
  box-shadow: 1px 2px 5px rgba(0,0,0,.31); 
  border: solid 1px #cbc9c9;
}

.inputbutton2 {
  font-size: 15px;
  font-weight: 600;
  color: white;
  padding: 5px 25px;
  margin: 20px 8px 20px 0px;
  /*display: inline-block;
  float: right;*/
  text-decoration: none;
 /* width: 98px; */
  line-height: 25px; 
  -webkit-border-radius: 5px; 
  -moz-border-radius: 5px; 
  border-radius: 5px; 
  background-color: #3a57af; 
  -webkit-box-shadow: 0 3px rgba(58,87,175,.75); 
  -moz-box-shadow: 0 3px rgba(58,87,175,.75); 
  box-shadow: 0 1px rgba(58,87,175,.75);
  transition: all 0.1s linear 0s; 
  top: 0px;
  position: relative;
}

.inputbutton2:hover {
  top: 3px;
  background-color:#2e458b;
  -webkit-box-shadow: none; 
  -moz-box-shadow: none; 
  box-shadow: none;  
}

.buttonback {
	font-size: 15px;
    font-weight: 600;
    color: white;
    padding: 5px 25px;
    display: inline-block;
    text-decoration: none;
    line-height: 18px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    background-color: #3a57af;
    -webkit-box-shadow: 0 3px rgba(58,87,175,.75);
    -moz-box-shadow: 0 3px rgba(58,87,175,.75);
    box-shadow: 0 1px rgba(58,87,175,.75);
    transition: all 0.1s linear 0s;
    top: 0px;
    position: relative;
}
.buttonback:hover {
  top: 3px;
  background-color:#2e458b;
  -webkit-box-shadow: none; 
  -moz-box-shadow: none; 
  box-shadow: none;  
}

.buttonback2 {
	font-size: 15px;
    font-weight: 600;
    color: white;
    padding: 5px 25px;
    display: inline-block;
    text-decoration: none;
    line-height: 18px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    background-color: #3a57af;
    -webkit-box-shadow: 0 3px rgba(58,87,175,.75);
    -moz-box-shadow: 0 3px rgba(58,87,175,.75);
    box-shadow: 0 1px rgba(58,87,175,.75);
    transition: all 0.1s linear 0s;
    top: 0px;
    position: relative;
	margin-right: 10px;
	float: right;
}
.buttonback2:hover {
  top: 3px;
  background-color:#2e458b;
  -webkit-box-shadow: none; 
  -moz-box-shadow: none; 
  box-shadow: none;  
}

.errorClass { border:  1px solid #f00; }
.normalClass { border:  1px solid #cbc9c9; }
    </style>
    <script src="css/prefixfree.min.js"></script>
</head>

<body>
<div id="dvform" class="databox">
  <h1>
	<!--span style="float: left;margin: 3px 5px 0 10px;"><a href="index.php"><img src="images/go-home.png" border="0"></a></span-->
	คำนวณสินเชื่อ
	<span style="float: right;margin: -10px 5px 0 0;"><a href="index.php"><img src="images/close.png" border="0"></a></span>
  </h1>
  <form name="frm" id="frm" action="">
      <hr>
    <div class="accounttype">
            <label id="icon" for="name"><i class="icon-shield"></i></label>
            <input type="radio" value="TLIFE" id="radioOne" name="LoanType" checked />
            <label for="radioOne" class="radio">TLife</label>
			<input type="radio" value="NO" id="radioTwo" name="LoanType" />
            <label for="radioTwo" class="radio">ไม่ทำ</label>            
			<hr>
			<div id="dvGB">
            <label id="icon" for="name"><i class="icon-user"></i></label>
            <input type="radio" value="male" id="male" name="Gender" checked/>
            <label for="male" class="radio" chec>ชาย</label>
            <input type="radio" value="female" id="female" name="Gender" />
            <label for="female" class="radio">หญิง</label>
            <label id="icon" for="name"><i class="icon-calendar"></i></label>
            <input type="text" name="BirthDate" id="BirthDate" placeholder="วันเดือนปีเกิด" data-mask="00-00-0000" data-mask-selectonfocus="true" onkeypress="checknumber()" maxlength="10" />
            <div style="padding-left: 15px; color:#F00;font-size: 15px;">วัน/เดือน/ปีพ.ศ.  เกิด<br>เช่น 01-05-2533</div>
            <!--<label id="icon" for="name"><i class="icon-credit-card"></i></label>
            <input type="text" name="IDcard" id="IDcard" placeholder="เลขบัตรประชาชน" data-mask="0-0000-00000-0-00" data-mask-selectonfocus="true" onkeypress="checknumber()" maxlength="17" />-->
            <hr>
			</div>
            <label id="icon" for="name"><i class="icon-money"></i></label>
            <input type="text" name="LoanAmount" id="LoanAmount" placeholder="เงินต้นเช่าซื้อ" onkeypress="checknumber()" autocomplete="off" />
            <label id="icon" for="name"><i class="icon-star"></i></label>
            <input type="text" name="InterestRate" id="InterestRate" style="width: 35%;" placeholder="อัตราดอกเบี้ย" onkeypress="checknumber()" autocomplete="off" />&nbsp;%&nbsp;&nbsp;<span id="xRate" style="color: #f00; font-size: 15px; font-weight: bold;"></span><br>
            <label id="icon" for="name"><i class="icon-th-list"></i></label>
            <select name="LoanTerm" id="LoanTerm" required>
            	<option value="">จำนวนงวด</option>
                <?php
                for($i=1;$i<=6;$i++){
					echo '<option value="'.($i*12).'">'.($i*12).'</option>';
				}
				?>
            </select>
            <label id="icon" for="name"><i class="icon-calendar"></i></label>
            <input type="text" name="AppDate" id="AppDate" placeholder="วันที่เซ็นสัญญา" data-mask="00-00-0000" data-mask-selectonfocus="true" onkeypress="checknumber()" maxlength="10" value="<?=date("d")."-".date("m")."-".(date("Y")+543)?>" />
            <div style="padding-left: 15px; color:#F00;font-size: 15px;">วัน/เดือน/ปีพ.ศ. เซ็นสัญญา<br>เช่น 01-05-2533</div>
			<div id="msg" style="color: #F00; text-align: center; margin-left: -25px; margin-top: 15px;"></div>
            <button name="btncal" id="btncal" class="inputbutton" />คำนวณสินเชื่อ</button>			
  	</div>
    <input type="hidden" name="resdata" id="resdata">
  </form>
</div>
<div id="dvoutput" class="outputbox" style="display:none;">
   <div id="output" class="accounttype" style=""></div>   
</div>  
<script src="js/jquery-1.10.2.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="bootstrap/js/bootstrap-datepicker.js"></script>
<script src="bootstrap/js/bootstrap-datepicker-thai.js"></script>
<script src="bootstrap/js/locales/bootstrap-datepicker.th.js"></script>
<script src="js/jquery.mask.min.js" type="text/javascript"></script>
<script type="text/javascript">
function resetform() {
		$('#dvoutput').hide();
		$('#dvform').show();
		$('#resdata').val('');
		document.forms[0].reset();
}

function checknumber()
{
  key = event.keyCode;
  if ( key != 46 & ( key < 48 || key > 57 ) )
  {
    event.returnValue = false;
  }
}

function checkID(id)
{
	if(id.length != 13) return false;
	for(i=0, sum=0; i < 12; i++)
	sum += parseFloat(id.charAt(i))*(13-i); if((11-sum%11)%10!=parseFloat(id.charAt(12)))
	return false; return true;
}

function backform() {
		$('#dvoutput').hide();
		$('#dvform').show();
		//console.log($('#resdata').val());
		var arryData = $.trim($('#resdata').val()).split("|");
		/* ## arryData ##
		0 - insuRate=1.68														#0 - อัตราเบี้ยประกัน (%)
		1 - PPIamount=5483.0												#1 - ค่าเบี้ยประกัน
		2 - LoanAmount=200000.0											#2 - เงินต้นเช่าซื้อ

		3 - newLoanAmount=205483.0									#3 - ยอดจัดเช่าซื้อ
		4 - vat_newLoanAmount=14383.81								#4 - ภาษีมูลค่าเพิ่ม (VAT) ของยอดจัดเช่าซื้อ
		5 - sum_newLoanAmount=219866.81							#5 - ยอดจัดเช่าซื้อ + VAT

		6 - NewInterest=107878.58											#6 - ดอกเบี้ย
		7 - vat_newInterest=7551.50										#7 - ภาษีมูลค่าเพิ่ม (VAT) ของดอกเบี้ย
		8 - sum_newInterest=115430.08									#8 - ดอกเบี้ย + VAT

		9 - newLoanAmountWithInterest=313361.58					#9 - รวมยอดเช่าซื้อ
		10 - vat_newLoanAmountWithInterest=21935.31			#10 - ภาษีมูลค่าเพิ่ม (VAT) ของรวมยอดเช่าซื้อ
		11 - sum_newLoanAmountWithInterest=335296.89		#11 - รวมยอดเช่าซื้อ + VAT

		12 - newMonthlyInstallmentNoVat=5223.0					#12 - ค่างวด งวดแรก
		13 - vat_newMonthlyInstallmentNoVat=365.61				#13 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดแรก
		14 - newMonthlyInstallment=5590								#14 - ค่างวด งวดแรก + VAT

		15 - InterestRate = 10.5												#15 - อัตราดอกเบี้ย
		16 - LoanTerm = 60													#16 - จำนวนงวด
		17 - LoanType = CHUBB												#17 - บริษัทประกัน
		18 - IDcard = 123456789123										#18 - เลขบัตรประชาชน
		19 - BirthDate = 12-02-2523										#19 - วันเดือนปีเกิด

		20 - newLastMonthlyInstallment									#20 - ค่างวด งวดสุดท้าย			
		21 - vat_newLastMonthlyInstallmentNoVat						#21 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดสุดท้าย
		22 - newLastMonthlyInstallmentNoVat							#22 - ค่างวด งวดสุดท้าย	+ VAT

		23 - AppDate																#23 - วันเซ็นสัญญา
		24 - Age																	#24 - อายุ
		25 - Gender																#25 - เพศ
		26 - xRate																	#26 - อัตราดอกเบี้ย Effective rate
		27 - newMonthlyInstallmentNoVat_noROUND					#27 - ค่างวด ไม่ปัดเศษ
		28 - approveLoanAmount											#28 - ยอดอนุมัติเช่าซื้อ(1)
		*/

		//var EffRate = xRate(arryData[16], arryData[12], arryData[3]);

		$('#BirthDate').val(arryData[19]);
		$('#IDcard').val(arryData[18]);
		$('#LoanAmount').val(arryData[2].replace(",", ""));
		$('#InterestRate').val(arryData[15]);
		$('#LoanTerm').val(arryData[16]);
		$('#xRate').html("");
}

function detailform() {
		var arryData = $.trim($('#resdata').val()).split("|");
		/* ## arryData ##
		0 - insuRate=1.68														#0 - อัตราเบี้ยประกัน (%)
		1 - PPIamount=5483.0												#1 - ค่าเบี้ยประกัน
		2 - LoanAmount=200000.0											#2 - เงินต้นเช่าซื้อ

		3 - newLoanAmount=205483.0									#3 - ยอดจัดเช่าซื้อ
		4 - vat_newLoanAmount=14383.81								#4 - ภาษีมูลค่าเพิ่ม (VAT) ของยอดจัดเช่าซื้อ
		5 - sum_newLoanAmount=219866.81							#5 - ยอดจัดเช่าซื้อ + VAT

		6 - NewInterest=107878.58											#6 - ดอกเบี้ย
		7 - vat_newInterest=7551.50										#7 - ภาษีมูลค่าเพิ่ม (VAT) ของดอกเบี้ย
		8 - sum_newInterest=115430.08									#8 - ดอกเบี้ย + VAT

		9 - newLoanAmountWithInterest=313361.58					#9 - รวมยอดเช่าซื้อ
		10 - vat_newLoanAmountWithInterest=21935.31			#10 - ภาษีมูลค่าเพิ่ม (VAT) ของรวมยอดเช่าซื้อ
		11 - sum_newLoanAmountWithInterest=335296.89		#11 - รวมยอดเช่าซื้อ + VAT

		12 - newMonthlyInstallmentNoVat=5223.0					#12 - ค่างวด งวดแรก
		13 - vat_newMonthlyInstallmentNoVat=365.61				#13 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดแรก
		14 - newMonthlyInstallment=5590								#14 - ค่างวด งวดแรก + VAT

		15 - InterestRate = 10.5												#15 - อัตราดอกเบี้ย
		16 - LoanTerm = 60													#16 - จำนวนงวด
		17 - LoanType = CHUBB												#17 - บริษัทประกัน
		18 - IDcard = 123456789123										#18 - เลขบัตรประชาชน
		19 - BirthDate = 12-02-2523										#19 - วันเดือนปีเกิด

		20 - newLastMonthlyInstallment									#20 - ค่างวด งวดสุดท้าย			
		21 - vat_newLastMonthlyInstallmentNoVat						#21 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดสุดท้าย
		22 - newLastMonthlyInstallmentNoVat							#22 - ค่างวด งวดสุดท้าย	+ VAT

		23 - AppDate																#23 - วันเซ็นสัญญา
		24 - Age																	#24 - อายุ
		25 - Gender																#25 - เพศ
		26 - xRate																	#26 - อัตราดอกเบี้ย Effective rate
		27 - newMonthlyInstallmentNoVat_noROUND					#27 - ค่างวด ไม่ปัดเศษ
		28 - approveLoanAmount											#28 - ยอดอนุมัติเช่าซื้อ(1)
		*/

		var EffRate = arryData[26];

		if(arryData[17] == "CHUBB"){
			var insutype = "Chubb Life";
			var trcolor = "#9C27B0";
		}else if(arryData[17] == "TLIFE"){
			var insutype = "T Life";
			var trcolor = "#f37020";
		}else{
			var insutype = "AIA";
			var trcolor = "#e53a40";
		}
		if(arryData[25] == "male"){
			var sGender = "ชาย";
		}else{
			var sGender = "หญิง";
		}

		var headerInsu = '';
		if(arryData[17] == "NO"){
			headerInsu = '';
		}else{
			headerInsu = '<table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 25px;background-color: '+trcolor+';color: #fff;font-weight: bold;"><td style="text-align: center;">'+insutype+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 22px;background-color: #3a57af;color: #fff;"><td width="50%" style="text-align: center;">อัตราเบี้ยประกัน (%)</td><td style="text-align: center;">ค่าเบี้ยประกัน</td></tr><tr style="height: 22px;background-color: #ebebeb;"><td style="text-align: center;background-color: #FFC107; font-weight: bold;">'+arryData[0]+'</td><td style="text-align: center;background-color: #fee42d; font-weight: bold;">'+arryData[1]+'</td></tr></table>';
		}

		var resData = '<h1>ข้อมูลสินเชื่อ</h1><div style="text-align: center;"><b>เพศ</b>&nbsp;:&nbsp;'+sGender+' &nbsp;&nbsp;<b>อายุ</b>&nbsp;:&nbsp;'+arryData[24]+'&nbsp;ปี</div><hr><div class="accounttype">'+headerInsu+'<table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">เงินต้นเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">อัตราดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[15]+'%&nbsp;<font style="color: #ff1b1b; font-size: 15px;">(EF '+EffRate+')</font></td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนงวด</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[16]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ทุนประกัน</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[28]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ยอดจัดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[3]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[4]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[5]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[6]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[7]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[8]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">รวมยอดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[9]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[10]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[11]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดแรก</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[12]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[13]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[14]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดสุดท้าย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[22]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[21]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[20]+'</td></tr></table></div>';
		var btnBack = '<input type="button" name="btnback" id="btnback" class="buttonback" onclick="javascript:resultform()" value="กลับ">';
		var btnCal = '<input type="button" name="btnback" id="btnback" class="buttonback2" onclick="javascript:backform()" value="แก้ไข">';
		$('#output').html(btnBack+btnCal+resData);
}

function resultform() {
		var arryData = $.trim($('#resdata').val()).split("|");
		/* ## arryData ##
		0 - insuRate=1.68														#0 - อัตราเบี้ยประกัน (%)
		1 - PPIamount=5483.0												#1 - ค่าเบี้ยประกัน
		2 - LoanAmount=200000.0											#2 - เงินต้นเช่าซื้อ

		3 - newLoanAmount=205483.0									#3 - ยอดจัดเช่าซื้อ
		4 - vat_newLoanAmount=14383.81								#4 - ภาษีมูลค่าเพิ่ม (VAT) ของยอดจัดเช่าซื้อ
		5 - sum_newLoanAmount=219866.81							#5 - ยอดจัดเช่าซื้อ + VAT

		6 - NewInterest=107878.58											#6 - ดอกเบี้ย
		7 - vat_newInterest=7551.50										#7 - ภาษีมูลค่าเพิ่ม (VAT) ของดอกเบี้ย
		8 - sum_newInterest=115430.08									#8 - ดอกเบี้ย + VAT

		9 - newLoanAmountWithInterest=313361.58					#9 - รวมยอดเช่าซื้อ
		10 - vat_newLoanAmountWithInterest=21935.31			#10 - ภาษีมูลค่าเพิ่ม (VAT) ของรวมยอดเช่าซื้อ
		11 - sum_newLoanAmountWithInterest=335296.89		#11 - รวมยอดเช่าซื้อ + VAT

		12 - newMonthlyInstallmentNoVat=5223.0					#12 - ค่างวด งวดแรก
		13 - vat_newMonthlyInstallmentNoVat=365.61				#13 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดแรก
		14 - newMonthlyInstallment=5590								#14 - ค่างวด งวดแรก + VAT

		15 - InterestRate = 10.5												#15 - อัตราดอกเบี้ย
		16 - LoanTerm = 60													#16 - จำนวนงวด
		17 - LoanType = CHUBB												#17 - บริษัทประกัน
		18 - IDcard = 123456789123										#18 - เลขบัตรประชาชน
		19 - BirthDate = 12-02-2523										#19 - วันเดือนปีเกิด

		20 - newLastMonthlyInstallment									#20 - ค่างวด งวดสุดท้าย			
		21 - vat_newLastMonthlyInstallmentNoVat						#21 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดสุดท้าย
		22 - newLastMonthlyInstallmentNoVat							#22 - ค่างวด งวดสุดท้าย	+ VAT

		23 - AppDate																#23 - วันเซ็นสัญญา
		24 - Age																	#24 - อายุ
		25 - Gender																#25 - เพศ
		26 - xRate																	#26 - อัตราดอกเบี้ย Effective rate
		27 - newMonthlyInstallmentNoVat_noROUND					#27 - ค่างวด ไม่ปัดเศษ
		28 - approveLoanAmount											#28 - ยอดอนุมัติเช่าซื้อ(1)
		*/

		var EffRate = arryData[26];

		if(arryData[17] == "CHUBB"){
			var insutype = "Chubb Life";
			var trcolor = "#9C27B0";
		}else if(arryData[17] == "TLIFE"){
			var insutype = "T Life";
			var trcolor = "#f37020";
		}else{
			var insutype = "AIA";
			var trcolor = "#e53a40";
		}
		if(arryData[25] == "male"){
			var sGender = "ชาย";
		}else{
			var sGender = "หญิง";
		}

		var headerInsu = '';
		if(arryData[17] == "NO"){
			headerInsu = '';
		}else{
			headerInsu = '<table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 25px;background-color: '+trcolor+';color: #fff;font-weight: bold;"><td style="text-align: center;">'+insutype+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 22px;background-color: #3a57af;color: #fff;"><td width="50%" style="text-align: center;">อัตราเบี้ยประกัน (%)</td><td style="text-align: center;">ค่าเบี้ยประกัน</td></tr><tr style="height: 22px;background-color: #ebebeb;"><td style="text-align: center;background-color: #FFC107; font-weight: bold;">'+arryData[0]+'</td><td style="text-align: center;background-color: #fee42d; font-weight: bold;">'+arryData[1]+'</td></tr></table>';
		}

		var resData = '<h1>ข้อมูลสินเชื่อ</h1><div style="text-align: center;"><b>เพศ</b>&nbsp;:&nbsp;'+sGender+' &nbsp;&nbsp;<b>อายุ</b>&nbsp;:&nbsp;'+arryData[24]+'&nbsp;ปี</div><hr><div class="accounttype">'+headerInsu+'<table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">เงินต้นเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">อัตราดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[15]+'%&nbsp;<font style="color: #ff1b1b; font-size: 15px;">(EF '+EffRate+')</font></td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนงวด</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[16]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ทุนประกัน</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[28]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ยอดจัดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[3]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">รวมยอดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[11]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดแรก</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[14]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดสุดท้าย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[20]+'</td></tr></table></div><input type="button" name="btnback" id="btnback" class="buttonback" style="margin: auto 25%;" onclick="javascript:detailform()" value="รายละเอียด">';
		var btnBack = '<input type="button" name="btnback" id="btnback" class="buttonback" onclick="javascript:resetform()" value="เริ่มใหม่">';
		var btnCal = '<input type="button" name="btnback" id="btnback" class="buttonback2" onclick="javascript:backform()" value="แก้ไข">';
		$('#output').html(btnBack+btnCal+resData);
}

function xRate(periods, payment, present, future, type, guess) {
    guess = (guess === undefined) ? 0.01 : guess;
    future = (future === undefined) ? 0 : future;
    type = (type === undefined) ? 0 : type;
  
    // Set maximum epsilon for end of iteration
    var epsMax = 1e-10;
  
    // Set maximum number of iterations
    var iterMax = 10;
  
    // Implement Newton's method
    var y, y0, y1, x0, x1 = 0,
      f = 0,
      i = 0;
    var rate = guess;
    if (Math.abs(rate) < epsMax) {
      y = present * (1 + periods * rate) + payment * (1 + rate * type) * periods + future;
    } else {
      f = Math.exp(periods * Math.log(1 + rate));
      y = present * f + payment * (1 / rate + type) * (f - 1) + future;
    }
    y0 = present + payment * periods + future;
    y1 = present * f + payment * (1 / rate + type) * (f - 1) + future;
    i = x0 = 0;
    x1 = rate;
    while ((Math.abs(y0 - y1) > epsMax) && (i < iterMax)) {
      rate = (y1 * x0 - y0 * x1) / (y1 - y0);
      x0 = x1;
      x1 = rate;
        if (Math.abs(rate) < epsMax) {
          y = present * (1 + periods * rate) + payment * (1 + rate * type) * periods + future;
        } else {
          f = Math.exp(periods * Math.log(1 + rate));
          y = present * f + payment * (1 / rate + type) * (f - 1) + future;
        }
      y0 = y1;
      y1 = y;
      ++i;
    }
    //return rate;
	rate = parseFloat(rate)*100*12;
	return rate.toFixed(2);
}

$(document).ready(function() { 
	$('.datepicker').datepicker({
		format: "dd-mm-yyyy",
		todayBtn: "linked",
		language: "th-th",
		autoclose: true,
		todayHighlight: true
	});
	

	// กดปุ่มคำนวณ
	$( "#btncal" ).click(function() {
		
		var flgerr = true;
		/*if($.trim($('#BirthDate').val())=="" || $.trim($('#BirthDate').val()).length!=10){
			flgerr = false;
			$("#BirthDate").css('border-color', 'red');
		}else{ $("#BirthDate").css('border-color', ''); }*/
		
		/*if($.trim($('#IDcard').val())=="" || $.trim($('#IDcard').val()).replace(/\-/g,'').length!=13 || !checkID($.trim($('#IDcard').val()).replace(/\-/g,'')) ){
			flgerr = false;
			$("#IDcard").css('border-color', 'red');
		}else{ $("#IDcard").css('border-color', ''); }*/
		
		if($.trim($('#LoanAmount').val())==""){
			flgerr = false;
			$("#LoanAmount").css('border-color', 'red');
		}else{ $("#LoanAmount").css('border-color', '');}
		
		if($.trim($('#InterestRate').val())==""){
			flgerr = false;
			$("#InterestRate").css('border-color', 'red');
		}else{ $("#InterestRate").css('border-color', '');}
		
		if($.trim($('#LoanTerm').val())==""){
			flgerr = false;
			$("#LoanTerm").css('border-color', 'red');
		}else{ $("#LoanTerm").css('border-color', ''); }
		
		if($.trim($('#AppDate').val())=="" || $.trim($('#AppDate').val()).length!=10){
			flgerr = false;
			$("#AppDate").css('border-color', 'red');
		}else{ $("#AppDate").css('border-color', ''); }		
		
		if(flgerr){
				$('#dvoutput').show();				
				$('#output').html("<center>กรุณารอสักครู่ ระบบกำลังคำนวณ...<br><br><img src=\"images/loading1.gif\" id=\"loading-img\" alt=\"Please Wait\"/></center>");

				var LoanType = $("input[name='LoanType']:checked").val();
				var Gender = $("input[name='Gender']:checked").val();
				var BirthDate = $('#BirthDate').val();
				//console.log("rand="+ Math.random()+"&LoanType="+LoanType+"&Gender="+Gender+"&BirthDate="+BirthDate+"&IDcard="+$('#IDcard').val()+"&AppDate="+$('#AppDate').val()+"&LoanAmount="+$('#LoanAmount').val()+"&InterestRate="+$('#InterestRate').val()+"&LoanTerm="+$('#LoanTerm').val());
				$.ajax({
						url: "getData.php",
						type: "POST",
						data: "rand="+ Math.random()+"&LoanType="+LoanType+"&Gender="+Gender+"&BirthDate="+BirthDate+"&IDcard="+$('#IDcard').val()+"&AppDate="+$('#AppDate').val()+"&LoanAmount="+$('#LoanAmount').val()+"&InterestRate="+$('#InterestRate').val()+"&LoanTerm="+$('#LoanTerm').val(),
						success: function(data){						
							$('#resdata').val(data); 
							//console.log(data);
							var arryData = $.trim(data).split("|");							
							/* ## arryData ##
							0 - insuRate=1.68														#0 - อัตราเบี้ยประกัน (%)
							1 - PPIamount=5483.0												#1 - ค่าเบี้ยประกัน
							2 - LoanAmount=200000.0											#2 - เงินต้นเช่าซื้อ

							3 - newLoanAmount=205483.0									#3 - ยอดจัดเช่าซื้อ
							4 - vat_newLoanAmount=14383.81								#4 - ภาษีมูลค่าเพิ่ม (VAT) ของยอดจัดเช่าซื้อ
							5 - sum_newLoanAmount=219866.81							#5 - ยอดจัดเช่าซื้อ + VAT

							6 - NewInterest=107878.58											#6 - ดอกเบี้ย
							7 - vat_newInterest=7551.50										#7 - ภาษีมูลค่าเพิ่ม (VAT) ของดอกเบี้ย
							8 - sum_newInterest=115430.08									#8 - ดอกเบี้ย + VAT

							9 - newLoanAmountWithInterest=313361.58					#9 - รวมยอดเช่าซื้อ
							10 - vat_newLoanAmountWithInterest=21935.31			#10 - ภาษีมูลค่าเพิ่ม (VAT) ของรวมยอดเช่าซื้อ
							11 - sum_newLoanAmountWithInterest=335296.89		#11 - รวมยอดเช่าซื้อ + VAT

							12 - newMonthlyInstallmentNoVat=5223.0					#12 - ค่างวด งวดแรก
							13 - vat_newMonthlyInstallmentNoVat=365.61				#13 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดแรก
							14 - newMonthlyInstallment=5590								#14 - ค่างวด งวดแรก + VAT

							15 - InterestRate = 10.5												#15 - อัตราดอกเบี้ย
							16 - LoanTerm = 60													#16 - จำนวนงวด
							17 - LoanType = CHUBB	, NO											#17 - บริษัทประกัน
							18 - IDcard = 123456789123										#18 - เลขบัตรประชาชน
							19 - BirthDate = 12-02-2523										#19 - วันเดือนปีเกิด

							20 - newLastMonthlyInstallment									#20 - ค่างวด งวดสุดท้าย			
							21 - vat_newLastMonthlyInstallmentNoVat						#21 - ภาษีมูลค่าเพิ่ม (VAT) ของงวดสุดท้าย
							22 - newLastMonthlyInstallmentNoVat							#22 - ค่างวด งวดสุดท้าย	+ VAT

							23 - AppDate																#23 - วันเซ็นสัญญา
							24 - Age																	#24 - อายุ
							25 - Gender																#25 - เพศ
							26 - xRate																	#26 - อัตราดอกเบี้ย Effective rate
							27 - newMonthlyInstallmentNoVat_noROUND					#27 - ค่างวด ไม่ปัดเศษ
							28 - approveLoanAmount											#28 - ยอดอนุมัติเช่าซื้อ(1)
							*/							

							var EffRate = arryData[26];
							//console.log("EffRate=>"+EffRate);

							/*var xLoanTerm = arryData[16];  //xLoanTerm = 36;
							var xMonthlyNoVat = arryData[27].replace(",", "").replace(".00", "");  //xMonthlyNoVat = 3444.44;
							xMonthlyNoVat =  -xMonthlyNoVat;
							var xLoanAmount =  arryData[3].replace(",", "").replace(".00", "");	 //xLoanAmount = 100000;					
							var EffRate = xRate(xLoanTerm , parseFloat(xMonthlyNoVat) , parseFloat(xLoanAmount));
							console.log("xRate("+xLoanTerm+" , "+xMonthlyNoVat+" , "+xLoanAmount+") => "+EffRate);*/
							//xRate(36 , -3444.44 ,100000)

							// Test	
							//var prate = parseFloat(arryData[15])/100;
							//var Prof =  parseFloat(xLoanAmount)*prate*parseFloat(xLoanTerm/12);
							//console.log("Prof =  "+parseFloat(xLoanAmount)+"*"+prate+"*"+parseFloat(xLoanTerm/12));
							//var Payment = (parseFloat(xLoanAmount) + Prof)/parseFloat(xLoanTerm);
							//console.log("Payment = ("+parseFloat(xLoanAmount)+" + "+Prof+")/"+parseFloat(xLoanTerm));
							//console.log("Prof=>"+Prof);
							//console.log("Payment=>"+Payment);

							//if(parseFloat(arryData[26])>15.00){
									
							//		$("#msg").html("ดอกเบี้ย Effective Rate เกินที่กำหนด!");
							//		$('#xRate').html("("+EffRate+" %)");
							//		$('#dvform').show();
							//		$('#dvoutput').hide();
							//		$('#output').html("");

							//}else{

									$("#msg").html("");
									$('#xRate').html("");
									$('#dvform').hide();
									
									if(arryData[17] == "CHUBB"){
										var insutype = "Chubb Life";
										var trcolor = "#9C27B0";
									}else if(arryData[17] == "TLIFE"){
										var insutype = "T Life";
										var trcolor = "#f37020";
									}else{
										var insutype = "AIA";
										var trcolor = "#e53a40";
									}
									if(arryData[25] == "male"){
										var sGender = "ชาย";
									}else{
										var sGender = "หญิง";
									}

									var headerInsu = '';
									if(arryData[17] == "NO"){
										headerInsu = '';
									}else{
										headerInsu = '<table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 25px;background-color: '+trcolor+';color: #fff;font-weight: bold;"><td style="text-align: center;">'+insutype+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 22px;background-color: #3a57af;color: #fff;"><td width="50%" style="text-align: center;">อัตราเบี้ยประกัน (%)</td><td style="text-align: center;">ค่าเบี้ยประกัน</td></tr><tr style="height: 22px;background-color: #ebebeb;"><td style="text-align: center;background-color: #FFC107; font-weight: bold;">'+arryData[0]+'</td><td style="text-align: center;background-color: #fee42d; font-weight: bold;">'+arryData[1]+'</td></tr></table>';
									}

									var resData = '<h1>ข้อมูลสินเชื่อ</h1><div style="text-align: center;"><b>เพศ</b>&nbsp;:&nbsp;'+sGender+' &nbsp;&nbsp;<b>อายุ</b>&nbsp;:&nbsp;'+arryData[24]+'&nbsp;ปี</div><hr><div class="accounttype">'+headerInsu+'<table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">เงินต้นเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">อัตราดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[15]+'%&nbsp;<font style="color: #ff1b1b; font-size: 15px;">(EF '+EffRate+')</font></td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนงวด</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[16]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ทุนประกัน</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[28]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ยอดจัดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[3]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">รวมยอดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[11]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดแรก</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[14]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดสุดท้าย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[20]+'</td></tr></table></div><input type="button" name="btnback" id="btnback" class="buttonback" style="margin: auto 25%;" onclick="javascript:detailform()" value="รายละเอียด">';
									var btnBack = '<input type="button" name="btnback" id="btnback" class="buttonback" onclick="javascript:resetform()" value="เริ่มใหม่">';
									var btnCal = '<input type="button" name="btnback" id="btnback" class="buttonback2" onclick="javascript:backform()" value="แก้ไข">';

									$('#output').html(btnBack+btnCal+resData);

							//}

						}
					});
		}
		return false;
	});		
	
	$("#LoanAmount, #InterestRate").on("keypress keyup blur",function (event) {
            //this.value = this.value.replace(/[^0-9\.]/g,'');
    		 $(this).val($(this).val().replace(/[^0-9\.]/g,''));
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
		
	$("#LoanTerm").on("keypress keyup blur",function (event) {    
           $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
	
	$( "#radioOne" ).click(function() {
		$("#dvGB").show();
	});

	$( "#radioTwo" ).click(function() {
		$("#dvGB").hide();
	});

}); 	
</script>
</body>

</html>
