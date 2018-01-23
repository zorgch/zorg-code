<?php
//coded by [z]keep3r
require_once( __DIR__ .'/includes/main.inc.php');
require_once( __DIR__ .'/includes/quotes.inc.php');


// Form-Aktionen ausf?hren	
Quotes::execActions();


//echo head(40, "quotes");
$smarty->assign('tplroot', array('page_title' => 'quotes'));
$smarty->display('file:layout/head.tpl');
echo menu('main');
echo menu('quotes');

// Aenderung an Quote speichern
if($_POST['do'] == "edit_now" && $user->id != '') {
	//not implented yet.

// Quote hinzufuegen
} elseif($_POST['do'] == "add_now" && $user->id != '') {

  $sql = "INSERT INTO quotes(user_id, date, text) 
  		VALUES('$user->id','".date("YmdHis")."','$_POST[text]')";
  $db->query($sql,__FILE__, __LINE__);

  echo ("Quote hinzugef?gt");
  $_GET['do'] = "";

// Quote loeschen
} elseif($_GET['do'] == "delete_now" && $user->id != '') {
	$sql = "SELECT * FROM quotes WHERE id = $_GET[quote_id]";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);
	
	if ($rs["user_id"] == $user->id){
		
		if(Quotes::isDailyQuote($_GET['quote_id'])) {
			Quotes::newDailyQuote();
		}
		
		$sql = "DELETE FROM quotes WHERE id = $_GET[quote_id]";
		$db->query($sql,__FILE__, __LINE__);
		echo "Quote gel&ouml;scht";
		$_GET['do'] = "";
	} else {
		echo "scho recht, tschipthorre!";
	}
}

// Quotes ausgeben, ev. von speziellem User
if($_GET['do'] == "" || $_GET['do'] == "my" ) {
	
	echo("<table width=\"$mainwidth\"><tr><td align=\"center\" class=\"title\">"
    	."Quotes"
		."</td></tr></table><br>");

	if ($_GET['do'] == "my") {
		$userid = ($_GET['user_id'] != "") ? $GET['user_id'] : $user->id;
		$sql = "SELECT count(*) as anzahl FROM quotes WHERE user_id = '$userid'";
	} else {
		$sql = "SELECT count(*) as anzahl FROM quotes";
	}

	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	$total = $rs[anzahl];

	$site = $_GET['site'];
	if ($site == "" || $site < 0 ) $site=0;
	$cnt = 10; // wird hier noch auf usercount gesetzt

	if ($_GET['do'] == "my"){
		$sql = "SELECT * FROM quotes WHERE user_id = $userid ORDER BY date DESC LIMIT $site , $cnt";
	} else {
		$sql = "SELECT * FROM quotes ORDER BY date DESC LIMIT $site , $cnt";
	}
	
	$result = $db->query($sql, __FILE__, __LINE__);

	while ($rs = $db->fetch($result, __FILE__, __LINE__)) {
	
		echo Quotes::formatQuote($rs);
		
		echo "<br>";
	}

	// Ausgabe der Navigationspfeile
	echo "<table width=\"$mainwidth\"><tr><td align=\"center\" class=\"title\">";
	if ($site == 0){
		$site += 10;
		if($total % 10 == 0){
			$last = $total - 10;
		} else {
			$last = $total - ($total % 10);
		}
		echo ("<a href=$PHP_SELF?site=$site>></a></td>"
		     ."<td align=\"center\" class=\"title\">"
		     ."<a href=$PHP_SELF?site=$last>>></a>");

	} elseif ($site >= 10 && $site+$cnt < $total ) {

		$site -= 10;
		echo ("<a href=$PHP_SELF><<</a></td>"
		     ."<td align=\"center\" class=\"title\">"
		     ."<a href=$PHP_SELF?site=$site><</a></td>"
		     ."<td align=\"center\" class=\"title\">");

		$site_next = $site + $cnt + 10;
		echo " $site - $site_next </td>";

		$site += 20;
		if($total % 10 == 0){
			$last = $total - 10;
		} else {
			$last = $total - ($total % 10);
		}

		echo ("<td align=\"center\" class=\"title\">"
		     ."<a href=$PHP_SELF?site=$site>></a></td>"
		     ."<td align=\"center\" class=\"title\">"
		     ."<a href=$PHP_SELF?site=$last>>></a>");

	} elseif ($site+$cnt >= $total) {
			$site -= 10;

	  echo ("<a href=$PHP_SELF?site=$site><</a></td>"
		   ."<td align=\"center\" class=\"title\">"
		   ."<a href=$PHP_SELF><<</a>");
	}

	echo ("</td></tr></table>");

// Quote hinzuf?gen
} elseif($_GET['do'] == "add" && $user->id != '') {

 echo(
  	 "<form action='$_SERVER[PHP_SELF]' method='post' enctype='multipart/form-data'>"
  	.'<input type="hidden" name="do" value="add_now">'

    ."<table width=\"$mainwidth\"><tr><td align=\"left\" class=\"title\">"
    ."Add Quote"
    ."</td></tr></table>"
    ."<br/>"
    ."<table cellpadding=\"1\" cellspacing=\"1\" width=\"500\" class=\"border\" align=\"center\">"
    ."<tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Text:"
   	."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    ."<textarea class='text' type=\"text\" name=\"text\" cols=\"80\" rows=\"10\">"
    ."</textarea>"
    ."</td></tr></table>"
    ."<input type='submit' class='button' name='send' value='speichern'>"
    ."</form>");
    	 
// Quote wirklich loeschen?
} elseif($_GET['do'] == "delete" && $user->id != '') {
	$sql = "SELECT * FROM quotes where id = $_GET[quote_id]";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	echo ("Willst du den Quote wirklich l&ouml;schen?<br>"
	     ."<a href=$PHP_SELF?do=delete_now&quote_id=$rs[id]>ja</a>"
	     ." / "
	     ."<a href=$PHP_SELF?site=$_GET[site]>nein</a>");
	     
}
//echo foot(52);
$smarty->display('file:layout/footer.tpl');
