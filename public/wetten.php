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
require_once __DIR__.'/includes/main.inc.php';
require_once INCLUDES_DIR.'wetten.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Wetten();

/**
 * Input validation & sanitization
 */
$wette = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0; // $_GET['id']
$getEintrag = filter_input(INPUT_GET, 'eintrag', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['eintrag']

/** Post actions ausführen/entgegennehmen */
wetten::exec();

/** Wettbüro Übersicht */
if ($wette <= 0)
{
	$model->showOverview($smarty);
	if (isset($getEintrag) && $getEintrag == true)
	{
		//echo "<br /><b>Eintrag erfolgreich</b><br />";
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Wette erfolgreich erfasst']);
	}

	/**
	 * neue wette erstellen form anzeigen
	 * aber nur wenn eingeloggter user
	 */
	if ($user->is_loggedin()) $smarty->assign('sidebarHtml', wetten::newform());
	$smarty->display('file:layout/head.tpl');
	echo '<h1>zorg Wettbüro</h1>';

	/** offene wetten auflisten */
	//wetten::listopen();
	wetten::listwetten();

	/** laufende wetten auflisten */
	//wetten::listlaufende();
	wetten::listwetten('laeuft');

	/** geschlossene wetten auflisten */
	//wetten::listclosed();
	wetten::listwetten('geschlossen');
}

/** Wette anzeigen */
else {
	$model->showWette($smarty, $user, $wette, wetten::getWettstarter($wette), wetten::getTitle($wette), wetten::getWettetext($wette));
	$smarty->display('file:layout/head.tpl');
	wetten::get_wette($wette);
}

$smarty->display('file:layout/footer.tpl');
