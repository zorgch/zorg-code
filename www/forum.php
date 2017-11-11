<?php
//=============================================================================
// Includes
//=============================================================================

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');

//=============================================================================
// Output
//=============================================================================


if ($_GET['layout'] == '') {
	
	$id = ($_GET['parent_id'] > 1) ? $_GET['parent_id'] : $_GET['thread_id'];
	
  if ($id <= 1) { // Forumübersicht ausgeben
  	
    $parent_id = 1;
    echo head(4, "forum");
    echo menu("zorg");
    
    
    
    if($user->typ == USER_NICHTEINGELOGGT) {
    	echo Forum::getHTML(array('f', 't', 'e'), 23, $_GET['sortby']); // Boards: f=forum, t=templates, e=events
    } else {
    	
    	echo Forum::getHTML(Forum::getBoards($user->id), 23, $_GET['sortby']);
    	echo ($_SESSION['user_id'] ? Forum::getFormNewPart1of2() : '');
			//echo Forum::getFormNewPart2of2('f', 1, 0);
			$smarty->assign("board", "f");
			$smarty->assign("thread_id", 1);
			$smarty->assign("parent_id", 0);
			$smarty->display("tpl:194");
    }
    
    
  } else {  // Thread ausgeben
  	
  	
		//$rs = Comment::getRecordset(Comment::getThreadid($_GET[parent_id]));
		
		echo head(4, "thread");
		echo menu("zorg");
		
		if($_SESSION['user_id']) echo Forum::getFormNewPart1of2();
		
		$rsparent = Comment::getRecordset($id);
		$parent_id = $rsparent['parent_id'];
		
		$thread = $db->fetch($db->query("SELECT * FROM comments WHERE id='$id'", __FILE__, __LINE__));
		
		if (!$thread) {
			echo "Thread not found.";
			$no_form = true;
		} else {
			
			// damit man die älteren kompilierten comments löschen kann (speicherplatz sparen)
			Thread::setLastSeen($thread['board'], $thread['thread_id']);
			
			// Subscribed_Comments Array Bauen
			$comments_subscribed = array();
			$sql = "
				SELECT comment_id
				FROM comments_subscriptions
				WHERE board='".$thread['board']."' AND user_id='".$user->id."'
			";
			$e = $db->query($sql, __FILE__, __LINE__);
			while ($d = $db->fetch($e)) $comments_subscribed[] = $d['comment_id'];
			$smarty->assign("comments_subscribed", $comments_subscribed);
			
			
			// Unread Posts bauen
			$comments_unread = array();
			$e = $db->query(
				"SELECT u.comment_id 
				FROM comments c, comments_unread u
				WHERE c.id=u.comment_id AND c.thread_id='$thread[thread_id]' AND u.user_id ='$user->id'",
				__FILE__, __LINE__
			);
			while ($d = $db->fetch($e)) $comments_unread[] = $d['comment_id'];
			$smarty->assign("comments_unread", $comments_unread);		
			
			echo '<br />';
			
			if($parent_id == 1) {
				$smarty->display("comments:$id");
			} else {
				$smarty->assign("comments_top_additional", 1);
				$smarty->display("comments:$parent_id");
			}
			
			// Form zum posten printen
			if($_SESSION['user_id'] && !$no_form) {
				
				//echo Forum::getFormNewPart2of2('f', Comment::getThreadid('f', $id), $id);
				
				$smarty->assign("board", "f");
				$smarty->assign("thread_id", Comment::getThreadid('f', $id));
				$smarty->assign("parent_id", $id);
				$smarty->display("tpl:194");
				
			}
		}
  }
	
  echo foot();
}

// Forumsuche ----------------------------------------------------------------
if($_GET['layout'] == 'search') {
	echo(
		head(4, "commentsearch")
		.menu("zorg")
		.Forum::getFormSearch()
	);
	echo Forum::printSearchedComments($_GET['keyword']);
	echo foot();
}

// Editseite ------------------------------------------------------------------
if($_GET['layout'] == 'edit' && $_SESSION['user_id']) {
	echo head(4, "commentedit");
	echo menu("zorg");
	$rs = Comment::getRecordset($_GET['id']);
	if($_SESSION['user_id'] == $rs['user_id']) {
  	echo Forum::getFormEdit($_GET['id']);
	}
	echo foot();
}


/**
  * RSS Feed
  * @author IneX
  * @date 16.03.2008
  * @desc RSS Feed für einzelne Boards oder für alles
  * @param $_GET['layout'] string
  * @param $_GET['board'] string
  * @param $_GET['thread_id'] int
  * @param $_SESSION['user_id'] int
  */
// RSS soll angezeigt werden
if($_GET['layout'] == 'rss') {
	
	// ein board wurde übergeben
	if ($_GET['board'] <> '') {
	
		// eine thread_id wurde übergeben
		if ($_GET['thread_id'] <> '') {
			// RSS Feed für einen einzelnen Thread
			echo rss(remove_html(Comment::getLinkThread($_GET['board'], Comment::getThreadid($_GET['board'], $_GET['thread_id'])))." @ zorg.ch", SITE_URL . "/forum.php", "Zorg RSS Feed", Forum::printRSS($_GET['board'], $_SESSION['user_id'], $_GET['thread_id']));
		
		// keine thread_id vorhanden
		} else {
			// RSS Feed ein ganzes Board
			echo rss(Forum::getBoardTitle($_GET['board'])." @ zorg.ch", SITE_URL . "/forum.php", "Zorg RSS Feed", Forum::printRSS($_GET['board'], $_SESSION['user_id'], $_GET['thread_id']));
		}

	// kein board vorhanden
	} else {
		// genereller Zorg RSS Feed
		echo rss("RSS @ zorg.ch", SITE_URL, "zorg.ch RSS Feed - Forum, Events, Gallery and more", Forum::printRSS(null, $_SESSION['user_id']));
	}
} // end if layout = rss

?>
