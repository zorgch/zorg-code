<?php
/**
 * SETI@Home Stats for zooomclan
 *
 * @author [z]keep3r
 * @package zorg\SETI
 */

/**
 * File includes
 * @include main.inc.php Includes the Main Zorg Configs and Methods
 * @include setiathome.inc.php Includes SETI@home setiathome() Class and Methods
 * @include core.model.php required
 */
require_once __DIR__.'/includes/main.inc.php';
require_once INCLUDES_DIR.'setiathome.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Seti();
$seti = new setiathome();

/**
 * Validate GET-Parameters
 */
if (!empty($_GET['update'])) $doAction = (string)$_GET['update'];

if($doAction === 'true')
{
	if ($user->is_loggedin() && $user->typ >= USER_MEMBER)
	{
		$seti->update_group();
		header('Location: '.getURL(false,false));
	} else {
		$model->showOverview($smarty);
		$smarty->display('file:layout/head.tpl');
		echo 'Hier dürfen nur Member was machen. Tschau.';
		$smarty->display('file:layout/footer.tpl');
	}
} else {
	$model->showOverview($smarty);
	$smarty->display('file:layout/head.tpl');

	$sql = 'SELECT count(s.name) as number, sum(s.num_results) as results, sum(s.total_cpu) as time, sum(s.num_results) - sum(st.num_results) as diff
			FROM seti s INNER JOIN seti_tage st ON st.name = s.name WHERE DAY(st.datum)=DAY(?) AND YEAR(st.datum)=YEAR(?) AND MONTH(st.datum)=MONTH(?)';
			$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [timestamp(true), timestamp(true), timestamp(true)]);
	if(!$db->num($result)) {
		$sql = 'SELECT COUNT(s.name) as number, SUM(s.num_results) as results, SUM(s.total_cpu) as time, 0 as diff FROM seti s';
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	}
	$group = $db->fetch($result);

	echo "<h1>SETI - zooomclan.org</h1>
	<h3>Total Units: ".$group['results']."<sup>+".$group['diff']."</sup></h3>
	<h3>Total CPU Zeit: ".$seti->seti_time($group['time'])."</h3>
	<table cellpadding='3' cellspacing='1' bgcolor=".TABLEBACKGROUNDCOLOR.">
		<tr><td align='center' colspan='6' bgcolor=".BORDERCOLOR."><b></b></td></tr>
		<tr>
			<td align='left' bgcolor=".BACKGROUNDCOLOR." colspan='2'><b>Name</b></td>
			<td align='left' bgcolor=".BACKGROUNDCOLOR."><b>Units</b></td>
			<td align='left' bgcolor=".BACKGROUNDCOLOR." class='hide-mobile'><b>CPU Zeit</b></td>
			<td align='left' bgcolor=".BACKGROUNDCOLOR." class='hide-mobile'><b>Durchschn. Zeit</b></td>
			<td align='left' bgcolor=".BACKGROUNDCOLOR."><b>Letztes Unit</b></td></tr>";

	$secadd = (date("I",time()) ? 7200 : 3600);
	$sql = 'SELECT s.*, (UNIX_TIMESTAMP(s.date_last_result) + ?) as date_last_result, st.num_results as last_num FROM seti s
				LEFT JOIN seti_tage st ON st.name = s.name
			WHERE YEAR(st.datum)=YEAR(?) AND DAY(st.datum)=DAY(?) AND MONTH(st.datum)=MONTH(?) ORDER by s.num_results DESC';
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$secadd, timestamp(true), timestamp(true), timestamp(true)]);
	if(!$db->num($result)) {
		$sql = 'SELECT s.*, (UNIX_TIMESTAMP(s.date_last_result) + 7200) as date_last_result, num_results as last_num FROM seti s ORDER by s.num_results DESC';
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
		echo "<tr>
			<td align='left' $add>".$i."</td>
			<td align='left' $add>".$rs['name']."</td>
			<td align='left' $add>".$rs['num_results']."$add2</td>
			<td align='left' $add class='hide-mobile'>".setiathome::seti_time($rs['total_cpu'])."</td>
			<td align='left' $add class='hide-mobile'>".$rs['avg_cpu']."</td>
			<td align='left' $add>".datename($rs['date_last_result'])."</td>
		</tr>";
		$i++;
	}
	echo "</table>";

	$smarty->display('file:layout/footer.tpl');
}
