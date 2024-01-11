<?php
/**
 * Polls Packages
 *
 * Holt und übergibt Polls an Smarty
 *
 * @version 1.0
 * @since 1.0 `11.01.2024` `IneX` Package added
 *
 * @package		zorg\Polls
 */

/**
 * @global object $polls Globales Class-Object mit allen Polls-Methoden
 */
global $polls;

//$polls = new Polls(); --> Instantiated in poll.inc.php
$smarty->assign('polls', $polls->getAll());
