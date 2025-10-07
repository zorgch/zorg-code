<?php
/**
 * This is where it all starts.
 * The Index. A digital zorg. We tried to picture clusters of information as they traveled through to your computer.
 * Bits, bytes. With the network like freeways. We kept dreaming of a zorg we thought we'd never see.
 * And then, one day... we got it running.
 */
/**
 * File Includes
 * @include config.inc.php Global Configs
 * @include main.inc.php Layout Stuff
 */
require_once __DIR__.'/includes/config.inc.php';
require_once INCLUDES_DIR.'main.inc.php';

/**
 * Parse URL-Routes & Query Parameters
 * @TODO Route-Switch ist dirty - muss challenged werden & braucht wahrscheinlich refactoring!
 * @TODO Event geht nur wenn /event/year/month/day/event(id) mitgegeben wird
 *
 * @see .htaccess
 */
if (!empty(key($_GET)))
{
	$routeSwitch = key($_GET);
	$routeValue = htmlspecialchars(trim($_GET[$routeSwitch]), ENT_QUOTES, 'UTF-8');
	switch ($routeSwitch)
	{
		/** Route: /user/[user-id|username] */
		case 'username':
			$getUserId = ( is_numeric($routeValue) ? $routeValue : $user->user2id($routeValue) );
			if (!empty($getUserId)) {
				$_GET['user_id'] = $getUserId;
				include('profil.php');
				exit;
			}
			break;

		/** Route: /bug/[bug-id] */
		case 'bug':
			if ( is_numeric($routeValue) ) $getBugId = $routeValue;
			if (!empty($getBugId)) {
				$_GET['bug_id'] = $getBugId;
				include('bugtracker.php');
				exit;
			}
			break;

		/** Route: /event/[year]/[month]/[day]/[event-id|eventname] */
		case 'event':
			$tplId = 158; // 158 = Event Template
			$_GET['tpl'] = $tplId;
			$getEventId = (int)$routeValue;
			$_GET['event_id'] = $getEventId;
			break;

		/** Route: /word/[pagetitle] */
		case 'word':
			$tplWord = $routeValue;
			$_GET['word'] = $tplWord;
			break;

		/** Route: /thread/[thread-id] */
		case 'thread':
			if ( is_numeric($routeValue) ) $getThreadId = $routeValue;
			if (!empty($getThreadId)) {
				$_GET['thread_id'] = $getThreadId;
				include('forum.php');
				exit;
			}
			break;
	}
}

