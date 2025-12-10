<?php
/**
 * Anficker Actions
 *
 * @author [z]milamber
 * @package zorg\Games\Anficker
 */
// Includes --------------------------------------------------------------------
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'anficker.inc.php';

/** Input validation */
$doAction = filter_input(INPUT_POST, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['do']
$trainSpresim = filter_input(INPUT_POST, 'spresim-trainieren', FILTER_VALIDATE_BOOLEAN) ?? false; // $_POST['spresim-trainieren']
$anfickId = filter_input(INPUT_POST, 'anfick_id', FILTER_VALIDATE_INT) ?? 0; // $_POST['anfick_id']
$anfickScore = filter_input(INPUT_POST, 'note', FILTER_VALIDATE_INT) ?? 0; // $_POST['note']
$anfick = htmlentities(filter_input(INPUT_POST, 'text', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?? null; // $_POST['text']
$returnUrl = '/tpl/175';

// Anficken -------------------------------------------------------------------
if($user->is_loggedin() && $doAction === 'anficken')
{
	/**
	 * Benoten NUR wenn spresim-trainieren gewählt wird
	 * und eine Note vorhanden ist
	 */
	if ($trainSpresim && $anfickId > 0 && $anfickScore > 0) Anficker::vote($anfickId, $anfickScore);

	if (!empty($anfick)) Anficker::addAnfick(max(0, $user->id), $anfick, $trainSpresim);

	$returnUrl .= '?del=no&spresimtrainieren='.$trainSpresim.'#anficker';
}
header('Location: '.$returnUrl);
exit;
