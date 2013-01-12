<?php
/**
 * Logout
 * 
 * Meldet den aktuellen Benutzer ab aus mobilezorg
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage usersystem
 */

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user;

// Session destroy
unset($_SESSION['user_id']);
session_destroy();

// Cookie killen
setcookie("autologin_id",'',time()-(86400*14));
setcookie("autologin_pw",'',time()-(86400*14));

header('Location: login.php');