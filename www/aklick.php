<?php
/**
 * Aficks Admin
 *
 * Neui Aficks-Wörter und neui Afick-Sprüch
 *
 * @author ?
 * @package zorg\Games\Anficker
 */

/**
 * File Includes
 */
require_once( __DIR__ .'/includes/config.inc.php');
require_once( __DIR__ .'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Anficker();

$model->showOverview($smarty);
$smarty->display('file:layout/head.tpl');

/** Nur für eingeloggte User! */
if ($user->is_loggedin())
{
	if(count($_POST)>0) {
		if($_POST['edit_wort'] && ($_POST['edit_user'] == $_SESSION['user_id'] || $user->typ == 2)) {
			$sql = 'UPDATE aficks set wort = "'.$_POST['edit_wort'].'" WHERE id = '.$_POST['edit_id'];
			$db->query($sql,__FILE__,__LINE__);
			header("Location: ".$_SERVER['PHP_SELF']."?".session_name()."=".session_id());
			exit;
		}
		if($_POST['new_wort'] && $_POST['new_typ']) {
			$sql = 'INSERT IGNORE INTO aficks (wort, typ, wort_user_id) 
					VALUES ("'.$_POST['new_wort'].'","'.$_POST['new_typ'].'",'.$_SESSION['user_id'].')';
			$db->query($sql,__FILE__,__LINE__);
			header("Location: ".$_SERVER['PHP_SELF']."?".session_name()."=".session_id());
			exit;
		}
			
		if($_SESSION['query']) {
			$old_query = base64_decode($_SESSION['query']);
		}
		
		$aklick = strip_tags($_POST['afick']);
		$old_query .= "
		<tr><td align='left'>
		<B>".$user->username."</B>
		</td><td align='left' width='100%'>".$aklick."</td></tr>";
		$sql = 'SELECT * FROM aficks WHERE typ = 1';
		$result = $db->query($sql,__FILE__,__LINE__);
		while($rs = $db->fetch($result)) {
			$af[] = $rs['wort'];
			$id[$rs['wort']] = $rs['id'];
			$user_ids[$rs['id']] = $rs['wort_user_id'];
		}	
		$num = (strlen($aklick)/5);
		shuffle($af);
		$old_query .= "<tr><td align='left'>
		<B>AM</B></td><td align='left' width='100%'>";
		for($i = 0;$i<=$num;$i++) {
			if($user_ids[$id[$af[$i]]] == $_SESSION['user_id'] || $user->typ == 2) {
				$afick_am .= "<a href='?edit=".$id[$af[$i]]."'>".trim($af[$i])."</a> ";
			} else {
				$afick_am .= trim($af[$i])." ";
			}
		}
		
		$sql = 'SELECT * FROM aficks WHERE typ = 2';
		$result = $db->query($sql,__FILE__,__LINE__);
		while($rs = $db->fetch($result)) {
			$am[] = $rs['wort'];
			$id[$rs['wort']] = $rs['id'];
			$user_ids[$rs['id']] = $rs['wort_user_id'];
		}
		shuffle($am);
		if($user_ids[$id[$am[0]]] == $_SESSION['user_id'] || $user->typ == 2) {
			$afick_am .= '<a href="?edit='.$id[$am[0]].'">'.$am[0].'</a>';
		} else {
			$afick_am .= $am[0];
		}
		$old_query .= $afick_am."</td></tr>";
		$_SESSION['query'] = base64_encode($old_query);
		
		if($_POST['afick'] == "" && !$_POST['edit_wort'] && !$_POST['new_wort']) {
			$_SESSION['query'] = "";
		}
		
		$sql = 'INSERT into aficks_log (user_id, afick_am, afick_user, datum)
		VALUES ('.$_SESSION['user_id'].',"'.$afick_am.'","'.$aklick.'",now())';
		$db->query($sql,__FILE__,__LINE__);
		header("Location: ".getURL(false,false));
		exit;
	}

	//echo head(20);
	//$smarty->assign('tplroot', array('page_title' => 'Aficks'));
	//echo menu("zorg");
	//echo menu("games");
	echo '<h2>Aficks Admin</h2>';
	echo '
	<form action="'.$_SERVER['PHP_SELF'].'" method="post">
	<table class="border" style="display: flex;white-space: nowrap;align-items: center;">
		<tr>
			<td align="left">
				<fieldset>
					<label style="flex: 1;"><strong>Afick:</strong> 
						<input type="text" class="text" name="afick" tabindex="0" style="width: 80%;">
					</label>
					<input type="submit" value="maaaaach" name="del" class="button" style="flex: 2;">
				</fieldset>
			</td>
		</tr>
		<tr>
			<td align="left">
				<a href="?new=1">Neues Wort</a>
			</td>
		</tr>
	</table>
	</from><br />';

	if($_GET['edit'])
	{
		$sql = 'SELECT * FROM aficks WHERE id = '.$_GET['edit'];
		$result = $db->query($sql,__FILE__,__LINE__);
		$rs = $db->fetch($result);
		$typ = array("","mittelchind","mueterbai");
		if($rs['wort_user_id'] == $_SESSION['user_id'] || $user->typ == 2) {
			echo '
			<form action="'.$_SERVER['PHP_SELF'].'" method="post">
			<table cellpadding="2" class="border"><tr><td align="center" colspan="2">EDIT</td>
			</tr><td align="left" colspan="2">
			<input type="text" class="text" name="edit_wort" size="40" value="'.$rs['wort'].'">
			<input type="hidden" name="edit_id" value="'.$rs['id'].'">
			<input type="hidden" name="edit_user" value="'.$rs['wort_user_id'].'">
			</td></tr><tr><td align="left">
			<input type="submit" value="speichern" class="button">
			</td><td align="right">
			Typ: ".$typ['.$rs['"typ"'].']."
			</tr></table>
			</from>
			<br />';
		}
	}
	if($_GET['new'])
	{
		echo '
		<form action="'.$_SERVER['PHP_SELF'].'" method="post">
		<table cellpadding="2" class="border"><tr><td align="center" colspan="2">Neu</td>
		</tr><td align="left" colspan="2">
		<input type="text" class="text" name="new_wort" size="40">
		</td></tr><tr><td align="left">
		<input type="submit" value="speichern" class="button">
		</td><td align="right">
		<select name="new_typ" size="1" class="text">
		<option value="0">-- Typ --</option>
		<option value="1">adjektiv</option>
		<option value="2">subjekt</option>
		</select>
		</tr></table>
		</from>
		<br />';
	}
	if($_GET['log']) 
	{
		$sql = '
		SELECT 
			a.afick_am, 
			a.afick_user, 
			u.username 
		FROM aficks_log	a
		INNER JOIN user u 
			ON u.id = a.user_id
		ORDER by a.datum DESC';
		$result = $db->query($sql,__FILE__,__LINE__);
		echo "<table>";
		while($rs = $db->fetch($result)) {
			echo '
			<tr><td align="left">
			'.$rs['username'].'
			</td><td align="left">
			'.$rs['afick_user'].'
			</td></tr><tr><td align="left">
			AM:
			</td><td align="left">
			'.$rs['afick_am'].'
			</td></tr>';
			
		}
		echo "</table><br />";
	}
	
	if($_SESSION['query']) {
		echo "<table>";
		echo base64_decode($_SESSION['query']);
		echo "</table>";
	}
}
/** Not loggedin Users */
else {
	echo 'Du hesch scho alles gseh, aber das do d&ouml;rfsch n&ouml;d!';
}


//echo foot(1);
$smarty->display('file:layout/footer.tpl');
