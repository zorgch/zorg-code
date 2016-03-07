<?
/**
 * FILE INCLUDES
 */
require_once 'config.php';
require_once PHP_INCLUDES_DIR.'mobilez/chat.inc.php';

$json_data = json_decode($_POST['locationData']);
$from_mobile = $json_data->{'from_mobile'};
$location = str_replace(' ', '', $json_data->{'location'});

if(!empty($location) && $user->id > 0)
{
	$mobilezChat->postGoogleMapsLocation($user->id, $location, $from_mobile);
	error_log('[INFO] Location saved: '.$location);
	//header("Location: ".SITE_URL."/mobilezorg-v2/"); // Reload Chat -> solved in JS
} else {
	// In case the $_POST-values are empty or this Script was called directly...
	error_log('[WARN] Location is empty');
	exit('Location is empty');
}