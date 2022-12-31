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
			$_GET['tpl'] = 158; // 158 = Event Template
			$_GET['event_id'] = $routeValue;
			break;

		/** Route: /word/[pagetitle] */
		case 'word':
			$_GET['word'] = $routeValue;
			break;

		/** Route: /thread/[thread-id] */
		case 'thread':
			if ( is_numeric($routeValue) ) $getThreadId = $routeValue;
			if (!empty($getThreadId)) {
				$_GET['thread_id'] = $getThreadId;
				include('forum.php');
				die();
			}
			break;
	}
}
/**
 * Standardtemplate setzen, wenn tpl oder word nicht oder leer übergeben wurden
 */
if (isset($_GET['layout']) && $_GET['layout'] != 'rss' && ((!isset($_GET['tpl']) && !isset($_GET['word'])) ||
	(empty($_GET['tpl']) && empty($_GET['word'])) || ($_GET['tpl'] <= 0 || is_numeric($_GET['word']))))
	{
		// If no Templates (yet) in Database, use 0 instead of 23...
		$_GET['tpl'] = ($db->num($db->query('SELECT id FROM templates WHERE id=23 LIMIT 1', __FILE__, __LINE__)) === 1 ? SMARTY_DEFAULT_TPL : 0);
	}


/**
 * RSS Feeds
 * @see Forum::printRSS()
 */
if (isset($_GET['layout']) && $_GET['layout'] === 'rss' && isset($_GET['type']))
{
	$smarty->assign('feeddesc', SITE_HOSTNAME . ' RSS Feed');
	$smarty->assign('feedlang', 'de-DE');
	$smarty->assign('feeddate', date('D, d M Y H:i:s').' GMT');
	$feedURLbase = $_ENV['URLPATH_RSS'];

	switch ($_GET['type'])
	{
		/** Forum RSS */
		case 'forum':
			/** ...ein board wurde übergeben */
			if (isset($_GET['board']))
			{
				/** eine thread_id wurde übergeben */
				if (isset($_GET['thread_id']) && is_numeric($_GET['thread_id']))
				{
					/** RSS Feed für einen einzelnen Thread */
					$smarty->assign('feedtitle', remove_html(Comment::getLinkThread($_GET['board'], $_GET['thread_id']) . PAGETITLE_SUFFIX) );
					$smarty->assign('feedlink', $feedURLbase . '&amp;amp;type=forum&amp;amp;board=' . $_GET['board'] . '&amp;amp;thread_id=' . $_GET['thread_id']);
					$smarty->assign('feeditems', Forum::printRSS($_GET['board'], (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 ? $_SESSION['user_id'] : null), $_GET['thread_id']));

				/** keine thread_id vorhanden */
				} else {
					/**
					 * RSS Feed für ein ganzes Board
					 * @TODO Fix "unknown feed" (broken RSS-feed) für Gallery-Comments: ?layout=rss&type=forum&board=i
					 */
					$smarty->assign('feedtitle', remove_html(Forum::getBoardTitle($_GET['board']) . PAGETITLE_SUFFIX) );
					$smarty->assign('feedlink', $feedURLbase . '&amp;amp;type=forum&amp;amp;board=' . $_GET['board']);
					$smarty->assign('feeditems', Forum::printRSS($_GET['board'], (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 ? $_SESSION['user_id'] : null)));
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
	/** Fallback for missing Template-ID */
	if (empty($_GET['word']) && empty($_GET['tpl']))
	{
		// If no Templates (yet) in Database, use 0 instead of 23...
		$_GET['tpl'] = ($db->num($db->query('SELECT id FROM templates WHERE id=23 LIMIT 1', __FILE__, __LINE__)) === 1 ? SMARTY_DEFAULT_TPL : 0);
	}

	/** Load Template data */
	if ($_GET['tpl'] !== 0)
	{
		if (isset($_GET['word']) && is_string($_GET['word'])) {
			$tplWord = (string)strip_tags(filter_var(trim($_GET['word']), FILTER_SANITIZE_STRING));
			if (false !== $tplWord && !empty($tplWord)) $queryWhere = 'word="'.$tplWord.'"';
		} else {
			$tplId = (int)strip_tags(filter_var(trim($_GET['tpl']), FILTER_SANITIZE_NUMBER_INT));
			if (false !== $tplId && !empty($tplId)) $queryWhere = 'id="'.$tplId.'"';
		}
		// FIXME change this to use Smarty:: Function!
		if (!empty($queryWhere)) $e = $db->query('SELECT id, title, word, LENGTH(tpl) size, owner, update_user, page_title,
										UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights,
										write_rights, force_compile, border, sidebar_tpl, allow_comments FROM templates WHERE '.$queryWhere, __FILE__, __LINE__, '$_TPLROOT');

		/**
		 * No Template found (404)
		 */
		if (empty($queryWhere) || empty($db->num($e)) || $e === false)
		{
			if (isset($tplId)) $_TPLROOT['id'] = $tplId;
			if (isset($tplWord)) $_TPLROOT['word'] = $tplWord;
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
			if (!empty($_GET['word'])) $_GET['tpl'] = $_TPLROOT['id'];
			if (!empty($_TPLROOT['title']) && $_TPLROOT['title'] !== null) $_TPLROOT['page_title'] = $_TPLROOT['title']; // HTML Page Title
			if (!empty($_TPLROOT['word']) && $_TPLROOT['word'] !== null) $_TPLROOT['page_link'] = '/page/'.$_TPLROOT['word']; // Canonical URL
			else $_TPLROOT['page_link'] = '/tpl/'.$_TPLROOT['id'];

			/** Events special... */
			if ($_TPLROOT['id'] == 158) {
				if (!empty($_GET['event_id'])) {
					$_TPLROOT['page_title'] = Events::getEventName($_GET['event_id']);
					$_TPLROOT['page_link'] = Events::getEventLink($_GET['event_id']);
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
			if ( isset($_GET['tpleditor']) && $_GET['tpleditor'] == 1) {
				$_TPLROOT['page_title'] = ($_GET['tplupd'] === 'new' ? 'Neues Template erstellen' : 'Template «'.$_TPLROOT['page_title'].'» bearbeiten');
				$_TPLROOT['sidebar_tpl'] = null; // Clear an assigned Sidebar
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
