<?php
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

if($user->is_loggedin() && count($_POST) > 0)
{
	/** Input validation & sanitization */
	$errorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? null; // $_GET['id']
	$tplId = filter_input(INPUT_GET, 'tpl', FILTER_VALIDATE_INT) ?? null; // $_GET['tpl']
	$doDelete = filter_input(INPUT_POST, 'del', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['del']
	$showQuery = filter_input(INPUT_POST, 'query', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 0; // $_POST['query']
	$del_ids = (isset($_POST['to_del']) ? call_user_func_array('array_merge', array($_POST['to_del'])) : null); // $_POST['to_del']
	$showNum = filter_input(INPUT_POST, 'num', FILTER_VALIDATE_INT) ?? 0; // $_POST['num']
	$urlParams = '';

	/** Delete SQL-Error */
	if($doDelete === 'delete' && $errorId>0)
	{
		$sql_del = 'DELETE FROM sql_error WHERE id=?';
		$db->query($sql_del, __FILE__, __LINE__, 'Delete SQL-Error', [$errorId]);
	}

	/** Show Query details */
	if(!empty($showQuery))
	{
		$urlParams = '?id='.$errorId.'&query='.base64url_encode($showQuery);
	}

	/** Delete multiple SQL-Errors */
	if(count($del_ids) > 0 && $user->typ >= USER_MEMBER)
	{
		$placeholders = implode(',', array_fill(0, count($del_ids), '?'));
		$sql = 'DELETE FROM sql_error WHERE id IN (' . $placeholders . ')';
		$params = array_map('intval', $del_ids); // $del_ids must be integers
		$db->query($sql, __FILE__, __LINE__, 'Delete multiple SQL-Errors', $params);
	}

	/** Change displayed number of SQL-Error */
	if($showNum > 0)
	{
		$_SESSION['error_num'] = intval($_POST['num']);
		$urlParams = '?error_num='.$showNum;
	}

	header('Location: /tpl/'.$tplId.$urlParams);
	exit;
}
else {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
}
