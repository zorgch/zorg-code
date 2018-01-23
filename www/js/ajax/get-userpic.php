<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'getpic')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Invalid or missing GET-Parameter (Error 1)', E_USER_ERROR);
	exit;
}
$index = ((isset($_GET['index']) && $_GET['index'] >= 0 && is_numeric($_GET['index'])) || strlen($_GET['index'] == '0') || (!empty($_GET['index']) && is_numeric($_GET['index'])) ? $_GET['index'] : 'false' );

/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../includes/mysql.inc.php');
//require_once($_SERVER['DOCUMENT_ROOT']."/includes/gallery.inc.php"); // zu lange Ladezeiten

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
try {
	$sql = 'SELECT * FROM gallery_pics_faceplusplus WHERE user_id_tagged IS NULL AND width >= "250" AND height >= "250" AND pic_id NOT IN (SELECT pic_id FROM gallery_pics_faceplusplus GROUP BY pic_id HAVING COUNT(pic_id) > 1) ' . ( is_numeric($index) ? 'LIMIT '.$index.',1' : 'ORDER BY RAND() LIMIT 0,1');
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = mysql_fetch_array($result, MYSQL_ASSOC))
	{
	    $pics[] = [
		    'pic_id' => $rs['pic_id'],
		    //'img_path' => imgsrcPic($rs['pic_id']),
		    'img_path' => 'https://zorg.ch/gallery/' . $rs['pic_id'],
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
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($pics);
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo json_encode($e->getMessage());
}
