<?php
/**
 * Das zorg Wettbüro
 *
 * @author freiländer
 * @package zorg\Wetten
 */

/**
 * File includes
 */
require_once(__DIR__.'/includes/main.inc.php');
require_once(__DIR__.'/includes/wetten.inc.php');
require_once(__DIR__.'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Wetten();

/**
 * Validate GET-Parameters
 */
if (!empty($_GET['id'])) $wette = (int)$_GET['id'];
if (!empty($_GET['eintrag'])) $getEintrag = (string)$_GET['eintrag'];

/** Post actions ausführen/entgegennehmen */
wetten::exec();

//echo head("zorg", "Wettbüro"); //head($menu, $title)
//$smarty->assign('tplroot', array('page_title' => 'Wettbüro'));
//echo menu("zorg");
//if ($user->typ != USER_NICHTEINGELOGGT) echo menu("eingeloggte_user");
//echo menu("user");

/** Wettbüro Übersicht */
if (empty($wette) || $wette <= 0)
{
	$model->showOverview($smarty);
	if ($getEintrag)
	{
		//echo "<br /><b>Eintrag erfolgreich</b><br />";
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Wette erfolgreich erfasst']);
	}

	/**
	 * neue wette erstellen form anzeigen
	 * aber nur wenn eingeloggter user
	 */
	if ($user->typ != USER_NICHTEINGELOGGT) $smarty->assign('sidebarHtml', wetten::newform());
	$smarty->display('file:layout/head.tpl');
	echo '<h1>zorg Wettbüro</h1>';

	/** offene wetten auflisten */
	wetten::listopen();

	/** laufende wetten auflisten */
	wetten::listlaufende();
	
	/** geschlossene wetten auflisten */
	wetten::listclosed();
}

/** Wette anzeigen */
else {
	$model->showWette($smarty, $user, $wette, wetten::getWettstarter($wette), wetten::getTitle($wette), wetten::getWettetext($wette));
	$smarty->display('file:layout/head.tpl');
	wetten::get_wette($wette);
}

$smarty->display('file:layout/footer.tpl');
