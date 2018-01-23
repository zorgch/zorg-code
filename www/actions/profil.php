<?PHP
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once( __DIR__ .'/../includes/mysql.inc.php');
require_once( __DIR__ .'/../includes/usersystem.inc.php');

if($_GET['do'] == 'aussperren') {
	$sql =	
		"
			UPDATE user 
			SET 
				".AUSGESPERRT_BIS." = '"
					.$_POST['aussperrenYear']."-".$_POST['aussperrenMonth']."-".$_POST['aussperrenDay']." ".$_POST['aussperrenHour'].":00' 
			WHERE id = ".$user->id;
	$db->query($sql, __FILE__, __LINE__);
	header("Location: /profil.php?do=view");
	exit;
}
