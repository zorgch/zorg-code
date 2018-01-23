<?php

// Includes --------------------------------------------------------------------
require_once( __DIR__ .'/../includes/main.inc.php');
require_once( __DIR__ .'/../includes/anficker.inc.php');


// Anficken -------------------------------------------------------------------

if($_POST['do'] == 'anficken') {		
		
	// Benoten NUR wenn spresim-trainieren gewählt wird
		// und eine Note vorhanden ist
	if($_POST['spresim-trainieren'] && (is_numeric($_POST['note']) && $_POST['note'] > 0)) {
		Anficker::vote($_POST['anfick_id'], $_POST['note']);
	}
	
	$textEscaped = htmlentities(addslashes($_POST['text']));
	
	Anficker::addAnfick(max(0, $user->id), $textEscaped, $_POST['spresim-trainieren']);
	
	header("Location: /?tpl=175&del=no&spresimtrainieren=".$_POST['spresim-trainieren']."#anficker");	
	exit;
}

