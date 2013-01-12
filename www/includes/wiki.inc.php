<?php
//=============================================================================
// includes
//=============================================================================
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');

Class Wiki {

	function getContent($word) {
	  $html = Wiki::getWord($word);
		if($_SESSION[user_id]) { // falls eingeloggt
			$html .= 
				' <small><a href="/wiki.php?word='.$word.'&mode=edit&url='
				.base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">[EDIT]</a></small>';
		}
	  return $html;
	}
	
	function getWord($word) {
		global $db;
	  $sql = "SELECT * from wiki where word = '".$word."' order by date desc limit 0,1";
	  $result = $db->query($sql, __FILE__, __LINE__);
	  $rs = $db->fetch($result);
		
	  return $rs['text'];
	}
	
	function getHighestThread_id() {
		global $db;
	  $sql = "SELECT thread_id from wiki order by thread_id desc limit 0,1";
	  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	  return $rs['thread_id'];
	}
	
	function getThread_id($word) {
		global $db;
	  $sql = "SELECT thread_id from wiki where word='".$word."' limit 0,1";
	  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	  return $rs['thread_id'];
	}
	
	function getWordFromThread_id($thread_id) {
		global $db;
	  $sql = "SELECT word from wiki where thread_id=".$thread_id." limit 0,1";
	  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	  return $rs['word'];
	}
}


?>