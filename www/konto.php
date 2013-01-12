<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');


function transaktionParse($source) {
	global $db;
	$source = strip_tags($source,"<tr> <td> <table> <th> <br>");
	$source = substr($source,strpos($source,"Datum"));
	$source = str_replace("</TR>","<tr>",$source);
	//echo nl2br(htmlentities($source));
	$array = explode("<tr>",$source);
	$i = 0;
	foreach ($array as $val) {
		$in_array[$i] = explode("</TD>",strtoupper($val));	
		foreach ($in_array[$i] as $kk => $vv) {
			if($kk == 1 || $kk == 3 || $kk == 4 || $kk == 5) {
				$trans[$i][$kk] = trim(str_replace("&NBSP;","0.0",strip_tags(str_replace("<BR>","\n",$vv))));
			}
		} 
		$i++;
	}
	for($i = 1;$i<=50;$i++) {
			$sql = "
			REPLACE INTO konto_transaktionen
			(datum, wer, plus, minus)
			VALUES
			('".$trans[$i][1]."', '".$trans[$i][3]."', '".$trans[$i][4]."', '".$trans[$i][5]."')";
			$db->query($sql,__FILE__,__LINE__);
	
	}
}


if($_SESSION['user_id']) {
	echo head(63);
	echo menu('main');
	
	if($_SESSION['user_id'] == 1) {
		if($_POST['trans']) {
			transaktionParse($_POST['trans']);
		}
		echo "
		<form action='$_SERVER[PHP_SELF]' method='post'>
		<textarea name='trans' cols='4' rows='3' class='text'></textarea>
		<input type='submit' value='speichern' class='button'>
		</form>";	
		
	}

	$sql = "
	SELECT
		*
	FROM konto_transaktionen";
	$result = $db->query($sql,__FILE__,__LINE__);
	
	echo 
	"<br /><table class='border'><tr>
	<td class='title'>
	Datum
	</td><td class='title'>
	Text
	</td>
	<td class='title'>
	Plus
	</td><td class='title'>
	Minus
	</td></tr>";
	
	while($rs = $db->fetch($result)) {
		echo "<tr><td valign='top'>
		".$rs['datum']."
		</td><td>
		".nl2br($rs['wer'])."
		</td><td valign='top'>
		+ ".$rs['plus']."
		</td><td valign='top'>
		- ".$rs['minus']."
		</td>
		</tr>";	
		
	}
	
	echo "</table>";
	echo foot();	
}