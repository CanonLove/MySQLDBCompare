<?php
###############################################
#
# https://github.com/CanonLove/MySQLDBCompare
#
# 2016.04.01 / DB Compare / by CanonLove
# 2022.01.04 ver 1.0.1
# 2023.04.27 ver 1.1.0
#
# DB1 / DB2 Compare
# 1) DB1 - Real DB
# 2) DB2 - Development DB
# 3) DB1 is Table/Procedure exist & DB2 is Table/Procedure not exist >> 'Be careful' text print
# 4) DB1 is Table/Procedure not exist & DB2 is Table/Procedure not exist  >> 'Create Table & Procedure'  generated
# 5) 'Drop Table & Procedure' not generated
#
###############################################

require_once('./class.Diff.php');							/* text diff */
/*  class.Diff.php is http://code.stephenmorley.org/php/diff-implementation/  */

function _microtime ( ) { return array_sum(explode(' ',microtime())); }    /* Page loading time check */

function getRealClientIp() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } else if(getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } else if(getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } else if(getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } else if(getenv('HTTP_FORWARDED')) {
        $ipaddress = getenv('HTTP_FORWARDED');
    } else if(getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'unknown (알수없음)';
    }
    return $ipaddress;
}

/* Page loading time check (START time) */
$time_start=_microtime();

$ip = getRealClientIp();

/* Page loading time check */

/* post value START */
$DB1Ip = isset($_POST['DB1Ip']) ? trim($_POST['DB1Ip']) : "";
$DB1Name = isset($_POST['DB1Name']) ? trim($_POST['DB1Name']) : "";
$DB1User = isset($_POST['DB1User']) ? trim($_POST['DB1User']) : "";
$DB1Pwd = isset($_POST['DB1Pwd']) ? trim($_POST['DB1Pwd']) : "";

