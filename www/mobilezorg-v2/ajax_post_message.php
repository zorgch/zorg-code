<?
/**
 * FILE INCLUDES
 */
require_once 'config.php';
require_once PHP_INCLUDES_DIR.'mobilez/chat.inc.php';

if(isset($_POST['message']) && $user->id > 0)
{
	$from_mobile = (!isset($_POST['from_mobile']) ? 0 : $_POST['from_mobile']);
	
	/**
	 * CHAT COMMAND
	 */
	if ($_POST['message'][0] == '/')
	{
		$command = substr(stristr($_POST['message'], ' ', true), 1);
		$parameters = ltrim(substr($_POST['message'], stripos($_POST['message'], ' ')));
		if (!empty($command)) {
			$mobilezChat->execChatMessageCommand($user->id, $command, $parameters);
		} else {
			header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=Command%20is%20invalid%21");
		}
		
	/**
	 * CHAT MESSAGE
	 */
	} else {
		$fake_user_id_arr = array(1,2,3,7,8,9,10,11,13,14,15,16,17,18,22,26,30,37,51,52,59,117);
		$user_id = array_rand($fake_user_id_arr, 1);//$user->id;
		$mobilezChat->postChatMessage($user_id, $_POST['message'], $from_mobile);
	}
} else {
	header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=Message%20is%20empty%21");
}

// In case this Script was called directly...
header("Location: ".SITE_URL."/mobilezorg-v2/");