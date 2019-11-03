<?php
/**
 * GO Board
 * 
 * ...
 * ...
 * ...
 *
 * @author [z]bert, [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @package zorg\Games\Go
 * 
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
 */

/**
 * File Includes
 * @include go_game.inc.php
 */
require_once( __DIR__ . '/../includes/config.inc.php');
require_once( __DIR__ . '/../includes/go_game.inc.php');

$gameid = $_GET['game'];
if (!is_numeric($gameid)) user_error(t('error-game-invalid', 'global', $gameid));
$e = $db->query(
		'SELECT *
		FROM go_games g
		WHERE g.id = '.$gameid, __FILE__, __LINE__, 'SELECT FROM go_games');
$game = $db->fetch($e);

if (!$game){
    user_error(t('error-game-invalid', 'global', $gameid));
    return;
}

$size = $game['size'];
$im = draw_go_base($size);
draw_grid($im, $size);
draw_stardots($im, $size);
$board = $game['data'];
$board = str_split($board, 1);
if ($user->id == $game['pl1'] && $game['pl1luck'] == 0 || $user->id == $game['pl2'] && $game['pl2luck'] == 0) $luck = false;
else $luck = true;
	    
for ($i = 0; $i < $size; $i++) for ($j = 0; $j < $size; $j++){
    $stone = $board[$i + $j*$size];
    if ($stone == '1')
      draw_go_stone($im, $i, $j, 1, $luck);
    else if ($stone == '2')
      draw_go_stone($im, $i, $j, 2, $luck);
    else if ($stone == '3')
      draw_go_stone($im, $i, $j, 3, $luck);
    else if ($stone == '4')
      draw_go_stone($im, $i, $j, 4, $luck);
}

if ($game['state'] == 'running'){
    draw_go_last($im, $size, $game['last1']);
    draw_go_last($im, $size, $game['last2']);
}

draw_go_players($im, $game);

header("Content-Type: image/png");
imagepng($im);
