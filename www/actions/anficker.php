<?PHP

// Includes --------------------------------------------------------------------
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/anficker.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');


// Anficken -------------------------------------------------------------------

if($_POST['do'] == 'anficken') {		
		
	// Benoten NUR wenn spresim-trainieren gewählt wird
		// und eine Note vorhanden ist
	if($_POST['spresim-trainieren'] && (is_numeric($_POST['note'] && $_POST['note'] > 0)) {
		Anficker::vote($_POST['anfick_id'], $_POST['note']);
	}
	
	$textEscaped = htmlentities(addslashes($_POST['text']));
	
	Anficker::addAnfick(max(0, $user->id), $textEscaped, $_POST['spresim-trainieren']);
	
	header("Location: /smarty.php?tpl=175&del=no&spresimtrainieren=".$_POST['spresim-trainieren']."#anficker");
	
	exit;
}

?>