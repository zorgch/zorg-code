<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/events.inc.php');


if($_POST['action'] == 'new') {
  $sql = 
  	"
  	INSERT INTO 
  	events
  		(name, location, link, description, startdate, enddate, gallery_id, reportedby_id, reportedon_date, review_url) 
  	VALUES 
  		(
	  		'".$_POST[name]."'
	  		, '".$_POST[location]."'
	  		, '".$_POST[link]."'
	  		, '".$_POST[description]."'
  			, '".$_POST['startYear']."-".$_POST['startMonth']."-".$_POST['startDay']." ".$_POST['startHour'].":00'
  			, '".$_POST['endYear']."-".$_POST['endMonth']."-".$_POST['endDay']." ".$_POST['endHour'].":00'
  			, ".$_POST['gallery_id']."
  			, ".$user->id."
  			, now()
  			, '".$_POST['review_url']."'
  		)
  	"
  ;
  $db->query($sql, __FILE__, __LINE__);
  
  $idNewEvent = mysql_insert_id();
  
  // Activity Eintrag auslösen
  Activities::addActivity($user->id, 0, 'hat den Event <a href="'.base64_decode($_POST['url']).'&event_id='.$idNewEvent.'">'.$_POST['name'].'</a> erstellt.<br/><br/>');
  
  header('Location: '.base64_decode($_POST['url']).'&event_id='.$idNewEvent);
  exit;
}

else if($_POST['action'] == 'edit' ) {
	$sql =
	 "
		UPDATE `events` 
	 	SET 
			name = '".$_POST['name']."'
			, location = '".$_POST['location']."'
			, description = '".$_POST['description']."'
			, link = '".$_POST['link']."'
			, startdate = '".$_POST['startYear']."-".$_POST['startMonth']."-".$_POST['startDay']." ".$_POST['startHour'].":00'
	 		, enddate = '".$_POST['endYear']."-".$_POST['endMonth']."-".$_POST['endDay']." ".$_POST['endHour'].":00'
	 		, gallery_id = ".$_POST['gallery_id']."
	 		, review_url = '".$_POST['review_url']."'
		WHERE id = ".$_POST['id']."
	"
	;
	$db->query($sql, __FILE__, __LINE__);
	header('Location: '.base64_decode($_POST['url']).'&event_id='.$_POST['id']);
	exit;
}


else if (isset($_GET['join']) && is_numeric($_GET['join'])) { // User besucht Event
	$sql = "Insert into events_to_user values($user->id, ".$_GET['join'].")";
	$db->query($sql,__FILE__, __LINE__);
	
	// Activity Eintrag auslösen
	//Activities::addActivity($user->id, 0, "nimmt am Event <a href=\"".base64_decode($_GET['url'])."\">".$_GET['join']."</a> teil.<br/><br/>");
	Activities::addActivity($user->id, 0, 'nimmt an <a href="'.base64_decode($_GET['url']).'">'.Events::getEventName($_GET['join']).'</a> teil.');
	
	header('Location: '.base64_decode($_GET['url']).'&event_id='.$_GET['join']);
	exit;
}


 
else if (isset($_GET['unjoin']) && is_numeric($_GET['unjoin'])) { // User besucht Event nicht mehr
	$sql = "delete from events_to_user where user_id = '".$user->id."' and event_id = ".$_GET['unjoin'];
	$db->query($sql,__FILE__, __LINE__);
	header('Location: '.base64_decode($_GET['url']).'&event_id='.$_GET['unjoin']);
	exit;
}
?>
