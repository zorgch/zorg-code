<?php
require_once( __DIR__ .'/../includes/main.inc.php');

if(count($_POST) > 0)
{
	/** Delete SQL-Error */
	if($_POST['del'] && !empty($_GET['id']))
	{
		$sql_del = 'DELETE FROM sql_error WHERE id='.$_GET['id'];
		$db->query($sql_del, __FILE__, __LINE__, 'Delete SQL-Error');
		header('Location: /tpl/'.$_GET['tpl']);
		die();
	}

	/** Show Query details */
	if($_POST['query'])
	{
		header('Location: /tpl/'.$_GET['tpl'].'&id='.$_GET['id'].'&query='.base64_encode($_POST['query']));
		die();
	}

	/** Delete multiple SQL-Errors */
	if(count($_POST['to_del']) > 0)
	{
		$del_ids = implode(',', $_POST['to_del']);
		$sql = 'DELETE FROM sql_error WHERE id IN ('.$del_ids.')';
		$db->query($sql, __FILE__, __LINE__, 'Delete multiple SQL-Errors');
		header('Location: /tpl/'.$_GET['tpl']);
		die();
	}

	/** Change displayed number of SQL-Error */
	if($_POST['num'])
	{
		$_SESSION['error_num'] = $_POST['num'];
		header('Location: /tpl/'.$_GET['tpl'].'?error_num='.$_POST['num']);
		die();
	}
}
