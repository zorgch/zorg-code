<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');

$sql = "SELECT * FROM sql_error	WHERE status = 1";
$num_errors = $db->num($db->query($sql));

global $num_errors;

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
	
		$order_by = array("","u.username", "s.page", "s.file", "s.date", "s.referer");
		$by = array("DESC","ASC");
		$sql = "
		SELECT 
			COALESCE(u.username,'ausgeloggt') as username,
			COALESCE(u.last_ip,'ausgeloggt') as host,
			s.page as page,
			s.file as file,
			s.line as line,
			s.function as function,
			s.query as query,
			s.msg as msg,
			s.referer as referrer,
			s.id as id,
			UNIX_TIMESTAMP(s.date) as datum
		FROM sql_error s
		LEFT JOIN user u ON u.id = s.user_id
		WHERE s.status = 1
		ORDER by ".$order_by[$_SESSION['error_order']]." ".$by[$_SESSION['error_oby']]."
		LIMIT 0,".$_SESSION['error_num'];
	
		$result = $db->query($sql,__FILE__,__LINE__);
		
		if(!$_GET['id']) {
			$html .= "
			<script language='javascript'>
			function selectAll() {  
			for(i=0; i < (".$num_errors."); i++)  
			document.error_form.elements[i].checked = !document.error_form.elements[i].checked;
			}
			</script>
			<form action='actions/error_action.php?tpl=$_GET[tpl]' name='error_form' method='post'>";	
		}
		
		$html .= "
		<table class='border'>
		<tr><td align='center'><b>
		<a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=1'>User</a>
		</b>
		</td><td align='center'><b>
		<a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=2'>Page</a>
		</b>
		</td>
		<td align='center'><b>
		<a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=5'>Referrer</a>
		</b>
		</td>
		<td align='center'><b>
		<a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=3'>File</a>
		</b>
		</td>
		<td align='center'><b>
		<b>
		Line
		</b>
		</td>
		<td align='center'><b>
		SQL
		</b>
		</td>
		<!--
		<td align='center'><b>
		Message
		</b>
		</td>
		-->
		<td align='center'><b>
		<a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&o=4'>Datum</a>
		</b>
		</td>";
		
		if(!$_GET['id']) {
			$html .= "
			<td align='right'><b>del</b></td>";	
		}
		
		$html .= "</tr>";
		$i = 0;
		while($rs = $db->fetch($result)) {
			
			if(($i % 2) == 0) {	
				$add = " bgcolor=".TABLEBACKGROUNDCOLOR." "; 
			} else { 
				$add = " bgcolor=".BACKGROUNDCOLOR." "; 
			}
			
			$i++;
			
			$html .= "
			<tr $add><td align='left' ><small>
			".$rs['username']."
			</small>
			</td><td align='left'><small>
			".substr($rs['page'],0,23)."...
			</small>
			</td>
			<td align='left'><small>
			".substr(str_replace("http://".$_SERVER['SERVER_NAME'],"", $rs['referrer']),0,23)."...
			</small>
			</td>
			<td align='left'><small>
			".str_replace($_SERVER['DOCUMENT_ROOT'],"",$rs['file'])."
			</small>
			</td>
			<td align='left'><small>
			".$rs['line']."
			</small>
			</td><td align='left'><small>
			<a href='".$_SERVER['PHP_SELF']."?tpl=".$_GET['tpl']."&id=".$rs['id']."'>".substr($rs['query'],0,23)."...</a>
			</small>
			</td>
			<!--<td align='left'><small>
			".substr($rs['msg'],0,23)."...
			</small>
			</td>-->
			<td align='left'><small>
			".datename($rs['datum'])."
			</small>
			</td>";
			
			if(!$_GET['id']) {
				$html .= 
				"<td align='right' $add>
				<input type='checkbox' name='to_del[]' value='".$rs['id']."'> 
				</td>";
			}
	
			$html .= "</tr>";
			
			if($_GET['id'] == $rs['id']) {
				if($_GET['query']) {
					$result_chk = @mysql_query(stripslashes(base64_decode($_GET['query'])),$db->conn);
					if(!$result_chk) {
						$check = mysql_error($db->conn);
					} else {
						$check = "Keine Fehler: ".$db->num($result_chk)." Rows";
					}
					$rs['query'] = stripslashes(base64_decode($_GET['query']));
				}
				
				$html .= "
				<tr><td align='left' colspan='7'>
				<form action='actions/error_action.php?tpl=".$_GET['tpl']."&id=".$rs['id']."' method='post'>
				<table class='border'>
				<tr><td align='center' valign='top'><small>
				".$rs['msg']."</small>
				</td></tr><tr><td align ='left'><small><b>
				<textarea name='query' cols='130' rows='".(substr_count(nl2br($rs['query']),"<br />")+2)."'>
				".stripslashes(trim($rs['query']))."</textarea></b><br>";
				
				if($_GET['query']) {
					$html .= "<b>$check</b><br>";	
				}
				
				$html .= "
				<input type='submit' class='button' value='execute'><br>";
				if($rs['file'] != "" && $rs['line'] != "") {
					$html .= "File: ".$rs['file']."<br>
					Line: ".$rs['line']."<br>";
				} else {
					$html .= "<br><b>Bitte ".htmlentities("\$db->query(\"SQL\",__FILE__,__LINE__);")." anwenden!<br>";
				}
				if($rs['function'] != "") {
					$html .= "Function: ".$rs['function']."<br />";	
				}
				$html .= "
				Aufgetretten: ".$rs['page']." um ".datename($rs['datum'])."<br>
				User: ".$rs['username']." <br />";
				if($rs['username'] != "ausgeloggt") {
					$html .= "Host: ".$rs['host']."<br />";
				}
				if($rs['referrer'] != "") {
					$html .= "Ausgangsseite: ".str_replace("http://".$_SERVER['SERVER_NAME'],"",$rs['referrer']);
				}
				$html .= "
				</small>
				</td></tr>
				<tr><td align='right'>
				<small><b>Query korrigiert ?</b></small>
				<input type='submit' name='del' class='button' value='löschen'>
				</td></tr>
				</table>
				</form>
				</td></tr>";
			}
		}
		
		if(!$_GET['id']) {
			$html .= "
			<tr><td align='left' colspan='2'>
			<input type='text' name='num' class='text' size='5' value='".$_SESSION['error_num']."'><small> Anzahl Errors von <b>".$num_errors."</b></small>
			</td><td align='left' cospan='2'>
			<input type='submit' class='button' value='anpassen'>
			</td><td align='right' colspan='4'>
			<input type='submit' class='button' value='schliessen'>";
			
			$html .= "

			<input type='button' onClick='selectAll();' class='button' value='Alle'>
			</td></tr>";
		}
		
		$html .= "</tr></table>";
		
		if(!$_GET['id']) { $html .= "</form>";}
	
	} else {
		$html = "<b>Keine offenen SQL-Errors</b>";
	}
	
	return $html;
}




?>