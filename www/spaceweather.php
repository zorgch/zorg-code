<?php
/**
 * Spaceweather information
 *
 * @author ?
 * @package zorg\Spaceweather
 */

/**
 * File includes
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once INCLUDES_DIR.'apod.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Spaceweather();


/** Get Spaceweather */
$sql = 'SELECT * FROM spaceweather';
$result = $db->query($sql,__LINE__,__FILE__);

while($rs = $db->fetch($result)) {
	$sw[$rs['name']] = ( $rs['wert'] != '' ? $rs['wert'] == '' : 'unbekannt' );
}

/** Get Asteroids */
$sql = 'SELECT *, UNIX_TIMESTAMP(datum) as datum FROM spaceweather_pha WHERE MONTH(datum) = MONTH(now()) AND YEAR(datum) = YEAR(now())';
$result = $db->query($sql,__LINE__,__FILE__);
while($rs = $db->fetch($result)) {
	$ao[$rs['asteroid']] = [ 'date' => ( $rs['datum'] != '' ? date("M d.",$rs['datum']) : 'n/a' )
							,'distance' => ( $rs['distance'] != '' ? $rs['distance'] : 'n/a' )
							,'mag' => ( $rs['mag'] != '' ? round($rs['mag']) : 'n/a' )
						   ];
}

/** Assign Smarty Variables */
//$smarty->assign('tplroot', array('page_title' => 'Spacewetter'));
$model->showOverview($smarty);
$smarty->assign('solarflares_6hr_time', ( $sw['solarflares_6hr_time'] != 'unbekannt' ? date("H:i",strtotime($sw['solarflares_6hr_time'])) : 'n/a') );
$smarty->assign('solarflares_24hr_time', ( $sw['solarflares_24hr_time'] != 'unbekannt' ? date("H:i",strtotime($sw['solarflares_24hr_time'])) : 'n/a') );
$smarty->assign('spawe', $sw);
$smarty->assign('asteroids', $ao);

/** Display page from Smarty Template */
$smarty->display('file:layout/pages/spaceweather.tpl');
