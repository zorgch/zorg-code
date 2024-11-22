<?php
/**
 * zorg Tauschbörse
 * @package zorg\Tauschbörse
 */
global $db, $user, $smarty;

// Angebote
$result = $db->query('SELECT *, CONVERT(kommentar USING latin1) kommentar, UNIX_TIMESTAMP(datum) AS datum FROM tauschboerse
					WHERE art="angebot" AND aktuell="1" ORDER BY datum DESC', __FILE__, __LINE__, 'SELECT Angebote');
while ($rs = $db->fetch($result)) {
	$angebote[$rs['id']] = $rs;
}
$smarty->assign("angebote", $angebote);


// Nachfragen
$result = $db->query('SELECT *, CONVERT(kommentar USING latin1) kommentar, UNIX_TIMESTAMP(datum) AS datum FROM tauschboerse
					WHERE art="nachfrage" AND aktuell="1" ORDER BY datum DESC',	__FILE__, __LINE__, 'SELECT Nachfragen');
while ($rs = $db->fetch($result)) {
	$nachfragen[$rs['id']] = $rs;
}
$smarty->assign("nachfragen", $nachfragen);
