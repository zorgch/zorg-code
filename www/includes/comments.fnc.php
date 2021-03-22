<?php
/**
 * Comment Template-Resouce options
 */

/**
 * File includes
 * @include smarty.inc.php required
 * @include forum.inc.php required
 * @include usersystem.inc.php required
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * Register Smarty-Template functions & variables
 */
$smarty->register_function("comment_colorfade", "smarty_comment_colorfade");
$smarty->register_function("comment_get_link", "smarty_comment_get_link");
$smarty->register_function("base64_encode", "smarty_base64_encode");
$smarty->register_function("comment_extend_depth", "smarty_comment_extend_depth");
$smarty->register_function("comment_remove_depth", "smarty_comment_remove_depth");
$smarty->assign("comments_unread", array());
$smarty->assign("comments_subscribed", array());
$smarty->register_function("comment_mark_read", "smarty_comment_mark_read");

/**
 * Thread-Comment Color-Fade an $smarty übergeben
 *
 * @uses Forum::colorfade()
 * @var $color
 * @var $smarty
 */
function smarty_comment_colorfade ($params) {
	return Forum::colorfade($params['depth'], $params['color']);
}

function smarty_comment_get_link ($params) {
	return Comment::getLink($params['board'], $params['parent_id'], $params['id'], $params['thread_id']);
}

function smarty_base64_encode ($params) {
	return base64_encode($params['text']);
}

function smarty_comment_extend_depth ($params) {
	global $smarty;	
	
	if (isset($params['depth'])) $depth = $params['depth'];
	else $depth = array();	
	
	
	if ($params['childposts'] > $params['rcount']) {
      array_push($depth, "vertline");
    } else {
      array_push($depth, "space");
    }
    $smarty->assign("hdepth", $depth);
    
    return "";
}

function smarty_comment_remove_depth ($params) {
	global $smarty;
	
	$depth = $params['depth'];
	
	array_pop($depth);
	$smarty->assign("hdepth", $depth);
	
	return "";
}

function smarty_comment_mark_read ($params) {
	return Comment::markasread($params['comment_id'], $params['user_id']);
}

function comment_read_permission ($comment_id) {
	return true;
}
