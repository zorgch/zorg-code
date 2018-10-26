<?php
/**
 * File includes
 * @include main.inc.php Includes the Main Zorg Configs and Methods
 * @include setiathome.inc.php Includes SETI@home setiathome() Class and Methods
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/setiathome.inc.php');


if($_GET['update'] == 1) {
	setiathome::update_group();	
	header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?".session_name()."=".session_id());
} else {
	
	//echo head(63);
	$smarty->assign('tplroot', array('page_title' => 'SETI@Home'));
	$smarty->display('file:layout/head.tpl');
	echo menu('main');
	echo menu('mischt');
	echo menu('seti');
	
	$sql = "
	SELECT 
	count(s.name) as number, 
	sum(s.num_results) as results, 
	sum(s.total_cpu) as time,
	sum(s.num_results) - sum(st.num_results) as diff
	FROM seti s
	INNER JOIN seti_tage st
		ON st.name = s.name
	WHERE 
		DAY(st.datum) = DAY(now()) 
		AND 
		YEAR(st.datum) = YEAR(now()) 
		AND 
		MONTH(st.datum) = MONTH(now())";
	$result = $db->query($sql);
	if(!$db->num($result)) {
		$sql = "
		SELECT 
		count(s.name) as number, 
		sum(s.num_results) as results, 
		sum(s.total_cpu) as time,
		0 as diff
		FROM seti s";
		$result = $db->query($sql);
	}
	$group = $db->fetch($result);
	
	echo "
	<table width='80%' cellpadding='3' cellspacing='1' bgcolor=".TABLEBACKGROUNDCOLOR.">
	<tr><td align='center' colspan='6' bgcolor=".BORDERCOLOR."><b>SETI - zooomclan.org</b></td></tr>
	<tr><td align='left' colspan='3' bgcolor=".BACKGROUNDCOLOR."><B>Total Units: ".$group['results']."<sup>+".$group['diff']."</sup></B></td>
	<td align='left' colspan='3' bgcolor=".BACKGROUNDCOLOR."><B>Total CPU Zeit: ".setiathome::seti_time($group['time'])."</B></td></tr>
	<tr><td align='left' bgcolor=".BACKGROUNDCOLOR." colspan='2'><b>Name</b></td>
	<td align='left' bgcolor=".BACKGROUNDCOLOR."><b>Units</b></td>
	<td align='left' bgcolor=".BACKGROUNDCOLOR."><b>CPU Zeit</b></td>
	<td align='left' bgcolor=".BACKGROUNDCOLOR."><b>Durchschn. Zeit</b></td>
	<td align='left' bgcolor=".BACKGROUNDCOLOR."><b>Letztes Unit</b></td></tr>";
	
	$secadd = (date("I",time()) ? 7200 : 3600);
	$sql = "
	SELECT 
		s.*, 
		(UNIX_TIMESTAMP(s.date_last_result) + $secadd) as date_last_result, 
		st.num_results as last_num 
	FROM seti s
	LEFT JOIN seti_tage st
		ON st.name = s.name
	WHERE 
		YEAR(st.datum) = YEAR(now()) AND DAY(st.datum) = DAY(now()) AND MONTH(st.datum) = MONTH(now())
	ORDER by s.num_results DESC";
	$result = $db->query($sql);
	if(!$db->num($result)) {
		$sql = "
		SELECT 
			s.*,
			(UNIX_TIMESTAMP(s.date_last_result) + 7200) as date_last_result,
			num_results as last_num
		FROM seti s
		ORDER by s.num_results DESC	";
		$result = $db->query($sql,__FILE__,__LINE__);
	}
	$i = 1;
	while($rs = $db->fetch($result)) {
		if(($i % 2) == 0) {	
			$add = " bgcolor=".TABLEBACKGROUNDCOLOR." "; 
		} else { 
			$add = " bgcolor=".BACKGROUNDCOLOR." "; 
		}
		if($rs['num_results'] > $rs['last_num']) {
			$add2 = "<sup>+".($rs['num_results'] - $rs['last_num'])."</sup>";
			$add = " bgcolor=".MENUCOLOR2." ";
		} else {
			$add2 = "";
		}
		echo "
		<tr>
		<td align='left' $add>".$i."</td>
		<td align='left' $add>".$rs['name']."</td>
		<td align='left' $add>".$rs['num_results']."$add2</td>
		<td align='left' $add>".setiathome::seti_time($rs['total_cpu'])."</td>
	<td align='left' $add>".$rs['avg_cpu']."</td>
	<td align='left' $add>".datename($rs['date_last_result'])."</td></tr>";	
	$i++;
	}
	echo "</table>";

	//echo foot(1);
	$smarty->display('file:layout/footer.tpl');
}
