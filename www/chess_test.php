<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
	
	include_once(SITE_ROOT.'/includes/usersystem.inc.php');
	include_once(SITE_ROOT.'/includes/chess.inc.php');
	
	$board = Chess::get_board(1);
	
?>