<?
include_once('test/php_profiler/profiler.inc');
		$prof = new Profiler( true ); // Output the profile information but no trace

		$prof->startTimer( "initialise" );
		$prof->stopTimer( "initialise" );



		// ORIGINAL CODE
		$prof->startTimer( "require main.inc.php" );
		require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
		$prof->stopTimer( "require main.inc.php" );

		$prof->startTimer( "!isset tpl && !isset word { tpl = 23 }" );
		if (!isset($_GET['tpl']) && !isset($_GET['word'])) {
			$_GET['tpl'] = 23;
		}
		$prof->stopTimer( "!isset tpl && !isset word { tpl = 23 }" );

		//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/sunrise.inc.php');
		$prof->startTimer( "include_once smarty.inc.php" );
		include_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');
		$prof->stopTimer( "include_once smarty.inc.php" );

		$prof->startTimer( "display main.html" );
		$smarty->display("file:main.html");
		$prof->stopTimer( "display main.html" );
		// ORIGINAL CODE END



		$prof->printTimers( true );

?>