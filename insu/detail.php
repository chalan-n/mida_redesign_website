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
    <link href="css/legacy-override.css" rel="stylesheet">
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
   height: 500px;
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

input[type=text],input[type=password]{
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
  margin: 20px 8px 20px 0px;
  display: inline-block;
  float: right;
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
    </style>
    <script src="css/prefixfree.min.js"></script>
</head>

<body>
<div id="dvform" class="databox">
  <h1>คำนวณสินเชื่อ</h1>
  <form name="frm" id="frm" action="">
      <hr>
    <div class="accounttype">
            <label id="icon" for="name"><i class="icon-shield"></i></label>
            <input type="radio" value="AIA" id="radioOne" name="LoanType" checked/>
            <label for="radioOne" class="radio" chec>AIA</label>
            <input type="radio" value="CHUBB" id="radioTwo" name="LoanType" />
            <label for="radioTwo" class="radio">Chubb Life</label>
            <hr>
            <label id="icon" for="name"><i class="icon-user"></i></label>
            <input type="radio" value="male" id="male" name="Gender" checked/>
            <label for="male" class="radio" chec>ชาย</label>
            <input type="radio" value="female" id="female" name="Gender" />
            <label for="female" class="radio">หญิง</label>
            <label id="icon" for="name"><i class="icon-calendar"></i></label>
            <input type="text" name="BirthDate" id="BirthDate" class="datepicker" data-provide="datepicker" data-date-language="th-th" placeholder="วันเดือนปีเกิด" required/>
            <hr>
            <label id="icon" for="name"><i class="icon-money"></i></label>
            <input type="text" name="LoanAmount" id="LoanAmount" placeholder="ราคารถยนต์" required/>
            <label id="icon" for="name"><i class="icon-star"></i></label>
            <input type="text" name="InterestRate" id="InterestRate" style="width: 50%;" placeholder="อัตราดอกเบี้ย" required/>&nbsp;%
            <label id="icon" for="name"><i class="icon-th-list"></i></label>
            <input type="text" name="LoanTerm" id="LoanTerm" placeholder="จำนวนงวด" required/>
            <button name="btncal" id="btncal" class="inputbutton" />คำนวณสินเชื่อ</button>
  	</div>
    
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
<script type="text/javascript">
function resetform() {
		$('#dvoutput').hide();
		$('#dvform').show();
		document.forms[0].reset();
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
		//console.log("rand="+ Math.random()+"&LoanType="+$("input[name='LoanType']:checked").val()+"&Gender="+$("input[name='Gender']:checked").val()+"&BirthDate="+$('#BirthDate').val()+"&LoanAmount="+$('#LoanAmount').val()+"&InterestRate="+$('#InterestRate').val()+"&LoanTerm="+$('#LoanTerm').val());
		
		if($("input[name='LoanType']:checked").val()!="" && $("input[name='Gender']:checked").val()!="" && $('#BirthDate').val()!="" && $('#LoanAmount').val()!="" && $('#InterestRate').val()!="" && $('#LoanTerm').val()!=""){
				$('#dvoutput').show();
				$('#dvform').hide();
				$('#output').html("<center>กรุณารอสักครู่ ระบบกำลังคำนวณ...<br><br><img src=\"images/loading1.gif\" id=\"loading-img\" alt=\"Please Wait\"/></center>");
				$.ajax({
						url: "getData.php",
						type: "POST",
						data: "rand="+ Math.random()+"&LoanType="+$("input[name='LoanType']:checked").val()+"&Gender="+$("input[name='Gender']:checked").val()+"&BirthDate="+$('#BirthDate').val()+"&LoanAmount="+$('#LoanAmount').val()+"&InterestRate="+$('#InterestRate').val()+"&LoanTerm="+$('#LoanTerm').val(),
						success: function(data){						
							//console.log(data);
							/*
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
							*/
							var arryData = $.trim(data).split("|");
							var resData = '<h1>ข้อมูลสินเชื่อ</h1><hr><div class="accounttype"><table style="width: 95%;border: 1px solid #9E9E9E;"><tr style="height: 22px;background-color: #3a57af;color: #fff;"><td width="50%" style="text-align: center;">อัตราเบี้ยประกัน (%)</td><td style="text-align: center;">ค่าเบี้ยประกัน</td></tr><tr style="height: 22px;background-color: #ebebeb;"><td style="text-align: center;background-color: #FFC107; font-weight: bold;">'+arryData[0]+'</td><td style="text-align: center;background-color: #fee42d; font-weight: bold;">'+arryData[1]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ราคารถยนต์</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[2]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">อัตราดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[15]+'%</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนงวด</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[16]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ยอดจัดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[3]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[4]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[5]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ดอกเบี้ย</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[6]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[7]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[8]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">รวมยอดเช่าซื้อ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[9]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[10]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[11]+'</td></tr></table><table style="width: 95%;border: 1px solid #9E9E9E;margin-top: 20px;"><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ค่างวดผ่อนชำระ</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #fee42d; font-weight: bold;">'+arryData[12]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">ภาษีมูลค่าเพิ่ม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #ffec66; font-weight: bold;">'+arryData[13]+'</td></tr><tr style="height: 22px;"><td width="40%" style="text-align: left;background-color: #3a57af;color: #fff;padding-left: 5px;">จำนวนเงินรวม</td><td width="60%" style="padding-right: 5px; text-align: right;background-color: #FFC107; font-weight: bold;">'+arryData[14]+'</td></tr></table></div>';
							var btnBack = '<a href="#" id="btnback" class="inputbutton2" onclick="javascript:resetform()">กลับ</a>';
							$('#output').html(btnBack+resData);
						}
					});
		}
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
