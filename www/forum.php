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
require_once __DIR__.'/includes/config.inc.php';
require_once INCLUDES_DIR.'main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Forum();

/**
 * Validate passed Parameters
 */
$doAction = filter_input(INPUT_GET, 'layout', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
$searchKeyword = filter_input(INPUT_GET, 'keyword', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
$commentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? null;
$threadId = (isset($getThreadId) ? $getThreadId : (filter_input(INPUT_GET, 'thread_id', FILTER_VALIDATE_INT) ?? null));
$commentParentId = filter_input(INPUT_GET, 'parent_id', FILTER_VALIDATE_INT) ?? null;
$sortBy = filter_input(INPUT_GET, 'sortby', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
$errorMessage = filter_input(INPUT_GET, 'error', FILTER_SANITIZE_FULL_SPECIAL_CHARS, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]) ?? null;

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
	if (empty($showCommentId))
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
	 * Comments und Threads ausgeben
	 */
	} else {
		$outputContent = '';
		$no_form = false; // Commentform is: true=hidden / false=shown
		$rsparent = Comment::getRecordset($showCommentId);
		$parent_id = intval($rsparent['parent_id']);
		$thread = $db->fetch($db->query('SELECT * FROM comments WHERE id=?', __FILE__, __LINE__, 'SELECT * FROM comments', [$showCommentId]));

		/** Forum / Commenting Error anzeigen */
		if (!empty($errorMessage))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $errorMessage]);
			$outputContent .= $smarty->fetch('file:layout/elements/block_error.tpl');
		}

		/** Comment not found */
		if (false === $thread || empty($thread))
		{
			$no_form = true;
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-thread_id', 'commenting')]);
			$model->threadNotFound($smarty);
			http_response_code(404); // Set response code 404 (not found)
		}
		/** Comment found & fetched from DB */
		else {
			/** damit man die älteren kompilierten comments löschen kann (speicherplatz sparen) */
			Thread::setLastSeen($thread['board'], $thread['thread_id']);

			$model->showThread($smarty, $thread['thread_id'], $thread['text']);

			/** Zusätzliche Features & Output nur bei eingeloggten Usern... */
			if ($user->is_loggedin())
			{
				/** Subscribed_Comments Array bauen */
				$comments_subscribed = []; // Könnte leer bleiben wenn 0=Subscribed...
				$sql_subs = 'SELECT comment_id FROM comments_subscriptions WHERE board=? AND user_id=?';
				$e = $db->query($sql_subs, __FILE__, __LINE__, 'SELECT comment_id', [$thread['board'], $user->id]);
				while ($d = $db->fetch($e)) $comments_subscribed[] = $d['comment_id'];
				$smarty->assign('comments_subscribed', $comments_subscribed);

				/** Unread Posts bauen */
				$comments_unread = []; // Könnte leer bleiben wenn 0=Unreads...
				$sql_unreads = 'SELECT u.comment_id FROM comments c, comments_unread u WHERE c.id=u.comment_id AND c.thread_id=? AND u.user_id=?';
				$e = $db->query($sql_unreads, __FILE__, __LINE__, 'SELECT u.comment_id', [$thread['thread_id'], $user->id]);
				while ($d = $db->fetch($e)) {
					$comments_unread[] = $d['comment_id'];
				}
				$smarty->assign('comments_unread', $comments_unread);
			}

			/** Comment is the Thread-Comment */
			if ($parent_id === 1)
			{
				$comments_resource = ($showCommentId === $thread['thread_id'] ? $thread['board'].'-'.$showCommentId : $showCommentId);
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: %s', __FILE__, __LINE__, $parent_id, $comments_resource));
				$outputContent .= $smarty->fetch('comments:'.$comments_resource);
			}
			/** Comment is a regular (Sub-)Comment */
			else {
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: id=%d', __FILE__, __LINE__, $parent_id, $showCommentId));
				$smarty->assign('comments_top_additional', 1);
				$outputContent .= $smarty->fetch('comments:'.$showCommentId);
			}

			/** Commentform zum posten printen (bei eingeloggten Usern) */
			if ($user->is_loggedin() && $no_form === false)
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
	if($user->id === (int)$rs['user_id']) {
		echo Forum::getFormEdit($commentId);
	} else {
		http_response_code(403); // Set response code 403 (Forbidden)
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-comment-edit-permissions', 'commenting')]);
		$smarty->display('file:layout/elements/block_error.tpl');
	}
}

/** Page Footer */
$smarty->display('file:layout/footer.tpl');
