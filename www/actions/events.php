<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/events.inc.php');


// Validate & escape event fields
$eventName = ( isset($_POST['name']) && !empty($_POST['name']) ? escape_text($_POST['name']) : user_error('Event: invalid Name "' . $_POST['name'] . '"', E_USER_WARNING) );
$eventLocation = ( !empty($_POST['location']) ? escape_text($_POST['location']) : '' );
$eventLink = ( !empty($_POST['link']) ? escape_text(remove_html($_POST['link'])) : '' );
$eventReviewlink = ( !empty($_POST['review_url']) ? escape_text(remove_html($_POST['review_url'])) : '' );
$eventDescription = ( !empty($_POST['description']) ? escape_text($_POST['description']) : '' );
$eventGallery = ( isset($_POST['gallery_id']) && is_numeric($_POST['gallery_id']) && $_POST['gallery_id'] >= 0 ? $_POST['gallery_id'] : user_error('Event: invalid Gallery-ID "' . $_POST['gallery_id'] . '"', E_USER_WARNING) );


if($_POST['action'] == 'new') {
  $sql = 
  	"
  	INSERT INTO 
  	events
  		(name, location, link, description, startdate, enddate, gallery_id, reportedby_id, reportedon_date, review_url) 
  	VALUES 
  		(
	  		'".$eventName."'
	  		, '".$eventLocation."'
	  		, '".$eventLink."'
	  		, '".$eventDescription."'
  			, '".$_POST['startYear']."-".$_POST['startMonth']."-".$_POST['startDay']." ".$_POST['startHour'].":00'
  			, '".$_POST['endYear']."-".$_POST['endMonth']."-".$_POST['endDay']." ".$_POST['endHour'].":00'
  			, ".$eventGallery."
  			, ".$user->id."
  			, now()
  			, '".$eventReviewlink."'
  		)
  	"
  ;
  $db->query($sql, __FILE__, __LINE__);
  
  $idNewEvent = mysql_insert_id();
  
  // Activity Eintrag auslösen
  Activities::addActivity($user->id, 0, 'hat den Event <a href="'.base64_decode($_POST['url']).'&event_id='.$idNewEvent.'">'.$_POST['name'].'</a> erstellt.<br/><br/>', 'ev');
  
  header('Location: '.base64_decode($_POST['url']).'&event_id='.$idNewEvent);
  exit;
}

else if($_POST['action'] == 'edit' ) {
	$sql =
	 "
		UPDATE `events` 
	 	SET 
			name = '".$eventName."'
			, location = '".$eventLocation."'
			, link = '".$eventLink."'
			, description = '".$eventDescription."'
			, startdate = '".$_POST['startYear']."-".$_POST['startMonth']."-".$_POST['startDay']." ".$_POST['startHour'].":00'
	 		, enddate = '".$_POST['endYear']."-".$_POST['endMonth']."-".$_POST['endDay']." ".$_POST['endHour'].":00'
	 		, gallery_id = ".$eventGallery."
	 		, review_url = '".$eventReviewlink."'
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
	Activities::addActivity($user->id, 0, 'nimmt an <a href="'.base64_decode($_GET['url']).'">'.Events::getEventName($_GET['join']).'</a> teil.', 'ev');
	
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
