<?php
//=============================================================================
// Includes
//=============================================================================
require_once( __DIR__ .'/includes/main.inc.php');

//=============================================================================
// Output
//=============================================================================
if ($_GET['layout'] == '') {
	
	$id = ($_GET['parent_id'] > 1) ? $_GET['parent_id'] : $_GET['thread_id'];
	
  if ($id <= 1) { // Forumübersicht ausgeben
	
    $parent_id = 1;
    //echo head(4, "forum");
    $smarty->assign('tplroot', array('page_title' => 'forum'));
	$smarty->display('file:layout/head.tpl');
    echo menu("zorg");
    
    
    
    if($user->typ == USER_NICHTEINGELOGGT) {
  	echo Forum::getHTML(array('f', 't', 'e'), 23, $_GET['sortby']); // Boards: f=forum, t=templates, e=events
    } else {
  	
  	echo Forum::getHTML(Forum::getBoards($user->id), 23, $_GET['sortby']);
  	//echo ($_SESSION['user_id'] ? Forum::getFormNewPart1of2() : ''); @DEPRECATED
			//echo Forum::getFormNewPart2of2('f', 1, 0);
			$smarty->assign("board", "f");
			$smarty->assign("thread_id", 1);
			$smarty->assign("parent_id", 0);
			//$smarty->display("tpl:194"); @DEPRECATED
			$smarty->display("file:commentform.tpl");
    }
    
    
  } else {  // Thread ausgeben
	
	
		//$rs = Comment::getRecordset(Comment::getThreadid($_GET[parent_id]));
		
		//echo head(4, "thread");
		$smarty->assign('tplroot', array('page_title' => 'thread'));
		$smarty->display('file:layout/head.tpl');
		echo menu("zorg");
		
		//if($_SESSION['user_id']) echo Forum::getFormNewPart1of2(); @DEPRECATED
		
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
				//$smarty->display("tpl:194"); @DEPRECATED
				$smarty->display("file:commentform.tpl");
				
			}
		}
  }
	
  //echo foot();
  $smarty->display('file:layout/footer.tpl');
}

// Forumsuche ----------------------------------------------------------------
if($_GET['layout'] == 'search') {
	$smarty->assign('tplroot', array('page_title' => 'commentsearch'));
	$smarty->display('file:layout/head.tpl');
	echo menu("zorg");
	echo Forum::getFormSearch();
	echo Forum::printSearchedComments($_GET['keyword']);
	//echo foot();
	$smarty->display('file:layout/footer.tpl');
}

// Editseite ------------------------------------------------------------------
if($_GET['layout'] == 'edit' && $_SESSION['user_id']) {
	//echo head(4, "commentedit");
	$smarty->assign('tplroot', array('page_title' => 'commentedit'));
	$smarty->display('file:layout/head.tpl');
	echo menu("zorg");
	$rs = Comment::getRecordset($_GET['id']);
	if($_SESSION['user_id'] == $rs['user_id']) {
		echo Forum::getFormEdit($_GET['id']);
	}
	//echo foot();
	$smarty->display('file:layout/footer.tpl');
}
