<?php
/**
 * Frässe Tagging
 *
 * Game zum Frässene vo Gallery Pics zu Users tagge
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `IneX` 04.01.2019
 * @package zorg\Gallery
 */

/**
 * File includes
 * @include main.inc.php
 * @include core.model.php
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';
/**
 * Initialise MVC Model
 */
$model = new MVC\Gallery();

/** Frässä chönd nur nur iigloggti User mache */
if (!$user->is_loggedin())
{
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	$model->showFacetagging();
	$smarty->assign('error', ['type' => 'warn', 'title' => 'These are not the droids you\'re looking for!', 'message' => 'Bitte logge Dich ein oder <a href="/profil.php?do=anmeldung">erstelle einen neuen Benutzer</a>', 'dismissable' => 'false']);
	$smarty->display('file:layout/head.tpl');
} else {
	$index = ((isset($_GET['index']) && $_GET['index'] >= 0 && is_numeric($_GET['index'])) || strlen($_GET['index'] == '0') || (!empty($_GET['index']) && is_numeric($_GET['index'])) ? $_GET['index'] : 'false' );
	$smarty->assign('currindex', $index);

	$motivationalTitles = array('Wem ghört die Fresse?', 'Wem sini Hackfresse isch da?', 'Hüt d\'Fresse markiere, morn d\'Fuscht dri drucke', 'Welle Spast luegt do id Kamera?');
	$smarty->assign('h2', $motivationalTitles[array_rand($motivationalTitles, 1)]);

	$sql = 'SELECT * FROM gallery_pics_faceplusplus WHERE user_id_tagged IS NULL AND width >= "250" AND height >= "250" AND pic_id NOT IN (SELECT pic_id FROM gallery_pics_faceplusplus GROUP BY pic_id HAVING COUNT(pic_id) > 1) ' . ( is_numeric($index) ? 'LIMIT '.$index.',1' : 'ORDER BY RAND() LIMIT 0,1');
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = $db->fetch($result))
	{
	    $pics[] = [
		    'pic_id' => $rs['pic_id'],
		    'img_path' => imgsrcPic($rs['pic_id']),
		    'user_id' => $rs['user_id_tagged'],
			'top' => $rs['top'],
			'left' => $rs['left'],
			'width' => $rs['width'],
			'height' => $rs['height'],
			'headpose_roll_angle' => $rs['headpose_roll_angle'],
			'gender' => $rs['gender_value'],
			'age' => $rs['age_value'],
			'smiling' => ($rs['smile_value'] > $rs['smile_treshold'] ? 'smiling' : '')
		];
	}
	//Array Debug: print('<pre>'.print_r($pics,true).'</pre>');
	$smarty->assign('pics', $pics);

	$model->showFacetagging($index);
	$smarty->display('file:layout/head.tpl');
	$smarty->display('file:facetag.tpl');
}

$smarty->display('file:layout/footer.tpl');
