<?php

require_once($_SERVER['DOCUMENT_ROOT']."/includes/main.inc.php");
include_once(SITE_ROOT.'/includes/wetten.inc.php');


//Post actions ausführen/entgegennehmen
wetten::exec();

//echo head("zorg", "Wettbüro"); //head($menu, $title)
$smarty->assign('tplroot', array('page_title' => 'Wettbüro'));
$smarty->display('file:layout/head.tpl');
echo menu("zorg");
if ($user->typ != USER_NICHTEINGELOGGT) echo menu("eingeloggte_user");
echo menu("user");

if(!$_GET['id']) {
	if($_GET['eintrag']) {
		echo "<br /><b>Eintrag erfolgreich</b><br />";
	}

	//offene wetten auflisten
	wetten::listopen();

	//laufende wetten auflisten
	wetten::listlaufende();
	
	//geschlossene wetten auflisten
	wetten::listclosed();

	//neue wette erstellen form anzeigen
	//aber ur wenn eingeloggter user
	if ($user->typ != USER_NICHTEINGELOGGT) wetten::newform();
} else {
	wetten::get_wette($_GET['id']);
}

//echo foot(1);
$smarty->display('file:layout/footer.tpl');

?>