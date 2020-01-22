<?php
/**
 * This is where it all starts.
 * The Index. A digital zorg. We tried to picture clusters of information as they traveled through to your computer.
 * Bits, bytes. With the network like freeways. We kept dreaming of a zorg we thought we'd never see.
 * And then, one day... we got it running.
 */
/**
 * File Includes
 */
require_once( dirname(__FILE__) . '/includes/main.inc.php');

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
	$routeValue = $_GET[$routeSwitch];
	switch ($routeSwitch) {

		/** Route: /user/[user-id|username] */
		case 'username':
			$getUserId = ( is_numeric($routeValue) ? $routeValue : $user->user2id($routeValue) );
			if (!empty($getUserId)) {
				$_GET['user_id'] = $getUserId;
				include('profil.php');
				die();
			}
			break;

		/** Route: /bug/[bug-id] */
		case 'bug':
			if ( is_numeric($routeValue) ) $getBugId = $routeValue;
			if (!empty($getBugId)) {
				$_GET['bug_id'] = $getBugId;
				include('bugtracker.php');
				die();
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
if ($_GET['layout'] != 'rss' && ((!isset($_GET['tpl']) && !isset($_GET['word'])) || (empty($_GET['tpl']) && empty($_GET['word'])) || ($_GET['tpl'] <= 0 || is_numeric($_GET['word'])))) $_GET['tpl'] = 23;


/**
 * RSS Feeds
 * @see Forum::printRSS()
 */
if ($_GET['layout'] == 'rss' && $_GET['type'] != '') {
	$smarty->assign('feeddesc', SITE_HOSTNAME . ' RSS Feed');
	$smarty->assign('feedlang', 'de-DE');
	$smarty->assign('feeddate', date('D, d M Y H:i:s').' GMT');

	switch ($_GET['type']) {
		/** Forum RSS */
		case 'forum':
			/** ...ein board wurde übergeben */
			if ($_GET['board'] != '') {

				/** eine thread_id wurde übergeben */
				if ($_GET['thread_id'] != '') {
					/** RSS Feed für einen einzelnen Thread */
					$smarty->assign('feedtitle', remove_html(Comment::getLinkThread($_GET['board'], $_GET['thread_id']) . PAGETITLE_SUFFIX) );
					$smarty->assign('feedlink', RSS_URL . '&amp;amp;type=forum&amp;amp;board=' . $_GET['board'] . '&amp;amp;thread_id=' . $_GET['thread_id']);
					$smarty->assign('feeditems', Forum::printRSS($_GET['board'], $_SESSION['user_id'], $_GET['thread_id']));

				/** keine thread_id vorhanden */
				} else {
					/**
					 * RSS Feed für ein ganzes Board
					 * @TODO Fix "unknown feed" (broken RSS-feed) für Gallery-Comments: ?layout=rss&type=forum&board=i
					 */
					$smarty->assign('feedtitle', remove_html(Forum::getBoardTitle($_GET['board']) . PAGETITLE_SUFFIX) );
					$smarty->assign('feedlink', RSS_URL . '&amp;amp;type=forum&amp;amp;board=' . $_GET['board']);
					$smarty->assign('feeditems', Forum::printRSS($_GET['board'], $_SESSION['user_id']));
				}

			/** kein board vorhanden */
			} else {
				/** genereller Forum RSS Feed */
				$smarty->assign('feedtitle', 'Forum RSS' . PAGETITLE_SUFFIX);
				$smarty->assign('feedlink', RSS_URL . '&amp;amp;type=forum' . $_GET['board']);
				$smarty->assign('feeditems', Forum::printRSS(null, $_SESSION['user_id']));
			}
			break;

		/** Activities RSS */
		case 'activities':
			$smarty->assign('feedtitle', remove_html('Activities' . PAGETITLE_SUFFIX));
			$smarty->assign('feedlink', RSS_URL . '&amp;amp;type=activities');
			$smarty->assign('feeditems', Activities::getActivitiesRSS(25));
			break;
	}

	/** Text-codierung & XML-Encoding an den header senden */
	header("Content-Type: text/xml; charset=UTF-8");
	$smarty->display('file:rss.tpl');

/**
 * Regular Page
 * @see SMARTY_DEFAULT_TPL, SMARTY_404PAGE_TPL
 */
} else {
	/** Fallback for missing Template-ID */
	if (empty($_GET['word']) && empty($_GET['tpl'])) $_GET['tpl'] = SMARTY_DEFAULT_TPL;

	/** Load Template data */
	// FIXME change this to use Smarty:: Function!
	try {
		$where = ( isset($_GET['word']) && $_GET['word'] ? 'word="'.$_GET['word'].'"' : 'id='.$_GET['tpl'] );
		$e = $db->query('SELECT id, title, word, LENGTH(tpl) size, owner, update_user, page_title,
						UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights,
						write_rights, force_compile, border, sidebar_tpl, allow_comments FROM templates WHERE '.$where, __FILE__, __LINE__, '$_TPLROOT');

		/** No Template found (404) */
		if (empty($db->num($e)) || $db->num($e) === false)
		{
			if (isset($_GET['tpl'])) $_TPLROOT['id'] = (string)$_GET['tpl'];
			if (isset($_GET['word'])) $_TPLROOT['word'] = (string)$_GET['word'];
			$_TPLROOT['page_title'] = sprintf('Page «%s» not found', (isset($_GET['word']) ? (string)$_GET['word'] : $_GET['tpl']));
			$_TPLROOT['page_link'] = (isset($_GET['word']) ? '/page/'.(string)$_GET['word'] : '/tpl/'.$_GET['tpl']);
			$_TPLROOT['title'] = $_TPLROOT['page_title'];

			/** Display 404 page */
			http_response_code(404); // Set response code 404 (not found)
			$smarty->assign('tplroot', $_TPLROOT);
			$smarty->display(SMARTY_404PAGE_TPL);
			exit;
		}

		/** Template found */
		else {
			$_TPLROOT = $db->fetch($e);

			/** Load required packages for the current template */
			load_packages($_TPLROOT['id']);

			/** Load Template menus */
			$tpl_menus = load_navigation($_TPLROOT['id']);
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

		$_TPLROOT['page_title'] = htmlentities($_TPLROOT['page_title'], ENT_QUOTES); // To prevent breaking HTML syntax, convert all special characters to HTML entities
		$smarty->assign('tplroot', $_TPLROOT);
	}
	catch (Exception $e) {
		http_response_code(500); // Set response code 500 (internal server error)
		user_error($e->getMessage(), E_USER_ERROR);
	}

	/**
	 * SQL Query Tracker
	 */
	if ($user->sql_tracker) {
	   $_SESSION['noquerys'] = $db->noquerys;
	   $_SESSION['noquerytracks'] = $db->noquerytracks;
	   $_SESSION['query_track'] = $db->query_track;
	   $_SESSION['query_request'] = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	} else {
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
