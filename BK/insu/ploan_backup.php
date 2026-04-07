<?php
session_start();
if($_SESSION['sess_login']==""){ header("Location: login.php"); exit();}

if($_GET['prod']=='001'){
	$prodName = "สินเชื่อนาโนไฟแนนซ์";
}elseif($_GET['prod']=='002'){
	$prodName = "สินเชื่อส่วนบุคคล";
}elseif($_GET['prod']=='005'){
	$prodName = "สินเชื่อจำนำรถยนต์";
}else{
	$prodName = "";
}

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
  font-size: 28px;
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
  margin: 20px auto 10px;
    width: 343px;
   min-height: 420px;
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
  margin: 20px 8px 20px 15px;
  display: inline-block;
  float: left;
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

.inputbutton_clear {
    font-size: 15px;
    font-weight: 600;
    /* color: white; */
    padding: 5px 25px;
    margin: 20px 8px 20px 5px;
    display: inline-block;
    float: left;
    text-decoration: none;
    /* width: 98px; */
    line-height: 25px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    background-color: #bdbdbd;
    -webkit-box-shadow: 0 3px rgb(58 87 175 / 75%);
    -moz-box-shadow: 0 3px rgba(58,87,175,.75);
    box-shadow: 0 1px #cecece;
    transition: all 0.1s linear 0s;
    top: 0px;
    position: relative;
	border-color: #c7c7c7;
}

.inputbutton_clear:hover {
  top: 3px; 
  box-shadow: none;
  
}