$DB2Ip = isset($_POST['DB2Ip']) ? trim($_POST['DB2Ip']) : "";
$DB2Name = isset($_POST['DB2Name']) ? trim($_POST['DB2Name']) : "";
$DB2User = isset($_POST['DB2User']) ? trim($_POST['DB2User']) : "";
$DB2Pwd = isset($_POST['DB2Pwd']) ? trim($_POST['DB2Pwd']) : "";
/* post value END */
?>
<html>
	<meta charset="UTF-8">
	<style type="text/css">
	body, table, tr, td {  font-size:12px;}
	table, tr, td {  font-size:12px; border-collapse: collapse; padding:2px;}
	.tr1 { background-color:#efefef; }
	.textcenter { text-align:center;; }
	.h27 { height:27px; }
	.td1 { text-align:center; }
	.redbox { border:1px solid #ff0000; }
	.greybox { border:1px solid #cdcdcd; }
	.width95 { width:95px;}
	.width130 { width:130px;}

	/* top */
	#layer_fixed	{ height:170px;width:100%; color: #555; font-size:12px; position:fixed; z-index:999; top:0px; left:0px; -webkit-box-shadow: 0 1px 2px 0 #777; box-shadow: 0 1px 2px 0 #777; background-color:#ccc; }

	.button {background-color: #4CAF50; border-radius: 6px; color: #fff; padding: 3px 3px;}
	.resultBtn { border:1px solid #000; width:100px; heifht:25px; text-align:center;font-weight: bold; }

	.chkeckSuccess { font-size: 18pt; color: #0000ff; font-family: arial;  }
	.chkeckDifferent { font-size: 18pt; color: #ff0000; font-family: arial; }

	/* text diff */
      .diff td{
        padding:0 0.667em;
        vertical-align:top;
        white-space:pre;
        white-space:pre-wrap;
        font-family:Consolas,'Courier New',Courier,monospace;
        font-size:12px;
        /*line-height:1.333;*/
		border-right:1px solid #cdcdcd;
		line-height:0.9em
      }
      .diff span{
        display:block;
        /*min-height:1.333em;*/
        min-height:1em;
        margin-top:-1px;
        padding:0 3px;
      }
      * html .diff span{
        /*height:1.333em;*/
		height:1.em;
      }
      .diff span:first-child{
        margin-top:0;
      }
      .diffDeleted span{
        border:1px solid rgb(255,192,192);
        background:rgb(255,224,224);
      }
      .diffInserted span{
        border:1px solid rgb(192,255,192);
        background:rgb(224,255,224);
      }
      #toStringOutput{
        margin:0 2em 2em;
      }
    </style>

	<script type="text/javascript" >
	var tableVal = "none";
	function tableViewOnOff() {
		if(tableVal=="none") {
			document.getElementById("div_tableview").style.display = "block"
			tableVal = "block";
		} else {
			document.getElementById("div_tableview").style.display = "none"
			tableVal = "none";

		}
	}

	var tableQueryVal = "none";
	function tableQueryOnOff() {
		if(tableQueryVal=="none") {
			document.getElementById("div_tablequery").style.display = "block"
			tableQueryVal = "block";
		} else {
			document.getElementById("div_tablequery").style.display = "none"
			tableQueryVal = "none";
		}
	}

	var procedureQueryVal = "none";
	function procedureQueryOnOff() {
		if(procedureQueryVal=="none") {
			document.getElementById("div_procedurequery").style.display = "block"
			procedureQueryVal = "block";
		} else {
			document.getElementById("div_procedurequery").style.display = "none"
			procedureQueryVal = "none";
		}
	}

	var procedureListVal = "none";
	function procedureListOnOff() {
		if(procedureListVal=="none") {
			document.getElementById("div_procedurelist").style.display = "block"
			procedureListVal = "block";
		} else {
			document.getElementById("div_procedurelist").style.display = "none"
			procedureListVal = "none";
		}
	}

	function dbCopy1() {
		document.frm.DB2Ip.value = document.frm.DB1Ip.value;
		document.frm.DB2Name.value = document.frm.DB1Name.value;
		document.frm.DB2User.value = document.frm.DB1User.value;
		document.frm.DB2Pwd.value = document.frm.DB1Pwd.value;
	}
	function dbCopy2() {
		document.frm.DB1Ip.value = document.frm.DB2Ip.value;
		document.frm.DB1Name.value = document.frm.DB2Name.value;
		document.frm.DB1User.value = document.frm.DB2User.value;
		document.frm.DB1Pwd.value = document.frm.DB2Pwd.value;
	}
	function dbClear() {
		//document.frm.reset();
		document.frm.DB2Ip.value = "";
		document.frm.DB2Name.value = "";
		document.frm.DB2User.value = "";
		document.frm.DB2Pwd.value = ""
		document.frm.DB1Ip.value = "";
		document.frm.DB1Name.value = "";
		document.frm.DB1User.value = "";
		document.frm.DB1Pwd.value = "";
	}
	</script>
<body>
<h2>DB</h2>

<!-- DB Info Input Form Start  //-->
<div id="layer_fixed">
	<h1 style="margin : -5px 10px 0 10px; float:left">DB checker ver 1.1.0</h1><h4 style="margin : -2px 10px 0 10px;">You're IP : <?=$ip;?></h4><br>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
	<div style="float:left; margin : 0 10px 0 10px;">
		<table style="border:1px;">
		<tr class="tr1 h27">
			<td colspan=2><b>#DB 1  :: Real</b></td>
		</tr>
		<tr>
			<td  class="tr1">DB1 IP</td><td><input type="text" name="DB1Ip" value="<?php echo $DB1Ip; ?>"></td>
		</tr>
		<tr>
			<td  class="tr1">DB1 Name</td><td><input type="text" name="DB1Name" value="<?php echo $DB1Name; ?>"></td>
		</tr>
		<tr>
			<td  class="tr1">DB1 User</td><td><input type="text" name="DB1User" value="<?php echo $DB1User; ?>"></td>
		</tr>
		<tr>
			<td  class="tr1">DB1 Pwd</td><td><input type="password" name="DB1Pwd" value="<?php echo $DB1Pwd; ?>"></td>
		</tr>
		</table>


	</div>
	<div style="float:left; margin-right:10px;">
		<input type="button" value=" >> Text Copy >> " onclick="dbCopy1()" class="width130">
		<br><br>
		<input type="button" value=" << Text Copy << " onclick="dbCopy2()" class="width130">
		<br><br>
		<input type="button" value=" :: Text Clear  :: " onclick="dbClear()" class="width130">
		<br><br>
		<input type="submit" value=" :: Submit  :: " class="button width130">
	</div>
	<div style="float:left; margin-right:10px;">

		<table style="border:1px;">
		<tr class="tr1 h27">
			<td colspan=2><b>#DB 2  :: Dev</b></td>
		</tr>
		<tr>
			<td  class="tr1">DB2 IP</td><td><input type="text" name="DB2Ip" value="<?php echo $DB2Ip; ?>"></td>
		</tr>
		<tr>
			<td  class="tr1">DB2 Name</td><td><input type="text" name="DB2Name" value="<?php echo $DB2Name; ?>"></td>
		</tr>
		<tr>
			<td  class="tr1">DB2 User</td><td><input type="text" name="DB2User" value="<?php echo $DB2User; ?>"></td>
		</tr>
		<tr>
			<td  class="tr1">DB2 Pwd</td><td><input type="password" name="DB2Pwd" value="<?php echo $DB2Pwd; ?>"></td>
		</tr>
		</table>

	</div>
	<div style="float:left; margin-right:10px; padding-top:50px;">
		<div class="resultBtn">>> results >></div>
	</div>
	<div>
		<table style="border:1px;">
		<tr>
			<td  class="tr1 textcenter" colspan=2><h2>Compare results</h2></td>
		</tr>
		<tr>
			<td  class="tr1 textcenter"><h3>TABLE</h3></td><td id="TableResult"> --- </td>
		</tr>
		<tr>
			<td  class="tr1 textcenter"><h3>Procedure</h3></td><td id="ProcedureResult"> --- </td>
		</tr>
		</table>
	</div>
	</form>
</div>
<div style="clear:both"></div>
<!-- DB Info Input Form END  //-->

<?php
// 1) DB password required
// if( ($DB1Ip == "") || ($DB1Name=="")  || ($DB1User=="")  || ($DB1Pwd=="")
// 	&&
// 	($DB2Ip == "")  ||  ($DB2Name=="")  || ($DB2User=="")  || ($DB2Pwd=="")

// 2) DB password blank (for local test)
if( ($DB1Ip == "") || ($DB1Name=="")  || ($DB1User=="")  
 	&&
 	($DB2Ip == "")  ||  ($DB2Name=="")  || ($DB2User=="")
) {
	echo "<div style='margin-top:170px;'></div><h1>DB Info ......... blank</h1>";


} else {

########################## ELSE START ###############


	####################################################
	#################### TABLE START ###################
	####################################################
	$TableField1 = array();
	$TableField2 = array();

	$TableCreate1 = array();
	$TableCreate2 = array();

	$TableComment1 = array();
	$TableComment2 = array();

	$TableDB1 = array();
	$TableDB2 = array();

	$TableEngine1 = array();
	$TableEngine2 = array();
	$TableComment1 = array();
	$TableComment2 = array();


	/*  DB1 Table schema START */
	$conn = mysqli_connect($DB1Ip, $DB1User, $DB1Pwd, $DB1Name);
	if (!$conn)
	{
		echo "<div style='padding-top:110px;'><font style='color:#ff0000; font-weight:bold;'>";
		echo "Error: Unable to connect to MySQL. $DB1Ip (Table)" . PHP_EOL;
		echo "<br>Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
		echo "<br>Debugging error: " . mysqli_connect_error() . PHP_EOL;
		echo "</font></div>"; 

	} else {

		########################## DB Connect START ###############

		/*  DB1 Table schema START */
		$sql = "SELECT TABLE_NAME, ENGINE, TABLE_COMMENT FROM information_schema.tables WHERE table_schema = '$DB1Name' AND TABLE_TYPE='BASE TABLE' ORDER BY table_name;";
		$result = mysqli_query($conn, $sql);
		$TableDB1 = array();

		$RowCnt = 0;
		while ($row = mysqli_fetch_row($result)) {
			$TableDB1[$RowCnt] = isset($row[0]) ? $row[0] : "";
			$TableEngine1[$RowCnt] = isset($row[1]) ? $row[1] : "";
			$TableComment1[$RowCnt] = isset($row[2]) ? $row[2] : "";

			$TB1 = $TableDB1[$RowCnt];
			$result2 = mysqli_query($conn,"SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA, COLUMN_COMMENT FROM information_schema.columns WHERE table_name='$TB1'ORDER BY ORDINAL_POSITION ASC");

			if (mysqli_num_rows($result2) > 0) {
				$num=0;
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$TableField1[$RowCnt][$num] = isset($row2) ? $row2 : "";
					$num++;
				}
			}

			$result3 = mysqli_query($conn,"SHOW CREATE TABLE $TB1");
			$row3 = mysqli_fetch_assoc($result3);
			$TableCreate1[$RowCnt] = isset($row3['Create Table']) ? $row3['Create Table'] : "";

			$RowCnt++;

		}

		mysqli_free_result($result);
		mysqli_free_result($result2);
		mysqli_free_result($result3);
		mysqli_close($conn);
		/*  DB1 Table schema END */


		/*  DB2 Table schema START */
		$conn = mysqli_connect($DB2Ip, $DB2User, $DB2Pwd, $DB2Name);
		echo "=====1======";
		if (!$conn)
		{
			echo "<div style='padding-top:110px;'><font style='color:#ff0000; font-weight:bold;'>";
			echo "Error: Unable to connect to MySQL. $DB2Ip (Table)" . PHP_EOL;
			echo "<br>Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
			echo "<br>Debugging error: " . mysqli_connect_error() . PHP_EOL;
			echo "</font></div>";

			//exit();

		}
		echo "=====2======";

		$sql = "SELECT TABLE_NAME, ENGINE, TABLE_COMMENT FROM information_schema.tables WHERE table_schema = '$DB2Name' AND TABLE_TYPE='BASE TABLE' ORDER BY table_name;";
		$result = mysqli_query($conn, $sql);
		$TableDB2 = array();

		$RowCnt = 0;
		while ($row = mysqli_fetch_row($result)) {
			$TableDB2[$RowCnt] = isset($row[0]) ? $row[0] : "";
			$TableEngine2[$RowCnt] = isset($row[1]) ? $row[1] : "";
			$TableComment2[$RowCnt] = isset($row[2]) ? $row[2] : "";

			$TB2 = $TableDB2[$RowCnt];
			$result2 = mysqli_query($conn,"SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA, COLUMN_COMMENT FROM information_schema.columns WHERE table_name='$TB2'ORDER BY ORDINAL_POSITION ASC");

			if (mysqli_num_rows($result2) > 0) {
				$num=0;
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$TableField2[$RowCnt][$num] = isset($row2) ? $row2 : "";
					$num++;
				}
			}

			$result3 = mysqli_query($conn, "SHOW CREATE TABLE $TB2");
			$row3 = mysqli_fetch_assoc($result3);
			$TableCreate2[$RowCnt] = isset($row3['Create Table']) ? $row3['Create Table'] : "";

			$RowCnt++;
			
		}

		mysqli_free_result($result);
		mysqli_free_result($result2);
		mysqli_free_result($result3);
		mysqli_close($conn);
		/*  DB2 Table schema END */

		$CNT1 = sizeof($TableDB1);
		$CNT2 = sizeof($TableDB2);

		$OnlyTableDB1Cnt = 0;
		$OnlyTableDB2Cnt = 0;
		####################################################
		#################### TABLE END #####################
		####################################################




		####################################################
		#################### PROCEDURE START ###############
		####################################################
		$conn = mysqli_connect($DB1Ip, $DB1User, $DB1Pwd, $DB1Name);
		if (!$conn)
		{
			echo "<div style='padding-top:110px;'><font style='color:#ff0000; font-weight:bold;'>";
			echo "Error: Unable to connect to MySQL. $DB1Ip (Procedure)" . PHP_EOL;
		    echo "<br>Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
		    echo "<br>Debugging error: " . mysqli_connect_error() . PHP_EOL;
			echo "</font></div>";
		}

		$ProName1 = array();
		$ProName2 = array();
		$ProField1 = array();
		$ProField2 = array();

		$OnlyProDB1Cnt = 0;
		$OnlyProDB2Cnt = 0;


		/*  DB1 Procedure START */
		$sql = "SHOW PROCEDURE STATUS WHERE Db = '$DB1Name';";
		$result = mysqli_query($conn, $sql);
		$ProDB1 = array();

		$ProcedureCNT = 0;
		while ($row = mysqli_fetch_row($result)) {

			$ProDB1[$ProcedureCNT] = isset($row['1']) ? $row['1'] : "";
			$PRO1 =  $row['1'];

			$result2 = mysqli_query($conn, "SHOW CREATE PROCEDURE $PRO1");
			if (mysqli_num_rows($result2) > 0) {
				$row2 = mysqli_fetch_assoc($result2);
				$ProName1[$ProcedureCNT] = isset($PRO1) ? $PRO1 : "";
				$ProField1[$ProcedureCNT] = isset($row2['Create Procedure']) ? $row2['Create Procedure'] : "";
			}
			$ProcedureCNT++;
		}

		mysqli_free_result($result);
		mysqli_close($conn);
		/*  DB1 Procedure END  */


		/*  DB2 Procedure START */
		$conn = mysqli_connect($DB2Ip, $DB2User, $DB2Pwd, $DB2Name);
		if($conn === false)
		{
			echo "<div style='padding-top:110px;'><font style='color:#ff0000; font-weight:bold;'>";
			echo "Error: Unable to connect to MySQL. $DB2Ip (Procedure)" . PHP_EOL;
		    echo "<br>Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
		    echo "<br>Debugging error: " . mysqli_connect_error() . PHP_EOL;
			echo "</font></div>";
		}

		$sql = "SHOW PROCEDURE STATUS WHERE Db = '$DB2Name';";
		$result = mysqli_query($conn, $sql);
		$ProDB2 = array();

		$ProcedureCNT = 0;
		while ($row = mysqli_fetch_row($result)) {

			//array_push($ProDB2, $row['1']);
			$ProDB2[$ProcedureCNT] = isset($row['1']) ? $row['1'] : "";
			$PRO2 =  $row['1'];

			$result2 = mysqli_query($conn, "SHOW CREATE PROCEDURE $PRO2");
			if (mysqli_num_rows($result2) > 0) {
				$row2 = mysqli_fetch_assoc($result2);
				$ProName2[$ProcedureCNT] = isset($PRO2) ? $PRO2 : "";
				$ProField2[$ProcedureCNT] = isset($row2['Create Procedure']) ? $row2['Create Procedure'] : "";
			}
			$ProcedureCNT++;

		}

		mysqli_free_result($result);
		mysqli_close($conn);
		/*  DB2 Procedure END */

		$CNT91 = sizeof($ProDB1);
		$CNT92 = sizeof($ProDB2);
		####################################################
		#################### PROCEDURE END #################
		####################################################
		?>


		<!-- Top Margin : 130px; //-->
		<div style="margin-top:130px;"></div>

		<hr>
		<h2>#### Table Compare ###</h2>
		<div style="float:left; margin-right:10px;">

			<table border=1>
			<tr class="tr1">
				<td colspan=3><b>Real</b></td>
			</tr>
			<tr class="tr1">
				<td colspan=3><b>1. <?php echo  "'".$DB1Name."' Table List"; ?></b></td>
			</tr>
			<tr class="tr1">
				<td>No</td><td>Table Name</td><td>field count</td>
			</tr>
			<?php
			$fieldCnt1 = 0;
			for($i=0; $i<$CNT1; $i++) {
				echo "<tr>";
				echo "<td class='td1'>".($i+1)."</td>";
				echo "<td>";
					if (!in_array($TableDB1[$i], $TableDB2)) {
						echo "<font color=red><b>".$TableDB1[$i]."</b></font><br>";
						$OnlyTableDB1Cnt++;
					} else {
						echo $TableDB1[$i]."<br>";
					}
				$fieldCnt1 += sizeof($TableField1[$i]);
				echo "</td>";
				echo "<td class='td1'>".sizeof($TableField1[$i])."</td>";
				echo "</tr>";
			}
			?>
			<tr class="tr1"><td colspan=2>Total Table Count</td><td class='td1'><b><?php echo $CNT1; ?></b></td></tr>
			<tr class="tr1"><td colspan=2>Total Field Count</td><td class='td1'><b><?php echo $fieldCnt1; ?></b></td></tr>
			<tr class="tr1"><td colspan=3>Table Count exists only at '<?php echo  $DB1Name;?>' : <font color=red><b><?php echo $OnlyTableDB1Cnt; ?></b></font></td></tr>
			</table>

		</div>
		<div style="float:left; margin-right:10px;">
		&lt;&lt;
		</div>
		<div>

			<table border=1>
			<tr class="tr1">
				<td colspan=3><b>Dev</b></td>
			</tr>
			<tr class="tr1">
				<td colspan=3><b>2. <?php echo  "'".$DB2Name."' Table List"; ?></b></td>
			</tr>
			<tr class="tr1">
				<td>No</td><td>Table Name</td><td>field count</td>
			</tr>
			<?php
			$fieldCnt2 = 0;
			for($i=0; $i<$CNT2; $i++) {
				echo "<tr>";
				echo "<td class='td1'>".($i+1)."</td>";
				echo "<td>";
					if (!in_array($TableDB2[$i], $TableDB1)) {
						echo "<font color=red><b>".$TableDB2[$i]."</b></font><br>";
						$OnlyTableDB2Cnt++;
					} else {
						echo $TableDB2[$i]."<br>";
					}
				$fieldCnt2 += sizeof($TableField2[$i]);
				echo "</td>";
				echo "<td class='td1'>".sizeof($TableField2[$i])."</td>";
				echo "</tr>";
			}
			?>
			<tr class="tr1"><td colspan=2>Total Table Count</td><td class='td1'><b><?php echo $CNT2; ?></b></td></tr>
			<tr class="tr1"><td colspan=2>Total Field Count</td><td class='td1'><b><?php echo $fieldCnt2; ?></b></td></tr>
			<tr class="tr1"><td colspan=3>Table Count exists only at '<?php echo  $DB2Name;?>' : <font color=red><b><?php echo $OnlyTableDB2Cnt; ?></b></font></td></tr>
			</table>

		</div>
		<div style="clear:both"></div>



		<br><input type="button" value="TABLE List on/Off" onclick="javascript:tableViewOnOff();">

		<div id="div_tableview" style="display:none">
			<!-- TABLE List START -->
			<hr>
			<table  style="border:0px;">
			<tr><td valign="top">

				<h2><?php echo  $DB1Name;?> >> TABLE </h2>
				<?php

				for($i=0; $i<$CNT1; $i++) {

					$Table = $TableDB1[$i];
					?>
					<table border=1 width="800px">
					<tr class="tr1">
						<td colspan=8><b><?php echo $DB1Name.".".$Table; ?></b></td>
					</tr>
					<tr class="tr1">
						<td colspan=8><?=nl2br($TableComment1[$i])?></td>
					</tr>
					<tr class="tr1" style="font-weight:bold;">
						<td>No</td><td>Field</td><td>Type</td><td>Null</td><td>Key</td><td>Default</td><td>Extra</td><td>Comment</td>
					</tr>
					<?php
					$FieldCnt = sizeof($TableField1[$i]);
					for($kk=0; $kk<$FieldCnt; $kk++) {
						echo "<tr>";
						echo "<td class='td1'>".$kk."</td>";
						echo "<td>".$TableField1[$i][$kk]['COLUMN_NAME']."</td>";
						echo "<td>".$TableField1[$i][$kk]['COLUMN_TYPE']."</td>";
						echo "<td>".$TableField1[$i][$kk]['IS_NULLABLE']."</td>";
						echo "<td>".$TableField1[$i][$kk]['COLUMN_KEY']."</td>";
						echo "<td>".$TableField1[$i][$kk]['COLUMN_DEFAULT']."</td>";
						echo "<td>".$TableField1[$i][$kk]['EXTRA']."</td>";
						echo "<td>".$TableField1[$i][$kk]['COLUMN_COMMENT']."</td>";
						echo "</tr>";
					}
					?>
					<tr><td colspan=8><?php echo nl2br($TableCreate1[$i]); ?></td></tr>
					</table><br>
					<?php

				}
				?>

			</td>
			<td  style="padding-left:20px;" valign="top">

				<h2><?php echo  $DB2Name;?> >> TABLE </h2>
				<?php

				for($i=0; $i<$CNT2; $i++) {

					$Table = $TableDB2[$i];
					?>
					<table border=1 width="800px">
					<tr class="tr1">
						<td colspan=8><b><?php echo $DB2Name.".".$Table; ?></b></td>
					</tr>
					<tr class="tr1">
						<td colspan=8><?=nl2br($TableComment2[$i])?></td>
					</tr>
					<tr class="tr1" style="font-weight:bold;">
						<td>No</td><td>Field</td><td>Type</td><td>Null</td><td>Key</td><td>Default</td><td>Extra</td><td>Comment</td>
					</tr>
					<?php
					$FieldCnt = sizeof($TableField2[$i]);
					for($kk=0; $kk<$FieldCnt; $kk++) {
						echo "<tr>";
						echo "<td class='td1'>".$kk."</td>";
						echo "<td>".$TableField2[$i][$kk]['COLUMN_NAME']."</td>";
						echo "<td>".$TableField2[$i][$kk]['COLUMN_TYPE']."</td>";
						echo "<td>".$TableField2[$i][$kk]['IS_NULLABLE']."</td>";
						echo "<td>".$TableField2[$i][$kk]['COLUMN_KEY']."</td>";
						echo "<td>".$TableField2[$i][$kk]['COLUMN_DEFAULT']."</td>";
						echo "<td>".$TableField2[$i][$kk]['EXTRA']."</td>";
						echo "<td>".$TableField2[$i][$kk]['COLUMN_COMMENT']."</td>";
						echo "</tr>";
						echo "</tr>";
					}
					?>
					<tr><td colspan=8><?php echo nl2br($TableCreate2[$i]); ?></td></tr>
					</table><br>
					<?php

				}
				?>

			</td></tr>
			</table>
			<!-- TABLE List END -->
		</div>

		<input type="button" value="TABLE Query on/Off" onclick="javascript:tableQueryOnOff();">

		<div id="div_tablequery" style="display:none">
			<hr>
			<h2><?php echo  $DB1Name;?> is Real DB / <?php echo  $DB2Name;?> is Development DB :: Table Field Compare  -- Query</h2>
			<?php

			$TableChk = 0;

			for($i=0; $i<$CNT1; $i++) {

				$Table = $TableDB1[$i];
				$key = array_search($Table, $TableDB2);

				echo "<br><b>===== $Table =======</b> ";

				if( ($key ===0) || ($key) ) {

					############
					#
					# 1. ALTER TABLE _____  MODIFY
					#
					############
					$FieldCnt = sizeof($TableField1[$i]);
					$FieldOutputsequence = "";
					
					for($kk=0; $kk<$FieldCnt; $kk++) {

						 $tmp1Field = $TableField1[$i][$kk]['COLUMN_NAME'];
						 $tmp1Type = $TableField1[$i][$kk]['COLUMN_TYPE'];
						 $tmp1Key = $TableField1[$i][$kk]['COLUMN_KEY'];

						 $tmp2Field = isset($TableField2[$key][$kk]['COLUMN_NAME']) ? $TableField2[$key][$kk]['COLUMN_NAME'] : "";
						 $tmp2Type = isset($TableField2[$key][$kk]['COLUMN_TYPE']) ? $TableField2[$key][$kk]['COLUMN_TYPE'] : "";
						 $tmp2Null = isset($TableField2[$key][$kk]['IS_NULLABLE']) ? $TableField2[$key][$kk]['IS_NULLABLE'] : "";
						 $tmp2Default = isset($TableField2[$key][$kk]['COLUMN_DEFAULT']) ? $TableField2[$key][$kk]['COLUMN_DEFAULT'] : "";
						 $tmp2Key = isset($TableField2[$key][$kk]['COLUMN_KEY']) ? $TableField2[$key][$kk]['COLUMN_KEY'] : "";

						 
						$cnt22 = sizeof( $TableField2[$key]);

						$findFieldNo = -1;
						for($mm=0; $mm< $cnt22 ; $mm++) { 
							if(   $TableField1[$i][$kk]['COLUMN_NAME'] == $TableField2[$key][$mm]['COLUMN_NAME']	 ) { 
								$findFieldNo = $mm;
							}
						 }
						 if( ($FieldOutputsequence == "") && ($kk <> $findFieldNo ) ) { 

							$FieldOutputsequence = 1;
								echo "<br><font color=B200FF><b>$Table  :: [The order of the field is different.   >>  Be careful] </b></font>";
						 }
						 
						 $prnModifyNull = '';
						 if($tmp2Null == 'NO') {  $prnModifyNull  = 'NOT NULL';}
						 $prnModifyDefault  = '';
						 if($tmp2Default <> '') {  $prnModifyDefault  = "DEFAULT  '$tmp2Default'";}

						 if( ($tmp1Field ) && ($tmp2Field <> "")  && (($tmp1Field <> $tmp2Field) || ($tmp1Type <> $tmp2Type))) {
							$TableChk++;
							echo "<br>ALTER TABLE $Table  MODIFY $tmp1Field  $tmp2Type $prnModifyNull $prnModifyDefault;  ";
						 }

						 if( $tmp1Key != $tmp2Key) {
							 $TableChk++;
							 echo "<br><font color='#0000ff'>[Key - index] INDEX Check  ::::: ex) CREATE  INDEX   ____ ON  $Table ON $tmp1Field ---------------------</font>";
						 }


					}

					echo "<br>";

					############
					#
					# 2. ALTER TABLE _____  ADD COLUMN
					#
					############
					$FieldCnt2 = sizeof($TableField2[$i]);
					for($kk=0; $kk<$FieldCnt2; $kk++) {

						 $tmp2Field = isset($TableField2[$key][$kk]['COLUMN_NAME']) ? $TableField2[$key][$kk]['COLUMN_NAME'] : "";
						 $tmp2Type = isset($TableField2[$key][$kk]['COLUMN_TYPE']) ? $TableField2[$key][$kk]['COLUMN_TYPE'] : "";
						 $tmp2Null = isset($TableField2[$key][$kk]['IS_NULLABLE']) ? $TableField2[$key][$kk]['IS_NULLABLE'] : "";
						 $tmp2Default = isset($TableField2[$key][$kk]['COLUMN_DEFAULT']) ? $TableField2[$key][$kk]['COLUMN_DEFAULT'] : "";					 

						 if( $tmp2Field ) {

							$chkField = "";
							for($mm=0; $mm<$FieldCnt; $mm++) {
								if( $tmp2Field  == $TableField1[$i][$mm]['COLUMN_NAME']) {
									$chkField = "111";
								}
							}

							 $prnModifyNull = '';
							 if($tmp2Null == 'NO') {  $prnModifyNull  = 'NOT NULL';}
							 $prnModifyDefault  = '';
							 if($tmp2Default <> '') {  $prnModifyDefault  = "DEFAULT  '$tmp2Default'";}

							if( $chkField == "") {
								$TableChk++;
								echo "<br>ALTER TABLE $Table  ADD COLUMN   $tmp2Field  $tmp2Type $prnModifyNull $prnModifyDefault; ";
							}

						 }

					}

				} else {
					 $TableChk++;
						echo "<br><font color=B200FF><b>$Table  :: [Only Real Server  >>  Be careful] </b></font>";
				}

			}


			for($i=0; $i<$CNT2; $i++) {

				$Table = $TableDB2[$i];
				$key = array_search($Table, $TableDB1);

				if( ($key===0) || ($key) ) {

				} else {
					$TableChk++;
					echo "<br><font color=red><b>$Table  :: [Real Server not exists   >> create table]</b></font>";
					echo "<br>".$TableCreate2[$i];
				}

			}
			?>
		</div>


		<hr>

		<h2>#### Procedure Compare ###</h2>
		<div style="float:left; margin-right:10px;">

			<table border=1>
			<tr class="tr1">
				<td colspan=2><b>1. <?php echo  $DB1Name." Procedure List"; ?></b></td>
			</tr>
			<tr class="tr1">
				<td>No</td><td>Procedure  Name</td>
			</tr>
			<?php
			for($i=0; $i<$CNT91; $i++) {
				$No = $i+1;
				echo "<tr>";
				echo "<td class='td1'>".$No."</td>";
				echo "<td>";
					if (!in_array($ProDB1[$i], $ProDB2)) {
						echo "<font color=red><b>".$ProDB1[$i]."</b></font><br>";
						$OnlyProDB1Cnt++;
					} else {
						echo $ProDB1[$i]."<br>";
					}
				echo "</td>";
				echo "</tr>";
			}
			?>
			<tr class="tr1"><td colspan=2>Total Procedure Count : <b><?php echo $CNT91; ?></b></td></tr>
			<tr class="tr1"><td colspan=2>Procedure Count exists only at '<?php echo  $DB1Name;?>' : <font color=red><b><?php echo $OnlyProDB1Cnt; ?></b></font></td></tr>
			</table>

		</div>
		<div style="float:left; margin-right:10px;">
		&lt;&lt;
		</div>
		<div >

			<table border=1>
			<tr class="tr1">
				<td colspan=2><b>1. <?php echo  $DB2Name." Procedure List"; ?></b></td>
			</tr>
			<tr class="tr1">
				<td>No</td><td>Procedure  Name</td>
			</tr>
			<?php
			for($i=0; $i<$CNT92; $i++) {
				$No = $i+1;
				echo "<tr>";
				echo "<td class='td1'>".$No."</td>";
				echo "<td>";
					if (!in_array($ProDB2[$i], $ProDB1)) {
						echo "<font color=red><b>".$ProDB2[$i]."</b></font><br>";
						$OnlyProDB2Cnt  ++;
					} else {
						echo $ProDB2[$i]."<br>";
					}
				echo "</td>";
				echo "</tr>";
			}
			?>
			<tr class="tr1"><td colspan=2>Total Procedure Count  : <b><?php echo $CNT92; ?></b></td></tr>
			<tr class="tr1"><td colspan=2>Procedure Count exists only at '<?php echo  $DB2Name;?>': <font color=red><b><?php echo $OnlyProDB2Cnt; ?></b></font></td></tr>
			</table>

		</div>


		<div style="clear:both"></div>



		<?php
		$CNTPro1 = sizeof($ProField1);
		$CNTPro2 = sizeof($ProField2);
		?>
		<br><input type="button" value="Procedure List on/Off" onclick="javascript:procedureListOnOff();">

		<div id="div_procedurelist" style="display:none">
			<!-- Procedure List START -->
			<hr>
			<table  style="border:0px;">
			<tr><td valign="top">

				<h2><?php echo  $DB1Name;?> >> Procedure </h2>
				<?php

				for($i=0; $i<$CNTPro1; $i++) {

					$Table = $ProField1[$i];
					?>
					<table border=1 width="650px">
					<tr class="tr1">
						<td><b><?php echo "1.[PROCEDURE][$i] - ".$DB1Name.".".$ProName1[$i]; ?></b></td>
					</tr>
					<tr>
						<td><xmp><?php echo $ProField1[$i]; ?></xmp></td>
					</tr>
					</table><br>
					<?php

				}
				?>

			</td>
			<td  style="padding-left:20px;" valign="top">

				<h2><?php echo  $DB2Name;?> >> Procedure </h2>
				<?php

				for($i=0; $i<$CNTPro2; $i++) {

					$Table = $ProField2[$i];
					?>
					<table border=1 width="650px">
					<tr class="tr1">
						<td><b><?php echo "2.[PROCEDURE][$i] - ".$DB2Name.".".$ProName2[$i]; ?></b></td>
					</tr>
					<tr>
						<td><xmp><?php echo $ProField2[$i]; ?></xmp></td>
					</tr>
					</table><br>
					<?php

				}
				?>

			</td></tr>
			</table>
			<!-- Procedure List END -->
		</div>


		<input type="button" value="Procedure Query on/Off" onclick="javascript:procedureQueryOnOff();">

		<div id="div_procedurequery" style="display:none">
			<hr>

			<h2><?php echo  $DB1Name;?> is Real DB / <?php echo  $DB2Name;?> is Development DB :: Procedure Compare  -- Query</h2>
			<?php

			$ProChk = 0;

			for($i=0; $i<$CNTPro1; $i++) {

				$ProName = $ProName1[$i];
				$key = "";
				$key = array_search($ProName, $ProName2);

				echo "<br><b>===== Procedure : $ProName =======</b>";

				$key = isset($key) ? $key : 0;
				
				$text1 = strtr($ProField1[$i],array("\r\n"=>'',"\r"=>'',"\n"=>''));
				if($key>0) {
					$text2 = strtr($ProField2[$key],array("\r\n"=>'',"\r"=>'',"\n"=>''));
				} else { 
					$text2 = "";
				}
				
				if( ($text1  == $text2)  && ($key>-1)) {
					echo "  :: <font color=#00f><b>pass</b></font> ";
				} else if(  ($text1  <> $text2) && ($key>-1)  )  {
					$ProChk++;
					//echo "<br> DROP PROCEDURE IF EXISTS $ProName; ";
					//echo "<br><xmp>$ProField2[$key];</xmp> ";
					//echo "<br>$ProField2[$key];";
					echo "  :: <font color=#f00><b>different</b></font> ";

					echo "<div class=greybox>";
					$diff = Diff::compare($ProField1[$i],$ProField2[$key]);
					echo Diff::toTable($diff);
					echo "</div>";


				} else { 
					$ProChk++;
					echo "  :: <font color=#B200FF><b> [Only Real Server  >> Be careful] </b></font>";
				}
			}

			for($i=0; $i<$CNTPro2; $i++) {

				$ProName = $ProName2[$i];
				$key = array_search($ProName, $ProName1);

				if( ($key===0) || ($key) ) {

				} else {

					$ProChk++;
					echo "<br><font color=#f00><b>===== CREATE Procedure : [$ProName] =======</b></font> ";
					echo "<div class=greybox>";
					echo nl2br($ProField2[$i]).";</div>";

				}
			}


			/* ####################### Compare results Print ################# */
			if($TableChk == 0) {
				$TableChkHtml = "<span class='chkeckSuccess'>Success >> the same </span>";
			} else {
				$TableChkHtml = "<span class='chkeckDifferent'>Different </span>";
			}

			if($ProChk == 0) {
				$ProChkHtml = "<span class='chkeckSuccess'>Success >> the same </span>";
			} else {
				$ProChkHtml = "<span  class='chkeckDifferent'>Different </span>";
			}
			?>
		</div>

		<script>
			document.getElementById("TableResult").innerHTML = "<?php echo $TableChkHtml.'('.$TableChk.')' ; ?>";
			document.getElementById("ProcedureResult").innerHTML = "<?php echo $ProChkHtml.'('.$ProChk.')'; ?>";
		</script>

<?php

	########################## DB Connect END ###############
	}

}
########################## ELSE END ###############
?>

<div style="clear:both"></div>
<hr>
<?php 
/* Page loading time check (END time) */
echo "<br>Page Loading Time : ". ( _microtime() - $time_start ). " sec"; 
?>
</body>
</html>
