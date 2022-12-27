<?php
require_once dirname(__FILE__).'/main.inc.php';

/** Query errors Table for all open errors (status = 1) */
$sql = $db->fetch($db->query('SELECT count(*) as num_errors FROM sql_error WHERE status = 1', __FILE__, __LINE__, 'SELECT num_errors'));
$num_errors = $sql['num_errors'];

/**
 * Get all SQL-Error Entries from the database
 *
 * @version 1.1
 * @since 1.0 function added
 * @since 1.1 `17.04.2020` `IneX` SQL Slow-Query optimization
 */
function get_sql_errors($num=23,$order=3,$oby=0) {
	global $db, $num_errors;
	if($num_errors > 0) {
		if(!isset($_SESSION['error_order'])) {
			$_SESSION['error_num'] = $num;
			$_SESSION['error_order'] = $order;
			$_SESSION['error_oby'] = $oby;
		}

		if($_GET['o']) {
			if($_SESSION['error_order'] == $_GET['o']) {
				$_SESSION['error_oby'] = 1 ? $_SESSION['error_oby'] == 0 : 0;
				$_SESSION['error_order'] = $_GET['o'];

			} else {
				$_SESSION['error_order'] = $_GET['o'];
			}
		}

		$order_by = array('','u.username', 's.page', 's.file', 's.date', 's.referer');
		$by = array('DESC','ASC');
		$sql = 'SELECT
					COALESCE(u.username,"ausgeloggt") AS username,
					COALESCE(u.last_ip,"ausgeloggt") AS host,
					s.page,
					s.file,
					s.line,
					s.function,
					s.query,
					s.msg,
					s.referrer,
					s.id,
					s.s_date AS datum
				FROM
					(SELECT
			            s.page AS page,
			            s.file AS file,
			            s.line AS line,
			            s.function AS function,
			            s.query AS query,
			            s.msg AS msg,
			            s.referer AS referrer,
			            s.id AS id,
			            UNIX_TIMESTAMP(s.date) AS s_date,
			            s.user_id AS s_user_id
			        FROM
			            sql_error s
			        WHERE
			            s.status = 1
			        ORDER BY '.$order_by[$_SESSION['error_order']].' '.$by[$_SESSION['error_oby']].'
			        LIMIT '.$_SESSION['error_num'].') s
				LEFT JOIN user u ON u.id = s.s_user_id
				WHERE 1 = 1';
		$result = $db->query($sql,__FILE__,__LINE__);
		$html = '';
		if(!$_GET['id'])
		{
			$html .= "
			<script language='javascript'>
			function selectAll() {
			for(i=0; i < (".$num_errors."); i++)
			document.error_form.elements[i].checked = !document.error_form.elements[i].checked;
			}
			</script>
			<form action='/actions/error_action.php?tpl=$_GET[tpl]' name='error_form' method='post'>";
		}

		$html .= "
		<table class='border'>
			<tr>
				<td align='center'><b><a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=1'>User</a></b></td>
				<td align='center' class='hide-mobile'><b><a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=2'>Page</a></b></td>
				<td align='center' class='hide-mobile'><b><a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=5'>Referrer</a></b></td>
				<td align='center' class='hide-mobile'><b><a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=3'>File</a></b></td>
				<td align='center' class='hide-mobile'><b><b>Line</b></td>
				<td align='center'><b>SQL</b></td>
				<td align='center'><b><a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=4'>Datum</a></b></td>
			";
			if(!$_GET['id']) $html .= '<td align="right" class="hide-mobile"><b>del</b></td>';

		$html .= "</tr>";
		$i = 0;
		while($rs = $db->fetch($result)) {

			if(($i % 2) == 0) {
				$add = " bgcolor=".TABLEBACKGROUNDCOLOR." ";
			} else {
				$add = " bgcolor=".BACKGROUNDCOLOR." ";
			}

			$i++;

			$html .= '
				<tr '.$add.'>
					<td align="left"><small>'.$rs['username'].'</small></td>
					<td align="left" class="hide-mobile"><small>'.substr($rs['page'],0,23).'...</small></td>
					<td align="left" class="hide-mobile"><small>'.substr(str_replace('http://'.$_SERVER['SERVER_NAME'],'', $rs['referrer']),0,23).'...</small></td>
					<td align="left" class="hide-mobile"><small>'.str_replace($_SERVER['DOCUMENT_ROOT'],'',$rs['file']).'</small></td>
					<td align="left" class="hide-mobile"><small>'.$rs['line'].'</small></td>
					<td align="left"><small><a href="'.$_SERVER['PHP_SELF'].'?tpl='.$_GET['tpl'].'&id='.$rs['id'].'">'.substr($rs['query'],0,23).'...</a></small></td>
					<td align="left"><small>'.datename($rs['datum']).'</small></td>';

				if(!$_GET['id']) $html .= '<td align="right" '.$add.' class="hide-mobile"><input type="checkbox" name="to_del[]" value="'.$rs['id'].'"></td>';

			$html .= '</tr>';

			if($_GET['id'] == $rs['id']) {
				if($_GET['query']) {
					$result_chk = $db->query(stripslashes(base64url_decode($_GET['query'])));
					if(!$result_chk) {
						$check = mysqli_error($db->conn);
					} else {
						$check = "Keine Fehler: ".$db->num($result_chk)." Rows";
					}
					$rs['query'] = stripslashes(base64url_decode($_GET['query']));
				}

				$html .= "
				<tr>
					<td align='left' colspan='7'>
				<form action='/actions/error_action.php?tpl=".$_GET['tpl']."&id=".$rs['id']."' method='post'>
				<table class='border'>
				<tr>
					<td align='center' valign='top'><small>".$rs['msg']."</small></td>
				</tr>
				<tr>
					<td align ='left'><small><b><textarea name='query' cols='130' rows='".(substr_count(nl2br($rs['query']),"<br />")+2)."'>".stripslashes(trim($rs['query']))."</textarea></b><br>";
					if($_GET['query']) {
						$html .= "<b>$check</b><br>";
					}

					$html .= "<input type='submit' class='button' value='execute'><br>";
					if($rs['file'] != "" && $rs['line'] != "") {
						$html .= "File: ".$rs['file']."<br>Line: ".$rs['line']."<br>";
					} else {
						$html .= "<br><b>Bitte ".htmlentities("\$db->query(\"SQL\",__FILE__,__LINE__);")." anwenden!<br>";
					}
					if($rs['function'] != "") {
						$html .= "Function: ".$rs['function']."<br />";
					}
					$html .= "Aufgetretten: ".$rs['page']." um ".datename($rs['datum'])."<br>User: ".$rs['username']." <br />";
					if($rs['username'] != "ausgeloggt") {
						$html .= "Host: ".$rs['host']."<br />";
					}
					if($rs['referrer'] != "") {
						$html .= "Ausgangsseite: ".str_replace("http://".$_SERVER['SERVER_NAME'],"",$rs['referrer']);
					}
					$html .= "</small></td>
				</tr>
				<tr>
					<td align='right'><small><b>Query korrigiert ?</b></small><input type='submit' name='del' class='button' value='delete'></td>
				</tr>
				</table>
				</form>
				</td>
				</tr>";
			}
		}

		if(!$_GET['id']) {
			$html .= '
			<tr>
				<td align="left" colspan="2"><input type="text" name="num" class="text" size="5" value="'.$_SESSION['error_num'].'"><small> Anzahl Errors von <b>'.$num_errors.'</b></small></td>
				<td align="left" cospan="2"><input type="submit" class="button" value="show"></td>
				<td align="right" colspan="4" class="hide-mobile"><input type="submit" class="button" value="schliessen"><input type="button" onClick="selectAll();" class="button" value="Alle"></td>
			</tr>';
		}

		$html .= '</tr></table>';

		if(!$_GET['id']) { $html .= '</form>';}

	} else {
		$html = '<b>Keine offenen SQL-Errors</b>';
	}

	return $html;
}
