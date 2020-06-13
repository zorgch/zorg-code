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
	if ($showCommentId <= 1 || empty($showCommentId))
	{
		$parent_id = 1;

		/** Forum / Commenting Error anzeigen */
		if (!empty($errorMessage))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $errorMessage]);
		}

		$model->showOverview($smarty);
		$smarty->display('file:layout/head.tpl');

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
		$thread = Comment::getRecordset($showCommentId);
		$parent_id = $thread['parent_id'];

		/** Forum / Commenting Error anzeigen */
		if (!empty($errorMessage))
		{
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $errorMessage]);
		}

		/** Thread not found */
		if (!$thread || $thread['board'] !== 'f')
		{
			http_response_code(404); // Set response code 404 (not found)
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-thread_id', 'commenting')]);
			$model->threadNotFound($smarty);
			$smarty->display('file:layout/head.tpl');
		} else {
			$model->showThread($smarty, $thread['thread_id'], $thread['text']);

			/* DISABLED weil duplicate mit Forum::printCommentingSystem() // IneX, 13.05.2020)
			if ($parent_id == 1)
			{
				$outputContent .= '<h1>'.remove_html(Comment::getLinkThread($thread['board'], $showCommentId)).'</h1>';
				$comments_resource = ($showCommentId === $thread['thread_id'] ? $thread['board'].'-'.$showCommentId : $showCommentId);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: %s', __FILE__, __LINE__, $parent_id, $comment_resource));
				$outputContent .= $smarty->fetch('thread:'.$comments_resource);
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $parent_id == %d: id=%d', __FILE__, __LINE__, $parent_id, $showCommentId));
				$smarty->assign('comments_top_additional', 1);
				$outputContent .= $smarty->fetch('comments:'.$showCommentId);
			}

			// Commentform zum posten printen
			if ($user->is_loggedin() && !$no_form)
			{
				$smarty->assign('board', 'f');
				$smarty->assign('thread_id', Comment::getThreadid('f', $showCommentId));
				$smarty->assign('parent_id', $showCommentId);
				$outputContent .= $smarty->fetch('file:layout/partials/commentform.tpl');
			}*/
			$smarty->display('file:layout/head.tpl');

			/** Thread (erster Comment) muss angezeigt werden */
			if ((integer)$parent_id === 1) echo '<h1>'.Comment::getTitle(substr($thread['text'],0,125), 75).'</h1>';

			Forum::printCommentingSystem('f', $showCommentId);
		}
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
