<?PHP
require_once( __DIR__ .'/../includes/main.inc.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

//echo $user->id, " | ", $user->is_mobile, " | ", $_POST['text'], " | ", $_POST['url'];

//$sql = "INSERT INTO chat (user_id, date, from_mobile, text) VALUES ($user->id, now(), $user->is_mobile, '".$_POST['text']."')";
$from_mobile = ($_POST['from_mobile'] != '' || $_POST['from_mobile'] > 0) ? 1 : 0 ;
$sql = "INSERT INTO chat (user_id, date, from_mobile, text) VALUES ($user->id, now(), $from_mobile, '".$_POST['text']."')";
$db->query($sql, __FILE__, __LINE__);

header("Location: ".base64_decode($_POST['url']));
exit;
