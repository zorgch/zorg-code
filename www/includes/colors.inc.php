<?PHP
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/sunrise.inc.php');
if($sun == "up"){ 
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/colorsday.inc.php');
}
else {
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/colorsnight.inc.php');
}
?>