/** Input validation and sanitization */
$tplByName = (isset($tplWord) ? $tplWord : (filter_input(INPUT_GET, 'word', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null)); // $_GET['word']
$tplById = (isset($tplId) ? $tplId : (filter_input(INPUT_GET, 'tpl', FILTER_VALIDATE_INT) ?? null)); // $_GET['tpl']
$useLayout = filter_input(INPUT_GET, 'layout', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['layout']
$feedType = filter_input(INPUT_GET, 'type', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['type']
$feedCommentsBoard = filter_input(INPUT_GET, 'board', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['board']
$feedCommentsThreadId = filter_input(INPUT_GET, 'thread_id', FILTER_VALIDATE_INT) ?? null; // $_GET['thread_id']
$eventId = (isset($getEventId) ? $getEventId : (filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT) ?? null)); // $_GET['event_id']
$tplEditor = filter_input(INPUT_GET, 'tpleditor', FILTER_VALIDATE_BOOLEAN); // $_GET['tpleditor'] === "1"
$editTpl = (isset($_GET['tplupd']) && is_numeric($_GET['tplupd']) ? filter_input(INPUT_GET, 'tplupd', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) : (isset($_GET['tplupd']) && $_GET['tplupd'] === 'new' ? 'new' : null));
$tplCreated = filter_input(INPUT_GET, 'created', FILTER_VALIDATE_BOOLEAN); // $_GET['created'] === "1"
$tplUpdated = filter_input(INPUT_GET, 'updated', FILTER_VALIDATE_BOOLEAN); // $_GET['updated'] === "1"

/**
 * RSS Feeds
 * @see Forum::printRSS()
 */
if ($useLayout === 'rss' && !empty($feedType))
{
	$smarty->assign('feeddesc', SITE_HOSTNAME . ' RSS Feed');
	$smarty->assign('feedlang', 'de-DE');
	$smarty->assign('feeddate', date('D, d M Y H:i:s').' GMT');
	$feedURLbase = RSS_URL;

	switch ($feedType)
	{
		/** Forum RSS */
		case 'forum':
			/** ...ein board wurde übergeben */
			if (!empty($feedCommentsBoard))
			{
				/** eine thread_id wurde übergeben */
				if (is_numeric($feedCommentsThreadId) && $feedCommentsThreadId > 0)
				{
					/** RSS Feed für einen einzelnen Thread */
					$smarty->assign('feedtitle', remove_html(Comment::getLinkThread($feedCommentsBoard, $feedCommentsThreadId) . PAGETITLE_SUFFIX) );
					$smarty->assign('feedlink', $feedURLbase . '&amp;amp;type=forum&amp;amp;board=' . $feedCommentsBoard . '&amp;amp;thread_id=' . $feedCommentsThreadId);
					$smarty->assign('feeditems', Forum::printRSS($feedCommentsBoard, (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 ? $_SESSION['user_id'] : null), $feedCommentsThreadId));

				/** keine thread_id vorhanden */
				} else {
					/**
					 * RSS Feed für ein ganzes Board
					 * @TODO Fix "unknown feed" (broken RSS-feed) für Gallery-Comments: ?layout=rss&type=forum&board=i
					 */
					$smarty->assign('feedtitle', remove_html(Forum::getBoardTitle($feedCommentsBoard) . PAGETITLE_SUFFIX) );
					$smarty->assign('feedlink', $feedURLbase . '&amp;amp;type=forum&amp;amp;board=' . $feedCommentsBoard);
					$smarty->assign('feeditems', Forum::printRSS($feedCommentsBoard, (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 ? $_SESSION['user_id'] : null)));
				}

			/** kein board vorhanden */
			} else {
				/** genereller Forum RSS Feed */
				$smarty->assign('feedtitle', 'Forum RSS' . PAGETITLE_SUFFIX);
				$smarty->assign('feedlink', $feedURLbase . '&amp;amp;type=forum');
				$smarty->assign('feeditems', Forum::printRSS(null, (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 ? $_SESSION['user_id'] : null)));
			}
			break;

		/** Activities RSS */
		case 'activities':
			$smarty->assign('feedtitle', remove_html('Activities' . PAGETITLE_SUFFIX));
			$smarty->assign('feedlink', $feedURLbase . '&amp;amp;type=activities');
			$smarty->assign('feeditems', Activities::getActivitiesRSS(25));
			break;
	}

	/** Text-codierung & XML-Encoding an den header senden */
	header("Content-Type: text/xml; charset=UTF-8");
	$smarty->display('file:rss.tpl');

/**
 * Regular Page
 *
 * @uses SMARTY_DEFAULT_TPL
 * @uses SMARTY_404PAGE_TPL
 */
} else {
	/** Standardtemplate setzen, wenn tpl oder word nicht oder leer übergeben wurden */
	if ((empty($tplById) && empty($tplByName)) || ($tplById<=0 && !is_string($tplByName)))
	{
		// If no Templates (yet) in Database, use 0 instead of 23...
		$defaultTplId = ($db->num($db->query('SELECT id FROM templates WHERE id=23 LIMIT 1', __FILE__, __LINE__)) === 1 ? SMARTY_DEFAULT_TPL : 0);
		$_GET['tpl'] = $defaultTplId;
		$tplById = $defaultTplId;
	}

	/** Load Template data */
	if ($tplById > 0 || is_string($tplByName))
	{
		$queryWhere = null;
		$queryParams = [];
		if (!empty($tplByName) && is_string($tplByName)) {
			$queryWhere = 'word=?';
			$queryParams[] = $tplByName;
		}
		else {
			$queryWhere = 'id=?';
			$queryParams[] = $tplById;
		}
		// FIXME change this to use Smarty:: Function!
		$e = $db->query('SELECT id, title, word, LENGTH(tpl) size, owner, update_user, page_title,
						UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights,
						write_rights, force_compile, border, sidebar_tpl, allow_comments FROM templates WHERE '.$queryWhere,
						__FILE__, __LINE__, 'Assign $_TPLROOT', $queryParams);

		/**
		 * No Template found (404)
		 */
		if ($e === false || empty($db->num($e)))
		{
			if (!empty($tplById)) $_TPLROOT['id'] = $tplById;
			if (!empty($tplByName)) $_TPLROOT['word'] = $tplByName;
			$_TPLROOT['page_title'] = sprintf('Page «%s» not found', (isset($_TPLROOT['word']) ? $_TPLROOT['word'] : $_TPLROOT['id']));
			$_TPLROOT['page_link'] = (isset($_TPLROOT['word']) ? '/page/'.$_TPLROOT['word'] : '/tpl/'.$_TPLROOT['id']);
			$_TPLROOT['title'] = $_TPLROOT['page_title'];

			/** Display 404 page */
			http_response_code(404); // Set response code 404 (not found)
			$smarty->assign('tplroot', $_TPLROOT);
			$smarty->display(SMARTY_404PAGE_TPL);
			exit;
		}

		/**
		 * Template found
		 */
		else {
			$_TPLROOT = $db->fetch($e);

			/** Load required packages for the current template */
			load_packages($_TPLROOT['id'], $smarty);

			/** Load Template menus */
			$tpl_menus = load_navigation($_TPLROOT['id'], $smarty);
			if (is_array($tpl_menus)) $_TPLROOT['menus'] = $tpl_menus;

			/** Assign Tpl Id, Template Titles and Template link */
			if (!empty($tplByName)) $_GET['tpl'] = $_TPLROOT['id'];
			if (!empty($_TPLROOT['title']) && $_TPLROOT['title'] !== null) $_TPLROOT['page_title'] = $_TPLROOT['title']; // HTML Page Title
			if (!empty($_TPLROOT['word']) && $_TPLROOT['word'] !== null) $_TPLROOT['page_link'] = '/page/'.$_TPLROOT['word']; // Canonical URL
			else $_TPLROOT['page_link'] = '/tpl/'.$_TPLROOT['id'];

			/** Events special... */
			if ($_TPLROOT['id'] === 158) {
				if (!empty($eventId) && $eventId>0) {
					$_TPLROOT['page_title'] = Events::getEventName($eventId);
					$_TPLROOT['page_link'] = Events::getEventLink($eventId);
				} else {
					$_TPLROOT['page_link'] = '/events/';
				}
			}

			/** Home(page) special... */
			if ($_TPLROOT['word'] === 'home' || $_TPLROOT['id'] === 23) {
				$_TPLROOT['page_title'] = ($user->is_loggedin() ? $_TPLROOT['page_title'] : 'Willkommen auf zorg');
				$_TPLROOT['page_link'] = ' ';
			}

			/** Immer zuletzt: Tpleditor special... */
			if ($tplEditor === true) {
				$_TPLROOT['page_title'] = ($editTpl === 'new' ? 'Neues Template erstellen' : 'Template «'.$_TPLROOT['page_title'].'» bearbeiten');
				$_TPLROOT['sidebar_tpl'] = null; // Clear an assigned Sidebar
			}
			if ($tplCreated === true || $tplUpdated === true) {
				/** If Template was updated from Tpleditor, show a Success-Message */
				$successTitle = sprintf('Template %s!', ($tplCreated ? 'created' : 'updated'));
				unset($_GET['created']); // Remove Query-Param from URL
				unset($_GET['updated']); // Remove Query-Param from URL
				$smarty->assign('error', ['type'=>'success', 'dismissable'=>'true', 'title'=>$successTitle]);
			}
		}
	}
	/**
	 * Inception: Welcome to a new Matrix
	 * (empty instance)
	 */
	else {
		$_TPLROOT['id'] = 0;
		$_TPLROOT['page_title'] = 'Eine neue Version der Matrix 🤩';
		$_TPLROOT['page_link'] = '/tpl/'.$_TPLROOT['id'];
		$_TPLROOT['title'] = $_TPLROOT['page_title'];

		/** Display 404 page */
		$smarty->assign('tplroot', $_TPLROOT);
		$smarty->display('file:layout/pages/inception.tpl');
		exit;
	}

	$_TPLROOT['page_title'] = htmlentities($_TPLROOT['page_title'], ENT_QUOTES); // To prevent breaking HTML syntax, convert all special characters to HTML entities
	$smarty->assign('tplroot', $_TPLROOT);

	/**
	 * SQL Query Tracker
	 */
	if (isset($user->sql_tracker) && $user->sql_tracker) {
	   $_SESSION['noquerys'] = $db->noquerys;
	   $_SESSION['noquerytracks'] = $db->noquerytracks;
	   $_SESSION['query_track'] = $db->query_track;
	   $_SESSION['query_request'] = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	} elseif (isset($_SESSION)) {
	   unset($_SESSION['noquerys']);
	   unset($_SESSION['query_track']);
	   unset($_SESSION['query_request']);
	   unset($_SESSION['noquerytracks']);
	}

	/**
	 * Display the page
	 */
	$smarty->display('file:layout/layout.tpl');

}