.outputbox {
  margin: 0 auto 20px;
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

.buttondetail {
	font-size: 15px;
    padding: 5px 25px;
    display: inline-block;
    text-decoration: none;
    line-height: 18px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    position: relative;
    border: 1px solid #008605;
    background: #4caf50;
    color: #fff;
	font-weight: bold;
    box-shadow: 0 1px #123814;
}
.buttondetail:hover {
  top: 3px;
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
	<?=$prodName?>
	<span style="float: right;margin: -10px 5px 0 0;"><a href="index.php"><img src="images/close.png" border="0"></a></span>
  </h1>
  <form name="frm" id="frm" action="">
      <hr>
    <div class="accounttype">
            <label id="icon" for="name"><i class="icon-money"></i></label>
            <input type="text" name="LoanAmount" id="LoanAmount" placeholder="เงินต้นเช่าซื้อ" onkeypress="checknumber()" />
            <label id="icon" for="name"><i class="icon-star"></i></label>
            <input type="text" name="InterestRate" id="InterestRate" style="width: 50%;" placeholder="อัตราดอกเบี้ย" onkeypress="checknumber()" />&nbsp;% ต่อปี
            <label id="icon" for="name"><i class="icon-th-list"></i></label>
			<input type="text" name="LoanTerm" id="LoanTerm" style="width: 50%;" placeholder="จำนวนงวด" onkeypress="checknumber()" /><br>
			<label id="icon" for="name"><i class="icon-calendar"></i></label>
            <input type="text" name="AppDate" id="AppDate" placeholder="วันที่เริ่มสัญญา" data-mask="00-00-0000" data-mask-selectonfocus="true" onkeypress="checknumber()" maxlength="10" value="<?=date("d")."-".date("m")."-".(date("Y")+543)?>" />
            <div style="padding-left: 15px; color:#F00;font-size: 15px;">วัน/เดือน/ปีพ.ศ. เริ่มสัญญา<br>เช่น 01-05-2533</div>
            <button name="btncal" id="btncal" class="inputbutton" />คำนวณสินเชื่อ</button>
			&nbsp;<button name="btnclear" id="btnclear" class="inputbutton_clear" />รีเซ็ต</button>
  	</div>
    <input type="hidden" name="resdata" id="resdata">
  </form>
</div>
<div id="dvoutput" class="outputbox" style="display:none;">
   <div id="output" class="accounttype" style="">xxxx</div>   
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

function backform() {
	var resData = '';

	var data = $("#resdata").val();
	if($.trim(data)!=""){
		var arryData = $.trim(data).split("@");
		if($.trim(arryData[0])!=""){
			var arryData2 = $.trim(arryData[0]).split("#");
			resData = '<table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tbody><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินกู้</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[0]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ดอกเบี้ยเงินกู้</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[1]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">เงินกู้รวมดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ผ่อนเดือนละ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[3]+'</td></tr></tbody></table><div style="text-align: center;margin-top: 15px;"><input type="button" name="btndetail" id="btndetail" class="buttondetail" onclick="javascript:detailform()" value="ดูตารางการผ่อนค่างวด"></div>';
		}
	}
	
	$('#output').html(resData);
	return false;
}

function detailform() {
		
		var data = $.trim($('#resdata').val());
		//console.log(data);
		var resData = '';

		if($.trim(data)!=""){
			var arryData = $.trim(data).split("@");
			if($.trim(arryData[0])!=""){
				var arryData2 = $.trim(arryData[0]).split("#");
				var resDetail = '';
				if($.trim(arryData[1])!=""){
					//console.log($.trim(arryData[1]));
					//period:Paid Per Month:Principle:Interrest:PrncipleAmount|
					var arryDetail = $.trim(arryData[1]).split("|");
					resDetail = '<table style="width: 95%;margin-top: 20px;"><tbody><tr style="height: 22px;"><td style="text-align: center;background-color: #4caf50;color: #fff;">งวด</td><td style="text-align: center;background-color: #4caf50;color: #fff;">ค่างวด</td><td style="text-align: center;background-color: #4caf50;color: #fff;">เงินต้น</td><td style="text-align: center;background-color: #4caf50;color: #fff;">ดอกเบี้ย</td><td style="text-align: center;background-color: #4caf50;color: #fff;">คงเหลือ</td></tr>';
					for(var i=0; i < arryDetail.length; i++){
						if($.trim(arryDetail[i])!=""){
							//console.log($.trim(arryDetail[i]));
							var arryDetail2 = $.trim(arryDetail[i]).split(":");
							resDetail = resDetail+'<tr style="height: 22px;font-size: 14px; color:#000;"><td style="text-align: center;background-color: #ffffff;">'+arryDetail2[0]+'.</td><td style="text-align: right;background-color: #ffffff;">'+arryDetail2[1]+'&nbsp;</td><td style="text-align: right;background-color: #ffffff;">'+arryDetail2[2]+'&nbsp;</td><td style="text-align: right;background-color: #ffffff;">'+arryDetail2[3]+'&nbsp;</td><td style="text-align: right;background-color: #ffffff;">'+arryDetail2[4]+'&nbsp;</td></tr>';
						}
					}
					resDetail = resDetail+'</tbody></table>';
				}
				//console.log(resDetail);

				resData = '<table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tbody><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินกู้</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[0]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ดอกเบี้ยเงินกู้</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[1]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">เงินกู้รวมดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ผ่อนเดือนละ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[3]+'</td></tr></tbody></table><div style="text-align: center;margin-top: 15px;">'+resDetail+'</div><div style="text-align: center;margin-top: 15px;"><input type="button" name="btnback" id="btnback" class="buttondetail" onclick="javascript:backform()" value="ปิด"></div>';
			}
		}

		$('#output').html(resData);
}

function resultform() {
		var arryData = $.trim($('#resdata').val()).split("|");
		/*
		"&LoanType="+$("input[name='LoanType']:checked").val()+"&Gender="+$("input[name='Gender']:checked").val()+"&BirthDate="+$('#BirthDate').val()+"&IDcard="+$('#IDcard').val()+"&LoanAmount="+$('#LoanAmount').val()+"&InterestRate="+$('#InterestRate').val()+"&LoanTerm="+$('#LoanTerm').val()
		0 - insuRate=1.68
		1 - PPIamount=5483.0
		2 - LoanAmount=200000.0
		3 - newLoanAmount=205483.0
		4 - vat_newLoanAmount=14383.81
		5 - sum_newLoanAmount=219866.81
		6 - NewInterest=107878.58
		7 - vat_newInterest=7551.50
		8 - sum_newInterest=115430.08
		9 - newLoanAmountWithInterest=313361.58
		10 - vat_newLoanAmountWithInterest=21935.31
		11 - sum_newLoanAmountWithInterest=335296.89
		12 - newMonthlyInstallmentNoVat=5223.0
		13 - vat_newMonthlyInstallmentNoVat=365.61
		14 - newMonthlyInstallment=5590
		15 - InterestRate = 10.5
		16 - LoanTerm = 60
		17 - LoanType = AIA
		18 - IDcard = 123456789123
		19 - BirthDate = 12-02-2523
		20 - newLastMonthlyInstallment = xxxxxxxxxx
		*/
		if(arryData[17] == "CHUBB"){
			var insutype = "Chubb Life";
			var trcolor = "#9C27B0";
		}else{
			var insutype = "AIA";
			var trcolor = "#e53a40";
		}
		if(arryData[25] == "male"){
			var sGender = "ชาย";
		}else{
			var sGender = "หญิง";
		}
		var resData = '<h1>ข้อมูลสินเชื่อ</h1><div style="text-align: center;"><b>เพศ</b>&nbsp;:&nbsp;'+sGender+' &nbsp;&nbsp;<b>อายุ</b>&nbsp;:&nbsp;'+arryData[24]+'&nbsp;ปี</div><hr><div class="accounttype"><table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 25px;background-color: '+trcolor+';color: #fff;font-weight: bold;"><td style="text-align: center;">'+insutype+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 22px;background-color: #3a57af;color: #fff;"><td width="50%" style="text-align: center;">อัตราเบี้ยประกัน (%)</td><td style="text-align: center;">ค่าเบี้ยประกัน</td></tr><tr style="height: 22px;background-color: #ebebeb;"><td style="text-align: center;background-color: #FFC107; font-weight: bold;">'+arryData[0]+'</td><td style="text-align: center;background-color: #fee42d; font-weight: bold;">'+arryData[1]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">เงินต้นเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">อัตราดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[15]+'%</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนงวด</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[16]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ยอดจัดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[3]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">รวมยอดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[11]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดแรก</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[14]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ชำระงวดสุดท้าย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[20]+'</td></tr></table></div><input type="button" name="btnback" id="btnback" class="buttonback" style="margin: auto 25%;" onclick="javascript:detailform()" value="รายละเอียด">';
		var btnBack = '<input type="button" name="btnback" id="btnback" class="buttonback" onclick="javascript:resetform()" value="เริ่มใหม่">';
		var btnCal = '<input type="button" name="btnback" id="btnback" class="buttonback2" onclick="javascript:backform()" value="แก้ไข">';
		$('#output').html(btnBack+btnCal+resData);
}

$(document).ready(function() { 
	$('.datepicker').datepicker({
		format: "dd-mm-yyyy",
		todayBtn: "linked",
		language: "th-th",
		autoclose: true,
		todayHighlight: true
	});
	
	$( "#btncal" ).click(function() {
		
		var flgerr = true;
		
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
		
		if(flgerr){
				$('#dvoutput').show();
				$('#output').html("<center>กรุณารอสักครู่ ระบบกำลังคำนวณ...<br><br><img src=\"images/loading1.gif\" id=\"loading-img\" alt=\"Please Wait\"/></center>");

				//console.log("&LoanAmount="+$('#LoanAmount').val()+"&InterestRate="+$('#InterestRate').val()+"&LoanTerm="+$('#LoanTerm').val());
				var resData = '';

				$.ajax({
						url: "getDataLoan.php",
						type: "POST",
						data: "rand="+ Math.random()+"&LoanAmount="+$('#LoanAmount').val()+"&InterestRate="+$('#InterestRate').val()+"&LoanTerm="+$('#LoanTerm').val()+"&AppDate="+$('#AppDate').val(),
						success: function(data){						
							//console.log(data);
							/* LoanAmount#Sum Interest#Sum LoanAmount#Paid Per Month@period:Paid Per Month:Principle:Interrest:PrncipleAmount|	*/
							$("#resdata").val($.trim(data));
							
							if($.trim(data)!=""){
								var arryData = $.trim(data).split("@");
								if($.trim(arryData[0])!=""){
									var arryData2 = $.trim(arryData[0]).split("#");
									resData = '<table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tbody><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินกู้</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[0]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ดอกเบี้ยเงินกู้</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[1]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">เงินกู้รวมดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ผ่อนเดือนละ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffffff; font-weight: bold;">'+arryData2[3]+'</td></tr></tbody></table><div style="text-align: center;margin-top: 15px;"><input type="button" name="btndetail" id="btndetail" class="buttondetail" onclick="javascript:detailform()" value="ดูตารางการผ่อนค่างวด"></div>';
								}
							}
							
							$('#output').html(resData);
						}
				});
		}
		return false;
	});		
	
	$( "#btnclear" ).click(function() {
		$("#LoanAmount").val('');
		$("#LoanAmount").css('border-color', '');
		$("#InterestRate").val('');
		$("#InterestRate").css('border-color', '');
		$("#LoanTerm").val('');
		$("#LoanTerm").css('border-color', '');
		$("#AppDate").val('<?=date("d")."-".date("m")."-".(date("Y")+543)?>');
		$('#output').html('');
		$('#dvoutput').hide();
		return false;
	});	

	$("#InterestRate").on("keypress keyup blur",function (event) {
            //this.value = this.value.replace(/[^0-9\.]/g,'');
    		 $(this).val($(this).val().replace(/[^0-9\.]/g,''));
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
		
	$("#LoanAmount, #LoanTerm").on("keypress keyup blur",function (event) {    
           $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
	
}); 	
</script>
</body>

</html>
