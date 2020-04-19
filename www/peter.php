<?php
/**
 * Peter Game
 *
 * @package zorg\Games\Peter
 */

/**
 * Starte Output-Buffering
 * damit die header() funktion nicht an oberster Stelle des Codes stehen muss
 */
ob_start();

/** File includes */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Peter();

/** Validate passed GET-Parameters */
$peterGame = (int)$_GET['game_id'];
$peterShow = (string)$_GET['img'];
$peterCard = (int)$_GET['card_id'];
$peterZug = (string)$_GET['make'];
$view = (string)$_GET['view'];

/**
 * Initialise Peter Class-Object
 */
$peter = new peter($peterGame);

if ($user->is_loggedin())
{
	if ($peterShow == 'karten')
	{
		if ($peterGame)
		{
			/*
			header("Content-Type: Image/PNG");
			header("Cache-Control: no-cache, no-store, must-revalidate");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			*/
			$model->showKartenberg($peter);
			imagepng($peter->kartenberg());
		}
	}
	else {
		//echo head(117, 'Peter');
		//$smarty->assign('tplroot', array('page_title' => 'Peter'));
		//echo menu("zorg");
		//echo menu("games");
		//echo menu("peter");
		$model->showGame($smarty, $peterGame);

		$peter->exec_peter();
		$htmlOutput = null;
		$sidebarHtml = null;

		/** Peter Game anzeigen */
		if ($peterGame)
		{
			/** Infos über das game holen */
			/*
			$sql = 'SELECT 
					*
				FROM peter_games pg
				LEFT JOIN user u
				ON pg.next_player = u.id
				WHERE pg.game_id = '.$peterGame;
			$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			$rsg = $db->fetch($result);
			*/
			$rsg = $model->getGamedata($peterGame);

			/** Wenn dem Spiel noch beigetreten werden kann */
			if ($rsg['status'] === 'offen')
			{
				if ((int)$rsg['players'] > 0 && !empty($rsg['players'])) $peter->peter_join($rsg['players']);

			/** Wenn das Spiel bereits läuft */
			} elseif ($rsg['status'] === 'lauft' || $rsg['status'] === 'geschlossen') {
				$htmlOutput .= $peter->game($rsg, $peterCard, $peterZug);
			
			/** Spieldaten fehlerhaft / nicht gefunden */
			} else {
				$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('error-game-invalid')]);
			}
		}

		/** Peter Highscores anzeigen */
		elseif (!empty($view)) {
			$model->showHighscores($smarty);
			$htmlOutput .= $peter->peterscore();
		}

		/** Ein Peter Spiel nur anzeigen */
		else {
			$model->showOverview($smarty);

			$sidebarHtml .= $peter->neu_form();
			$sidebarHtml .= $peter->laufende_spiele();

			$htmlOutput .= $peter->meine_laufende_spiele($user->id);
			$htmlOutput .= $peter->offene_spiele();
		}

		/** Layout */
		if (!empty($sidebarHtml)) $smarty->assign('sidebarHtml', $sidebarHtml);
		$smarty->display('file:layout/head.tpl');
		echo $htmlOutput;
		$smarty->display('file:layout/footer.tpl');
	}

/** Nicht eingeloggte User: sehen nur Anleitung */
}
else {
	$model->showOverview($smarty);
	$smarty->assign('error', ['type' => 'info', 'dismissable' => 'false', 'title' => t('error-newgame-not-logged-in')]);
	$smarty->display('file:layout/head.tpl');
	$smarty->display('file:layout/footer.tpl');
}

/** Beende das Output-Buffering */
ob_end_flush();
