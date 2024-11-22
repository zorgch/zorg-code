<?php
require_once __DIR__.'/../includes/hz_game.inc.php';

/** Validate passed $_GET Parameters */
zorgDebugger::log()->debug('$_GET-Params: %s', [print_r($_GET,true)]);
$gameId = filter_input(INPUT_GET, 'game', FILTER_SANITIZE_NUMBER_INT) ?? null;
$ticketType = filter_input(INPUT_GET, 'ticket', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;;
$moveToStationNum = filter_input(INPUT_GET, 'move', FILTER_SANITIZE_NUMBER_INT) ?? null;
$doAction = filter_input(INPUT_GET, 'do', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
zorgDebugger::log()->debug('do => %s: $gameId => %d | $ticketType => %s | $stationNum => %d', [(!empty($doAction) ? $doAction : $moveToStationNum), $gameId, $ticketType, $moveToStationNum]);

/** hz actions */
if (!empty($gameId) && $user->is_loggedin())
{
	if (turn_allowed($gameId, $user->id))
	{
		/** move */
		if (!empty($ticketType) && !empty($moveToStationNum))
		{
			zorgDebugger::log()->debug('turn_move(): %d, %s, %s', [$gameId, $ticketType, $moveToStationNum]);
			turn_move($gameId, $ticketType, $moveToStationNum); // TODO add 4th Param: $user->id
		}

		/** sentinel */
		elseif ($doAction === 'sentinel')
		{
			zorgDebugger::log()->debug('turn_sentinel(): %d', [$gameId]);
			turn_sentinel($gameId);
		}

		/** stay */
		elseif ($doAction === 'stay')
		{
			zorgDebugger::log()->debug('turn_stay(): %d', [$gameId]);
			turn_stay($gameId, $user->id);
		}
	}
	header('Location: /tpl/103?game='.$gameId);
	exit;
}
else user_error('Nice try :-)', E_USER_NOTICE);
