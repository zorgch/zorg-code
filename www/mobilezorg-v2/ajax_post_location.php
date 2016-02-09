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
{ error_log('[DEBUG] '.$location);//debug
	$mobilezChat->postGoogleMapsLocation($user->id, $location, $from_mobile);
	exit();
} else {
	exit('Location ist empty: '.$location);
}

// In case this Script was called directly...
header("Location: ".SITE_URL."/mobilezorg-v2/");