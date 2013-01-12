<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
	
	include_once($_SERVER['DOCUMENT_ROOT'].'includes/usersystem.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'includes/chess.inc.php');
	
	$board = Chess::get_board(1);
	
?>