<?php
/**
 * File includes
 * @include main.inc.php required
 */
require_once( __DIR__ .'/includes/main.inc.php');

/**
 * Forum-Übersicht/-Threads ausgeben
 */
if (!isset($_GET['layout']) || empty($_GET['layout']))
{
	$id = ($_GET['parent_id'] > 1) ? $_GET['parent_id'] : $_GET['thread_id'];

	/**
	 * Forumübersicht ausgeben
	 */
	if ($id <= 1)
	{
		$parent_id = 1;
		//echo head(4, "forum");
		$smarty->assign('tplroot', array('page_title' => 'forum', 'page_link' => $_SERVER['PHP_SELF']));
		$smarty->display('file:layout/head.tpl');
		echo menu('zorg');

		/** Forum / Commenting Error anzeigen */
		if (isset($_GET['error']) && !empty($_GET['error']))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $_GET['error']]);
			$smarty->display('file:layout/elements/block_error.tpl');
		}

		if(!$user->is_loggedin())
		{
			echo Forum::getHTML(['e','f','o','t'], 23, $_GET['sortby']); // Boards: e=Events, f=Forum, o=Tauschbörse, t=Templates
		} else {
			$userForumBoards = (!empty($user->forum_boards) ? $user->forum_boards : (!empty($user->forum_boards_unread) ? $user->forum_boards_unread : $user->default_forum_boards));
			echo Forum::getHTML($userForumBoards, 23, $_GET['sortby']);
			//echo ($_SESSION['user_id'] ? Forum::getFormNewPart1of2() : ''); @DEPRECATED
			//echo Forum::getFormNewPart2of2('f', 1, 0);
			$smarty->assign('board', 'f');
			$smarty->assign('thread_id', 1);
			$smarty->assign('parent_id', 0);
			//$smarty->display('tpl:194'); @DEPRECATED
			echo t('forum-new-thread', 'commenting');
			$smarty->display('file:commentform.tpl');
		}

	/**
	 * Thread ausgeben
	 */
	} else {

		//$rs = Comment::getRecordset(Comment::getThreadid($_GET[parent_id]));

		//echo head(4, "thread");

		//if($_SESSION['user_id']) echo Forum::getFormNewPart1of2(); @DEPRECATED

		$rsparent = Comment::getRecordset($id);
		$parent_id = $rsparent['parent_id'];
		$thread = $db->fetch($db->query('SELECT * FROM comments WHERE id='.$id, __FILE__, __LINE__, 'SELECT * FROM comments'));

		/**
		 * Google typically displays the first 50–60 characters of a title tag.
		 * If you keep your titles under 60 characters, our research suggests that you can expect about 90% of your titles to display properly.
		 * @link https://moz.com/learn/seo/title-tag
		 */
		$page_title = text_width(remove_html($thread['text']), 50, '', true, true);
		$smarty->assign('tplroot', array('page_title' => (!empty($page_title) ? $page_title : 'thread #'.$thread['thread_id']), 'page_link' => '/thread/'.$thread['thread_id']));//Comment::getLinkThread($thread['board'], $thread['thread_id'], FALSE)));
		$smarty->display('file:layout/head.tpl');
		echo menu('zorg');

		/** Forum / Commenting Error anzeigen */
		if (isset($_GET['error']) && !empty($_GET['error']))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $_GET['error']]);
			$smarty->display('file:layout/elements/block_error.tpl');
		}

		if (!$thread)
		{
			http_response_code(404); // Set response code 404 (not found)
			//echo 'Thread not found.';
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-thread_id', 'commenting')]);
			$smarty->display('file:layout/elements/block_error.tpl');
			$no_form = true;
		} else {
			/** damit man die älteren kompilierten comments löschen kann (speicherplatz sparen) */
			Thread::setLastSeen($thread['board'], $thread['thread_id']);

			/** Bei eingeloggten Usern... */
			if ($user->is_loggedin())
			{
				/** Subscribed_Comments Array bauen */
				$comments_subscribed = array();
				$sql = 'SELECT comment_id
						FROM comments_subscriptions
						WHERE board="'.$thread['board'].'" AND user_id='.$user->id;
				$e = $db->query($sql, __FILE__, __LINE__, 'SELECT comment_id');
				while ($d = $db->fetch($e)) $comments_subscribed[] = $d['comment_id'];
				$smarty->assign("comments_subscribed", $comments_subscribed);
	
				// Unread Posts bauen
				$comments_unread = array();
				$e = $db->query('SELECT u.comment_id 
								 FROM comments c, comments_unread u
								 WHERE c.id=u.comment_id AND c.thread_id='.$thread['thread_id'].' AND u.user_id ='.$user->id,
								__FILE__, __LINE__, 'SELECT u.comment_id'
							);
				while ($d = $db->fetch($e)) $comments_unread[] = $d['comment_id'];
				$smarty->assign('comments_unread', $comments_unread);
	
				echo '<br>';
			}

			if($parent_id == 1) {
				$comments_resource = ($id === $thread['thread_id'] ? $thread['board'].'-'.$id : $id);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: %s', __FILE__, __LINE__, $parent_id, $comment_resource));
				$smarty->display('comments:'.$comments_resource);
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: id=%d', __FILE__, __LINE__, $parent_id, $id));
				$smarty->assign('comments_top_additional', 1);
				$smarty->display('comments:'.$id);
			}

			/** Commentform zum posten printen */
			if($user->is_loggedin() && !$no_form)
			{
				//echo Forum::getFormNewPart2of2('f', Comment::getThreadid('f', $id), $id);
				$smarty->assign('board', 'f');
				$smarty->assign('thread_id', Comment::getThreadid('f', $id));
				$smarty->assign('parent_id', $id);
				//$smarty->display('tpl:194'); @DEPRECATED
				$smarty->display('file:commentform.tpl');
			}
		}
	}

	//echo foot();
	$smarty->display('file:layout/footer.tpl');
}

/**
 * Forumsuche
 */
elseif ($_GET['layout'] == 'search')
{
	$smarty->assign('tplroot', array('page_title' => 'commentsearch', 'page_link' => $_SERVER['PHP_SELF'].'?layout=search'));
	$smarty->display('file:layout/head.tpl');
	echo menu('zorg');
	echo Forum::getFormSearch();
	echo Forum::printSearchedComments($_GET['keyword']);
	//echo foot();
	$smarty->display('file:layout/footer.tpl');
}

/**
 * Comment Editseite
 */
elseif($_GET['layout'] == 'edit' && $user->id > 0)
{
	//echo head(4, "commentedit");
	$smarty->assign('tplroot', array('page_title' => 'commentedit'));
	$smarty->display('file:layout/head.tpl');
	echo menu('zorg');
	$rs = Comment::getRecordset($_GET['id']);

	/** Check if $user->id is Comment-Owner */
	if($user->id == $rs['user_id']) {
		echo Forum::getFormEdit($_GET['id']);
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-comment-edit-permissions', 'commenting')]);
		$smarty->display('file:layout/elements/block_error.tpl');
	}
	//echo foot();
	$smarty->display('file:layout/footer.tpl');
}
