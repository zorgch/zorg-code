<?
	include_once('../test/php_profiler/profiler.inc');
   $prof = new Profiler( true ); // Output the profile information but no trace

	$prof->startTimer( "initialise" );
	$prof->stopTimer( "initialise" );




	$prof->startTimer( "main.php: require main.inc.php" );
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
	$prof->stopTimer( "main.php: require main.inc.php" );

	$prof->startTimer( "main.php: include smarty.php" );
	include($_SERVER['DOCUMENT_ROOT'].'/smarty.php');
	$prof->stopTimer( "main.php: include smarty.php" );

	/*$prof->startTimer( "main.php: include comments.res.php" );
	include($_SERVER['DOCUMENT_ROOT'].'/includes/comments.res.php');
	$prof->stopTimer( "main.php: include smarty.php" );*/



   global $smarty, $db, $user, $_TPLROOT;


   // assign's für top-site
   if ($_GET['word']) $where = "word='$_GET[word]'";
   else $where = "id='$_GET[tpl]'";

   $prof->startTimer( "query: templates" );
   $e = $db->query("SELECT id, packages, title, word, LENGTH(tpl) size, owner, update_user, page_title,
                       UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights,
                       write_rights, force_compile, border FROM templates WHERE $where", __FILE__, __LINE__);
   $prof->stopTimer( "query: templates" );

   $prof->startTimer( "fetch: templates" );
   $d = $db->fetch($e);
   $prof->stopTimer( "fetch: templates" );


  if ($_GET['word']) $_GET['tpl'] = $d['id'];

  $prof->startTimer( "smarty: assign data" );
  $smarty->assign("page_title", $d['page_title']);
  $smarty->assign("tplroot", $d);
  $prof->stopTimer( "smarty: assign data" );

  $_TPLROOT = $d;


  print('<a name="result"></a>');
  $prof->printTimers( true );
?>