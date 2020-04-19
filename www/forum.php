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
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Forum();

/**
 * Validate passed Parameters
 */
$doAction = (isset($_GET['layout']) && !empty($_GET['layout']) && is_string($_GET['layout']) ? sanitize_userinput($_GET['layout']) : null);
$searchKeyword = (isset($_GET['keyword']) && !empty($_GET['keyword']) && is_string($_GET['keyword']) ? sanitize_userinput($_GET['keyword']) : null);
$commentId = (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0 ? sanitize_userinput($_GET['id']) : null);
$threadId = (isset($_GET['thread_id']) && !empty($_GET['thread_id']) && is_numeric($_GET['thread_id']) && $_GET['thread_id'] > 0 ? sanitize_userinput($_GET['thread_id']) : null);
$commentParentId = (isset($_GET['parent_id']) && !empty($_GET['parent_id']) && is_numeric($_GET['parent_id']) && $_GET['parent_id'] > 0 ? sanitize_userinput($_GET['parent_id']) : null);
$sortBy = (isset($_GET['sortby']) && !empty($_GET['sortby']) && is_string($_GET['sortby']) ? sanitize_userinput($_GET['sortby']) : null);
$errorMessage = (isset($_GET['error']) && !empty($_GET['error']) ? sanitize_userinput($_GET['error']) : null);

/**
 * Forum-Übersicht/-Threads ausgeben
 */
if (empty($doAction))
{
	/** Auf gültige Comment-ID prüfen */
	$showCommentId = ($commentParentId > 1) ? $commentParentId : ($threadId > 0 ? $threadId : null);

	/**
	 * Forumübersicht ausgeben
	 */
	if ($showCommentId <= 1)
	{
		$parent_id = 1;

		$model->showOverview($smarty);
		$smarty->display('file:layout/head.tpl');

		/** Forum / Commenting Error anzeigen */
		if (!empty($errorMessage))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $errorMessage]);
			$smarty->display('file:layout/elements/block_error.tpl');
		}

		if(!$user->is_loggedin())
		{
			echo Forum::getHTML(['e','f','o','t'], 23, $sortBy); // Boards: e=Events, f=Forum, o=Tauschbörse, t=Templates
		} else {
			$userForumBoards = (!empty($user->forum_boards) ? $user->forum_boards : (!empty($user->forum_boards_unread) ? $user->forum_boards_unread : $user->default_forum_boards));
			echo Forum::getHTML($userForumBoards, 23, $sortBy);
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
		$rsparent = Comment::getRecordset($showCommentId);
		$parent_id = $rsparent['parent_id'];
		$thread = $db->fetch($db->query('SELECT * FROM comments WHERE id='.$showCommentId, __FILE__, __LINE__, 'SELECT * FROM comments'));

		/** Forum / Commenting Error anzeigen */
		if (!empty($errorMessage))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $errorMessage]);
			$outputContent .= $smarty->fetch('file:layout/elements/block_error.tpl');
		}

		/** Thread not found */
		if (!$thread)
		{
			$no_form = true;
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-thread_id', 'commenting')]);
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
				$comments_resource = ($showCommentId === $thread['thread_id'] ? $thread['board'].'-'.$showCommentId : $showCommentId);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: %s', __FILE__, __LINE__, $parent_id, $comment_resource));
				$outputContent .= $smarty->fetch('comments:'.$comments_resource);
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: id=%d', __FILE__, __LINE__, $parent_id, $showCommentId));
				$smarty->assign('comments_top_additional', 1);
				$outputContent .= $smarty->fetch('comments:'.$showCommentId);
			}

			/** Commentform zum posten printen */
			if ($user->is_loggedin() && !$no_form)
			{
				$smarty->assign('board', 'f');
				$smarty->assign('thread_id', Comment::getThreadid('f', $showCommentId));
				$smarty->assign('parent_id', $showCommentId);
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
elseif ($doAction === 'search')
{
	$model->showSearch($smarty);

	/** Only for logged in Users */
	if ($user->is_loggedin())
	{
		$smarty->display('file:layout/head.tpl');
		echo Forum::getFormSearch($searchKeyword);
		if (!empty($searchKeyword))
		{
			echo Forum::printSearchedComments($searchKeyword);
		} else {
			echo t('error-search-noresult', 'commenting', ['[leer]']);
		}
	}

	/** Prevent Forum Search for anonymous visitors (wegen ganzen SQL-Inject Attacken) */
	else {
		http_response_code(403); // Set response code 403 (Forbidden)
		$smarty->assign('error', ['type' => 'info', 'dismissable' => 'false', 'title' => t('invalid-permissions-search', 'commenting')]);
		$smarty->display('file:layout/head.tpl');
	}
}

/**
 * Comment Editseite
 */
elseif($doAction === 'edit' && $user->is_loggedin())
{
	$model->editComment($smarty);
	$smarty->display('file:layout/head.tpl');
	$rs = Comment::getRecordset($commentId);

	/** Check if $user->id is Comment-Owner */
	if($user->id == $rs['user_id']) {
		echo Forum::getFormEdit($commentId);
	} else {
		http_response_code(403); // Set response code 403 (Forbidden)
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-comment-edit-permissions', 'commenting')]);
		$smarty->display('file:layout/elements/block_error.tpl');
	}
}

/** Page Footer */
$smarty->display('file:layout/footer.tpl');
