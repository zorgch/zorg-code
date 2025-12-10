<?php
/**
 * Chess game tester
 *
 * @package zorg\Games\Chess
 */

/**
 * File includes
 */
require_once __DIR__.'/includes/config.inc.php';
include_once INCLUDES_DIR.'chess.inc.php';

/** Validate parameters */
$gameId = (isset($_GET['game']) ? filter_input(INPUT_GET, 'game', FILTER_VALIDATE_INT) : 1); // Default: Game #1

/** Load Chess Board */
$board = $chess->get_board($gameId);
