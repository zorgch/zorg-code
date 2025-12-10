<?php
/**
 * Shoot The Lamber (Game)
 *
 * Shoot The Lamber ist ein Schiffchen-Versenken-Klon im Multiplayer-Modus
 *
 * @author [z]milamber
 * @version 1.0
 * @package zorg\Games\STL
 */

/**
 * File includes
 * @include main.inc.php
 * @include stl.inc.php Alle Shoot the Lamber Klassen & Methoden
 * @include core.model.php Required
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once INCLUDES_DIR.'stl.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\STL();
$model->showOverview($smarty);

/**
 * Validate GET-Parameters
 */
$game_id = filter_input(INPUT_GET, 'game_id', FILTER_VALIDATE_INT) ?? null;
$action = filter_input(INPUT_GET, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$shoot_field = filter_input(INPUT_GET, 'shoot', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$message = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;

/**
 * Initialise Shoot the Lamber Game Class-Object
 */
$stl = new stl($game_id);
if (!empty($message)) $stl->message = $message;

/** Zugriff nur wenn User eingeloggt ist */
if ($user->is_loggedin())
{
	/** STL - Zug ausführen */
	if ($action === 'game')
	{
		if ($game_id > 0)
		{
			/** Action */
			if(!empty($shoot_field)) {
				$stl->shoot_field = $shoot_field;
				$stl->shoot();
			}

			/** Layout */
			$model->showGame($smarty, $game_id, $stl->config['game_title']);
			$smarty->display('file:layout/head.tpl');
			echo '<h1>'.$model->page_title.'</h1>';
			echo $stl->data['game'];
			echo $stl->data['legende'];

		} else {
			$sql = 'SELECT game_id FROM stl_players WHERE user_id=? ORDER by last_shoot DESC';
			$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__, [$user->id]);
			if($db->num($result)) {
				$rs = $db->fetch($result);
				header('Location: '.getURL(false, false).'?do=game&game_id='.$rs['game_id']);
				exit;
			} else {
				header('Location: '.getURL(false, false).'?do=overview');
				exit;
			}
		}
	}

	/** STL - Overview */
	if ($action === 'overview' || !isset($action))
	{
		$smarty->display('file:layout/head.tpl');
		echo '<h1>'.$model->page_title.'</h1>';
		echo $stl->data['overview'];
		echo $stl->data['legende'];
	}

	/** STL - Reshuffle Players */
	if ($action === 'reshuffle' && !empty($game_id))
	{
		if ($user->typ >= USER_MEMBER)
		{
			$sql = 'DELETE FROM stl_positions WHERE game_id=?';
			$db->query($sql,__FILE__,__LINE__,__FUNCTION__, [$game_id]);
			$sql = 'UPDATE stl SET status = 0 WHERE game_id=?';
			$db->query($sql,__FILE__,__LINE__,__FUNCTION__, [$game_id]);
			header('Location: '.getURL(false, false).'?do=game&game_id='.$game_id);
			exit;
		} else {
			$smarty->display('file:layout/head.tpl');
			echo 'Only Members can shufflin, shufflin!';
		}
	}

/** ...sonst "Access denied" (für nicht-eingeloggte) */
} else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	$smarty->assign('error', ['type' => 'info', 'dismissable' => 'false', 'title' => 'Wenn Du eingeloggt wärst...', 'message' => '...könntest Du hier Shoot the Lamber spielen. Aber bis dahin: access denied!']);
	$smarty->display('file:layout/head.tpl');
	echo '<h1>'.$model->page_title.'</h1>';
}

/** Page Footer */
$smarty->display('file:layout/footer.tpl');
