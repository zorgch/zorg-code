<?php
/**
 * Chess game tester
 *
 * @package zorg\Games\Chess
 */
/**
 * File includes
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
include_once INCLUDES_DIR.'usersystem.inc.php';
include_once INCLUDES_DIR.'chess.inc.php';

$board = Chess::get_board(1);
