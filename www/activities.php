<?php
//=============================================================================
// Includes
//=============================================================================

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/activities.inc.php');

//=============================================================================
// Output
//=============================================================================


/**
  * RSS Feed der Activities
  * @author IneX
  * @date 16.03.2008
  * @desc RSS Feed für eine gewisse Anzahl von letzten Activities
  * @param $_GET['rss'] string
  */
// RSS soll angezeigt werden
if($_GET['layout'] = 'rss') {
	
		// RSS Feed ein ganzes Board
		//rss ($title, $link, $desc, $feeds) <-- layout.inc.php
		echo rss( 'Activities - zorg.ch', SITE_URL, 'Letzte Activities auf zorg.ch', Activities::getActivitiesRSS(25) );

} // end if layout = rss

?>
