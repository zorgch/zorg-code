<?php
require_once dirname(__FILE__).'/../includes/hz_game.inc.php';
require_once dirname(__FILE__).'/../includes/usersystem.inc.php';

/** Validate passed $_GET Parameters */
if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $_GET-Params: %s', __FILE__, __LINE__, print_r($_GET,true)));
$gameId = (isset($_GET['game']) && is_numeric($_GET['game']) && $_GET['game']>0 ? (int)strip_tags(filter_var(trim($_GET['game']), FILTER_SANITIZE_NUMBER_INT)) : null);
$ticketType = (isset($_GET['ticket']) && !is_numeric($_GET['ticket']) && !is_bool($_GET['ticket']) ? (string)strip_tags(filter_var(trim($_GET['ticket']), FILTER_SANITIZE_STRING)) : null);
$moveToStationNum = (isset($_GET['move']) && is_numeric($_GET['move']) && $_GET['move']>0 ? (int)strip_tags(filter_var(trim($_GET['move']), FILTER_SANITIZE_NUMBER_INT)) : null);
if (isset($_GET['do']) && !is_numeric($_GET['do']) && !is_bool($_GET['do'])) $doAction = (string)strip_tags(filter_var(trim($_GET['do']), FILTER_SANITIZE_STRING));
if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Sanitized-Params (%s): $gameId => %d | $ticketType => %s | $stationNum => %d', __FILE__, __LINE__, (isset($doAction) ? $doAction : 'mobe'), $gameId, $ticketType, $moveToStationNum));

/** hz actions */
if (!empty($gameId) && $user->is_loggedin())
{
	if (turn_allowed($gameId, $user->id))
	{
// 		$e = $db->query('SELECT g.*, me.station mystation FROM hz_games g
// 						JOIN hz_players me ON me.game = g.id
// 						WHERE g.id='.$gameId.' AND me.user='.$user->id,
// 						__FILE__, __LINE__, 'SELECT mystation');
// 		$game = $db->fetch($e);
//
// 		if (!$game)
// 		{
			/** move */
			if (!empty($ticketType) && !empty($moveToStationNum))
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_move(): %d, %s, %s', __FILE__, __LINE__, $gameId, $ticketType, $moveToStationNum));
				turn_move($gameId, $ticketType, $moveToStationNum);
			}

			/** sentinel */
			elseif (isset($doAction) && $doAction === 'sentinel')
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_sentinel(): %d', __FILE__, __LINE__, $gameId));
				turn_sentinel($gameId);
			}

			/** stay */
			elseif (isset($doAction) && $doAction === 'stay')
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_stay(): %d', __FILE__, __LINE__, $gameId));
				turn_stay($gameId, $user->id);
			}
		// }
	}
	header('Location: /tpl/103?game='.$gameId);
	exit;
}
else user_error('Nice try :-)', E_USER_NOTICE);
