<?php
/**
 * Chess game tester
 * @package zorg\Games\Chess
 */

/** File includes */
require_once(__DIR__.'/includes/main.inc.php');
include_once(__DIR__.'/includes/usersystem.inc.php');
include_once(__DIR__.'/includes/chess.inc.php');

$board = Chess::get_board(1);
