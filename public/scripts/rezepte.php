<?php
/**
 * zorg Rezepte Datenbank
 * @package zorg\Rezepte
 */
global $db, $user, $smarty;

/** Fetch all Rezepte by Title */
// FIXME move this to rezepte.inc.php
$f = $db->query('SELECT * FROM rezepte ORDER by title ASC', __FILE__, __LINE__, 'SELECT FROM rezepte');

$smarty->assign('rezepte', $db->fetch($f));
