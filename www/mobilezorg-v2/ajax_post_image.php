<?php
/**
 * FILE INCLUDES
 */
require_once 'config.php';
require_once PHP_INCLUDES_DIR.'mobilez/chat.inc.php';

if(!empty($_FILES) && $user->id > 0)
{
	if(empty($_FILES['upload_file']['error']))
	{
		/* Passed File-Array structure:
		Array
		(
		    [upload_file] => Array
		        (
		            [name] => Sample Image.png
		            [type] => image/png
		            [tmp_name] => /Applications/MAMP/tmp/php/phpKUqTZQ
		            [error] => 0
		            [size] => 194537
		        )
		
		)
		*/
		$from_mobile = 1;
		$pathinfo = pathinfo($_FILES['upload_file']['name']);
		$file_name = $pathinfo['filename'];
		$file_extension = $pathinfo['extension'];
		$mobilezChat->saveImage($user->id, $_FILES['upload_file']['tmp_name'], $_FILES['upload_file']['size'], $_FILES['upload_file']['type'], $file_name, $file_extension, $from_mobile);
		error_log(sprintf('[INFO] <%s:%d> File %s%s successfully uploaded!', 'mobilezorg-v2/ajax_post_image', __LINE__, $file_name, $file_extension));

		http_response_code(200);
		echo $file_name.'.'.$file_extension.' successfully uploaded!';
		//header("Location: ".SITE_URL."/mobilezorg-v2/"); // Reload Chat -> solved in JS
	} else {
		error_log(sprintf('[WARN] <%s:%d> File upload error: %s', 'mobilezorg-v2/ajax_post_image', __LINE__, $_FILES['upload_file']['error']));
		http_response_code(406); // Set response code 406 (Not Acceptable) and exit.
		exit($_FILES['upload_file']['error']);
	}
} else {
	// If the passed File(s) are invalid
	if (isset($_FILES['upload_file']['tmp_name']))
	{
		http_response_code(415); // Set response code 415 (Unsupported Media Type) and exit.
		exit('Issue with File: '.$_FILES['upload_file']['tmp_name']);
	} else {
		// ...or in case this Script was called directly
		http_response_code(411); // Set response code 411 (Length Required) and exit.
		header('Location: '.SITE_URL.'/mobilezorg-v2/');
		exit;
	}
}
