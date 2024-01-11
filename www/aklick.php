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
require_once __DIR__.'/includes/config.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Anficker();
$model->showOverview($smarty);

/** Nur für eingeloggte User! */
if ($user->is_loggedin())
{
	$edit_id = filter_input(INPUT_POST, 'edit_id', FILTER_VALIDATE_INT) ?? null; // $_POST['edit_id']
	$edit_wort = filter_input(INPUT_POST, 'edit_wort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_POST['edit_wort']
	$edit_user = filter_input(INPUT_POST, 'edit_user', FILTER_VALIDATE_INT) ?? null;
	$new_wort = filter_input(INPUT_POST, 'new_wort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$new_typ = filter_input(INPUT_POST, 'new_typ', FILTER_VALIDATE_INT) ?? null; // 1=adjektiv, or 2=subjektiv
	$aklick = filter_input(INPUT_POST, 'afick', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_POST['afick']
	$edit = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?? null; // $_GET['edit']
	$new = filter_input(INPUT_GET, 'new', FILTER_VALIDATE_BOOLEAN) ?? false; // $_GET['new']
	$log = filter_input(INPUT_GET, 'log', FILTER_VALIDATE_BOOLEAN) ?? false; // $_GET['log']

	if(count($_POST)>0) {
		if(!empty($edit_wort) && ($edit_user === (int)$_SESSION['user_id'] || $user->typ >= USER_MEMBER)) {
			$sql = 'UPDATE aficks set wort=? WHERE id=?';
			$db->query($sql,__FILE__,__LINE__,'Update Aficks', [$edit_wort, $edit_id]);
			header("Location: ".$_SERVER['PHP_SELF']."?".session_name()."=".session_id());
			exit;
		}
		if(!empty($new_wort) && ($new_typ === 1 || $new_typ === 2)) {
			$sql = 'INSERT IGNORE INTO aficks (wort, typ, wort_user_id)
					VALUES (?,?,?)';
			$db->query($sql,__FILE__,__LINE__,'Insert Aficks', [$new_wort, $new_typ, $_SESSION['user_id']]);
			header("Location: ".$_SERVER['PHP_SELF']."?".session_name()."=".session_id());
			exit;
		}

		if($_SESSION['query']) {
			$old_query = base64url_decode($_SESSION['query']);
		}

		$old_query .= "
		<tr><td align='left'>
		<B>".$user->username."</B>
		</td><td align='left' width='100%'>".$aklick."</td></tr>";
		$sql = 'SELECT * FROM aficks WHERE typ = 1';
		$result = $db->query($sql,__FILE__,__LINE__,'SQL Query');
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
		$result = $db->query($sql,__FILE__,__LINE__,'SQL Query');
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
		$_SESSION['query'] = base64url_encode($old_query);

		if(empty($aklick) && empty($edit_wort) && empty($new_wort)) {
			$_SESSION['query'] = "";
		}

		$sql = 'INSERT into aficks_log (user_id, afick_am, afick_user, datum) VALUES (?,?,?,?)';
		$db->query($sql,__FILE__,__LINE__,'Insert aficks_log', [$_SESSION['user_id'], $afick_am, $aklick, timestamp(true)]);
		header("Location: ".getURL(false,false));
		exit;
	}

	$smarty->display('file:layout/head.tpl');
	echo '<h2>Aficks Admin</h2>';
	echo '
	<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">
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
	</from><br>';

	if($edit>0 && !$new)
	{
		$sql = 'SELECT * FROM aficks WHERE id=?';
		$result = $db->query($sql,__FILE__,__LINE__,'Select aficks by id', [$edit]);
		$rs = $db->fetch($result);
		$typ = array("","mittelchind","mueterbai");
		if($rs['wort_user_id'] == $_SESSION['user_id'] || $user->typ == 2) {
			echo '
			<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">
			<table cellpadding="2" class="border"><tr><td align="center" colspan="2">EDIT</td>
			</tr><td align="left" colspan="2">
			<input type="text" class="text" name="edit_wort" size="40" value="'.$rs['wort'].'">
			<input type="hidden" name="edit_id" value="'.$rs['id'].'">
			<input type="hidden" name="edit_user" value="'.$rs['wort_user_id'].'">
			</td></tr><tr><td align="left">
			<input type="submit" value="speichern" class="button">
			</td><td align="right">
			Typ: ".$typ['.$rs['typ'].']."
			</tr></table>
			</from>
			<br>';
		}
	}

	if($new === true)
	{
		echo '
		<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">
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
		<br>';
	}

	if($log === true)
	{
		$sql = 'SELECT a.afick_am, a.afick_user, u.username
				FROM aficks_log	a
				INNER JOIN user u
					ON u.id = a.user_id
				ORDER by a.datum DESC';
		$result = $db->query($sql,__FILE__,__LINE__,'SQL Query');
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
		echo "</table><br>";
	}

	if(!empty($_SESSION['query']))
	{
		echo "<table>";
		echo base64url_decode($_SESSION['query']);
		echo "</table>";
	}
}
/** Not loggedin Users */
else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	$smarty->assign('error', ['type' => 'warn', 'title' => 'Access denied', 'message' => 'Du hesch scho alles gseh, aber das do d&ouml;rfsch n&ouml;d!', 'dismissable' => 'false']);
	$smarty->display('file:layout/head.tpl');
}

$smarty->display('file:layout/footer.tpl');
