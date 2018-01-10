<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');


if(count($_POST) > 0) {
	if($_POST['del']) {
		$sql_del = "UPDATE sql_error set status = 0 WHERE id = '".$_GET['id']."'";
		$db->query($sql_del,__FILE__,__LINE__);
		header("Location: /?tpl=".$_GET['tpl']."&".session_name()."=".session_id());
	} 
	if($_POST['query']) {
		header("Location: /?tpl=".$_GET['tpl']."&id=".$_GET['id']."&query=".base64_encode($_POST['query'])."&".session_name()."=".session_id());
	} 
	if(@count($_POST['to_del']) > 0) {
		$sql = "UPDATE sql_error set status = 0 WHERE id in(";
		foreach($_POST['to_del'] as $del) {
			$sql .= $del.",";	
		}
		$sql .= "0)";
		$db->query($sql,__FILE__,__LINE__);
		header("Location: /?tpl=".$_GET['tpl']."&".session_name()."=".session_id());
	}
	if($_POST['num']) {
		$_SESSION['error_num'] = $_POST['num'];
		header("Location: /?tpl=".$_GET['tpl']."&".session_name()."=".session_id());
	}
}
