<?php
/**
 * Forum
 * 
 * Das Forum Modul enthält 3 Klassen für alle Features:
 * - Forum
 * - Thread
 * - Comment
 * Mit diesen drei Bestandteilen wird das ganze Forum,
 * dessen Threads und das Commenting dazu - oder auch
 * eigenständige Commenting-Instanzen für Templates
 * erzeugt und abgehandelt.
 *
 * @package zorg\Forum
 */

/**
 * File includes
 * @include main.inc.php required
 * @include core.model.php required
 */
require_once( __DIR__ .'/includes/main.inc.php');
require_once( __DIR__ .'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Forum();

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
		//$smarty->assign('tplroot', array('page_title' => 'forum', 'page_link' => $_SERVER['PHP_SELF']));
		//echo menu('zorg');
		$model->showOverview($smarty);
		$smarty->display('file:layout/head.tpl');

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
			$smarty->assign('board', 'f');
			$smarty->assign('thread_id', 1);
			$smarty->assign('parent_id', 0);
			echo t('forum-new-thread', 'commenting');
			$smarty->display('file:layout/partials/commentform.tpl');
		}

	/**
	 * Thread ausgeben
	 */
	} else {
		$outputContent = '';
		$rsparent = Comment::getRecordset($id);
		$parent_id = $rsparent['parent_id'];
		$thread = $db->fetch($db->query('SELECT * FROM comments WHERE id='.$id, __FILE__, __LINE__, 'SELECT * FROM comments'));

		/**
		 * Google typically displays the first 50–60 characters of a title tag.
		 * If you keep your titles under 60 characters, our research suggests that you can expect about 90% of your titles to display properly.
		 * @link https://moz.com/learn/seo/title-tag
		 */
		//$page_title = text_width(remove_html($thread['text']), 50, '', true, true);
		//$smarty->assign('tplroot', array('page_title' => (!empty($page_title) ? $page_title : 'thread #'.$thread['thread_id']), 'page_link' => '/thread/'.$thread['thread_id']));
		//echo menu('zorg');

		/** Forum / Commenting Error anzeigen */
		if (isset($_GET['error']) && !empty($_GET['error']))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $_GET['error']]);
			$outputContent .= $smarty->fetch('file:layout/elements/block_error.tpl');
		}

		/** Thread not found */
		if (!$thread)
		{
			$no_form = true;
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-thread_id', 'commenting')]);
			$outputContent .= $smarty->fetch('file:layout/elements/block_error.tpl');
			$model->threadNotFound($smarty);
			http_response_code(404); // Set response code 404 (not found)
		} else {
			/** damit man die älteren kompilierten comments löschen kann (speicherplatz sparen) */
			Thread::setLastSeen($thread['board'], $thread['thread_id']);

			$model->showThread($smarty, $thread['thread_id'], $thread['text']);

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
				$smarty->assign('comments_subscribed', $comments_subscribed);
	
				// Unread Posts bauen
				$comments_unread = array();
				$e = $db->query('SELECT u.comment_id 
								 FROM comments c, comments_unread u
								 WHERE c.id=u.comment_id AND c.thread_id='.$thread['thread_id'].' AND u.user_id ='.$user->id,
								__FILE__, __LINE__, 'SELECT u.comment_id'
							);
				while ($d = $db->fetch($e)) $comments_unread[] = $d['comment_id'];
				$smarty->assign('comments_unread', $comments_unread);
			}

			if ($parent_id == 1)
			{
				$comments_resource = ($id === $thread['thread_id'] ? $thread['board'].'-'.$id : $id);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: %s', __FILE__, __LINE__, $parent_id, $comment_resource));
				$outputContent .= $smarty->fetch('comments:'.$comments_resource);
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: id=%d', __FILE__, __LINE__, $parent_id, $id));
				$smarty->assign('comments_top_additional', 1);
				$outputContent .= $smarty->fetch('comments:'.$id);
			}

			/** Commentform zum posten printen */
			if ($user->is_loggedin() && !$no_form)
			{
				$smarty->assign('board', 'f');
				$smarty->assign('thread_id', Comment::getThreadid('f', $id));
				$smarty->assign('parent_id', $id);
				$outputContent .= $smarty->fetch('file:layout/partials/commentform.tpl');
			}
		}

		$smarty->display('file:layout/head.tpl');
		echo $outputContent;
	}
}

/**
 * Forumsuche
 */
elseif ($_GET['layout'] == 'search')
{
	//$smarty->assign('tplroot', array('page_title' => 'commentsearch', 'page_link' => $_SERVER['PHP_SELF'].'?layout=search'));
	$model->showSearch($smarty);
	$smarty->display('file:layout/head.tpl');
	//echo menu('zorg');
	echo Forum::getFormSearch();
	echo Forum::printSearchedComments($_GET['keyword']);
}

/**
 * Comment Editseite
 */
elseif($_GET['layout'] == 'edit' && $user->id > 0)
{
	//$smarty->assign('tplroot', array('page_title' => 'commentedit'));
	$model->editComment($smarty);
	$smarty->display('file:layout/head.tpl');
	//echo menu('zorg');
	$rs = Comment::getRecordset($_GET['id']);

	/** Check if $user->id is Comment-Owner */
	if($user->id == $rs['user_id']) {
		echo Forum::getFormEdit($_GET['id']);
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-comment-edit-permissions', 'commenting')]);
		$smarty->display('file:layout/elements/block_error.tpl');
	}
}

/** Page Footer */
$smarty->display('file:layout/footer.tpl');
