<?php
include_once( __DIR__ .'/../includes/tpleditor.inc.php');

global $smarty, $db, $user;

if (!$_GET['tplupd']) $_GET['tplupd'] = 'new';

$smarty->assign('tpleditor_close_url', '/actions/tpleditor_close.php?'.url_params());


$username = $user->id2user($user->id, true);

$smarty->assign('rgroupids', array(0,1,2,3));
$smarty->assign('rgroupnames', array('Alle (auch nicht eingeloggte)', 'Normale User (eingeloggt)', 'Member und Sch&ouml;ne', 'Nur '.$username));
$smarty->assign('wgroupids', array(1,2,3));
$smarty->assign('wgroupnames', array('Normale User', 'Member und Sch&ouml;ne', 'Nur '.$username));
$smarty->assign('bordertypids', array(0,1,2));
$smarty->assign('bordertypnames', array('kein Rahmen', 'Rahmen mit Footer', 'Rahmen ohne Footer'));

$access_error = '';
$vars = $smarty->get_template_vars();

if (tpleditor_access_lock($_GET['tplupd'], $access_error)) {      
  if($_GET['tplupd']!='new') {
     $e = $db->query('SELECT *, unix_timestamp(created) created, unix_timestamp(last_update) last_update FROM templates WHERE id='.$_GET['tplupd'], __FILE__, __LINE__, 'SELECT * FROM templates');
     $d = $db->fetch($e);
     if ($d && !$vars['tpleditor_frm']) {
     	$d['title'] = stripslashes($d['title']);
     	$d['tpl'] = stripslashes($d['tpl']);
     	$d['tpl'] = htmlentities($d['tpl']);
     	
     	$smarty->assign('tpleditor_frm', $d);
     }elseif (!$d) {
     	$smarty->assign('tpleditor_strongerror', 'Template "'.$_GET['tplupd'].'" not found');
     }
  
  }elseif ($_GET['tplupd']=='new' && !$vars['tpleditor_frm']) {
     // default values
     $frm = array();
     $frm['read_rights'] = 0;
     $frm['write_rights'] = 3;
     $frm['border'] = 1;
     $frm['id'] = 'new';
     $frm['tpl'] = "{menu name=zorg} <br />\n";
     $smarty->assign('tpleditor_frm', $frm);
  }
}else{   
	$smarty->assign('tpleditor_strongerror', $access_error);
}
