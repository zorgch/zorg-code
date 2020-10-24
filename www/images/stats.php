<?php
/**
 * Forum Comments Stats Generator.
 *
 * Zeigt Forum Post Stats in Form eines Diagramm Posts/Monat mittels Line Graph generator
 *
 * @author [z]cylander
 * @version 2.0
 * @since 1.0 `16.12.2004` `[z]cylander` File added
 * @since 1.5 `[z]cylander` test zweite x-Achse mit Jahreszahlen
 * @since 2.0 `07.12.2019` `IneX` [Bug #602] "0 posts/mon gehen unter" fixed, Code & styling optimizations
 *
 * @package zorg\Forum
 * @see graph.inc.php
 * @link https://zorg.ch/thread/4986?parent_id=35192#60361
 */

/** Validate passed GET-Parameters */
$user_id = (is_numeric($_GET['user_id']) && (int)$_GET['user_id'] > 0 ? (int)$_GET['user_id'] : 'all');
$group = (empty($_GET['group']) || ($_GET['group'] !== 'month' && $_GET['group'] !== 'year') ? 'year' : $_GET['group']);
$w = (is_numeric($_GET['w']) && (int)$_GET['w'] > 100 ? (int)$_GET['w'] : 600); // width: min 100px
$h = (is_numeric($_GET['h']) && (int)$_GET['h'] > 100 ? (int)$_GET['h'] : 300); // height: min 100px

/**
 * File includes
 * @include config.inc.php
 */
require_once dirname(__FILE__).'/../includes/config.inc.php';
require_once INCLUDES_DIR.'graph.inc.php';

/**
 * Define some vars
 */
$monthNames = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Okt", 11 => "Nov", 12 => "Dez");
$zorg1stJahr = 2001;
$startM = 0;
$startJ = 0;

/**
 * Initialize Graph and set basic options
 */
$img = new Line($w, $h); // Initialize Graph Class

/** Image Background */
$rr = hexdec(substr(HEADERBACKGROUNDCOLOR,1,2)); // Red
$gg = hexdec(substr(HEADERBACKGROUNDCOLOR,3,2)); // Blue
$bb = hexdec(substr(HEADERBACKGROUNDCOLOR,5,2)); // Green
imagecolordeallocate($img->image,$img->bgCol);
$img->bgCol = imagecolorallocate($img->image,$rr,$gg,$bb);

/** Titlebar Background */
$rr = hexdec(substr(LINKCOLOR,1,2)); // Red
$gg = hexdec(substr(LINKCOLOR,3,2)); // Blue
$bb = hexdec(substr(LINKCOLOR,5,2)); // Green
//imagecolordeallocate($img->image,$img->titleCol);
$img->titleCol = imagecolorallocate($img->image,$rr,$gg,$bb); // Set Title Font color

/** Axis color */
$rr = hexdec(substr(OWNCOMMENTCOLOR,1,2)); // Red
$gg = hexdec(substr(OWNCOMMENTCOLOR,3,2)); // Blue
$bb = hexdec(substr(OWNCOMMENTCOLOR,5,2)); // Green
$img->SetAxesColor($rr,$gg,$bb);

/** Data Bar color */
$rr = hexdec(substr(LINKCOLOR,1,2)); // Red
$gg = hexdec(substr(LINKCOLOR,3,2)); // Blue
$bb = hexdec(substr(LINKCOLOR,5,2)); // Green
$img->barCol[0] = imagecolorallocate($img->image,$rr,$gg,$bb);

/** Graph Title */
$img->SetTitle(sprintf('Posts/%s - %s', $group, ($user_id === 'all' ? 'Forum Total' : $user->id2user($user_id, true))));

switch ($group)
{
	/**
	 * Show Graph by YEAR
	 */
	case 'year':
		/** Get # Comments by Year */
		$sql = 'SELECT 
					YEAR( date ) AS jahr,
					count( id ) AS num
				FROM comments 
				'.($user_id !== 'all' ? 'WHERE user_id = '.$user_id : '').' 
				GROUP BY jahr ORDER by jahr ASC';
		$result = $db->query($sql, __FILE__, __LINE__, 'Comments by Year');
		$numResult = $db->num($result);

		/** User haz Comments... */
		if ((int)$numResult > 0)
		{
			while($rs = $db->fetch($result))
			{
				$jahre[$rs['jahr']] = $rs['num'];
				if (empty($startJ)) $startJ = $rs['jahr'];//if (empty($startJ)) $startJ = (empty($rs['firstJahr']) ? $rs['jahr'] : $rs['firstJahr']); // Set 1st Year
			}
		/** 0 Comments... */
		} else {
			$startJ = date('Y'); // Current Year only
		}
		for ($yearRange=$startJ;$yearRange<=date('Y');$yearRange++)
		{
			$img->AddValue( $yearRange, [(array_key_exists($yearRange, $jahre) ? $jahre[$yearRange] : 0)], '' );
		}

		$img->spit('png');
		break;

	/**
	 * Show Graph by MONTH
	 */
	case 'month':
		/** Get # Comments by Month */
		$sql = 'SELECT
					YEAR( date ) AS jahr,
					MONTH( date ) AS monat,
					count( id ) AS num
				FROM comments 
				'.($user_id !== 'all' ? 'WHERE user_id = '.$user_id : '').' 
				GROUP BY jahr, monat
				ORDER by jahr ASC, monat ASC';
		$result = $db->query($sql, __FILE__, __LINE__, 'Comments by Month');
		$numResult = $db->num($result);

		/** User haz Comments... */
		if ((int)$numResult > 0)
		{
			while($rs = $db->fetch($result))
			{
				$monate[$rs['jahr']][$rs['monat']] = $rs['num'];
				if (empty($startJ)) $startJ = $rs['jahr']; // Set 1st Year
				if (empty($startM)) $startM = $rs['monat']; // Set 1st Month
			}
		/** 0 Comments... */
		} else {
			$startJ = date('Y'); // Current Year only
			$startM = 1; // Current Year only
		}
		for ($yearRange=$startJ;$yearRange<=date('Y');$yearRange++)
		{
			for ($monthRange=$startM;$monthRange<=($yearRange<date('Y') ? 12 : date('m'));$monthRange++)
			{
				$printMonth = $monthNames[$monthRange]; // leserlicher: nur noch jeder 4te Monat => ($monthRange === $startM || $monthRange % 3 == 1 ? $monthNames[$monthRange] : '')
				$monthValue = (isset($monate[$yearRange][$monthRange]) ? $monate[$yearRange][$monthRange] : 0);
				$printYear = ($monthRange === $startM || $monthRange === 1 ? $yearRange : ''); // Year only every 12 month
				$img->AddValue( $printMonth, [$monthValue], $printYear);
			}
			$startM = 1; // After first $monthRange interation set $startM = 1 (January)
		}

		$img->spit('png');
		break;

	/**
	 * Default: 404 Not found
	 */
	default:
		http_response_code(404); // Set response code 404 (Not found)
}
