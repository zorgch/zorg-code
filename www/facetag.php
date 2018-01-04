<?php
/**
 * Frässe Tagging
 * 
 * Game zum Frässene vo Gallery Pics zu Users tagge
 *
 * @author IneX
 * @date 04.01.2018
 * @version 1.0
 * @package zorg
 * @subpackage Gallery
 */
/**
 * File Includes
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT']."/includes/layout.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/gallery.inc.php");

echo head(117, 'Fresse Tagging', true);

echo menu("zorg");
echo menu("gallery");
	
// Frässä chönd nur nur iigloggti User mache
if ($user->typ == USER_NICHTEINGELOGGT)
{
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	user_error("<h2>These are not the droids you're looking for!</h2>
	<p>Bitte logge Dich ein oder <a href=\"profil.php?do=anmeldung&menu_id=13\">erstelle einen neuen Benutzer</a></p>", E_USER_NOTICE);	
} else {
	$index = ((isset($_GET['index']) && $_GET['index'] >= 0 && is_numeric($_GET['index'])) || strlen($_GET['index'] == '0') || (!empty($_GET['index']) && is_numeric($_GET['index'])) ? $_GET['index'] : 'false' );
	$smarty->assign('currindex', $index);
	
	$motivationalTitles = array('Wem ghört die Fresse?', 'Wem sini Hackfresse isch da?', 'Hüt d\'Fresse markiere, morn d\'Fuscht dri drucke', 'Welle Spast luegt do id Kamera?');
	$smarty->assign('h2', $motivationalTitles[array_rand($motivationalTitles, 1)]);
	
	try {
		$sql = 'SELECT * FROM gallery_pics_faceplusplus WHERE user_id_tagged IS NULL GROUP BY pic_id HAVING COUNT(pic_id) = 1 ' . ( is_numeric($index) ? 'LIMIT '.$index.',1' : 'ORDER BY RAND() LIMIT 0,1');
		$result = $db->query($sql, __FILE__, __LINE__);
		while ($rs = mysql_fetch_array($result, MYSQL_ASSOC))
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
		
		$smarty->display('file:facetag.tpl');
	}
	catch(Exception $e) {
		http_response_code(500); // Set response code 500 (internal server error)
		user_error($e->getMessage(), E_USER_ERROR);
	}
}

echo foot(117);
