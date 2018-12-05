<?php
/**
 * File includes
 * @include main.inc.php Includes the Main Zorg Configs and Methods
 * @include events.inc.php Includes the Event Class and Methods
 */
require_once( __DIR__ .'/../includes/main.inc.php');
require_once( __DIR__ .'/../includes/events.inc.php');

/** Validate $_GET & $_POST variables */
$error = NULL;
if (!empty($_POST['url'])) $redirect_url = base64_decode($_POST['url']);
if (!empty($_GET['url'])) $redirect_url = base64_decode($_GET['url']);
if (empty($redirect_url) || !isset($redirect_url)) $redirect_url = '/?tpl=158'; // tpl=158 = Events page (Fallback)

/** Validate & escape event fields for new or edit an event */
/*
( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] >= 0 ? $eventId = $_POST['id'] : $error .= 'Event: invalid Event ID ' . $_POST['id'] );
( isset($_POST['name']) && !empty($_POST['name']) ? $eventName = escape_text($_POST['name']) : $error .= 'Event: invalid Name ' . $_POST['name'] );
$eventLocation = ( !empty($_POST['location']) ? escape_text($_POST['location']) : '' );
$eventLink = ( !empty($_POST['link']) ? escape_text(remove_html($_POST['link'])) : '' );
$eventReviewlink = ( !empty($_POST['review_url']) ? escape_text(remove_html($_POST['review_url'])) : '' );
$eventDescription = ( !empty($_POST['description']) ? escape_text($_POST['description']) : '' );
( isset($_POST['gallery_id']) && is_numeric($_POST['gallery_id']) && $_POST['gallery_id'] >= 0 ? $eventGallery = $_POST['gallery_id'] : $error .= 'Event: invalid Gallery-ID ' . $_POST['gallery_id'] );
( isset($_GET['join']) && is_numeric($_GET['join']) && $_GET['join'] >= 0 ? $eventJoinId = $_GET['join'] : $error .= 'Event: invalid Event ID "' . $_GET['join'] . '"' );
( isset($_GET['unjoin']) && is_numeric($_GET['unjoin']) && $_GET['unjoin'] >= 0 ? $eventUnjoinId = $_GET['unjoin'] : $error .= 'Event: invalid Event ID "' . $_GET['unjoin'] . '"' );
*/
if ( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] >= 0) $eventId = $_POST['id'];
if ( isset($_POST['name']) && !empty($_POST['name'])) $eventName = escape_text($_POST['name']);
if ( !empty($_POST['location'])) $eventLocation = escape_text($_POST['location']);
if ( !empty($_POST['link'])) $eventLink = escape_text(remove_html($_POST['link']));
if ( !empty($_POST['review_url'])) $eventReviewlink = escape_text(remove_html($_POST['review_url']));
if ( !empty($_POST['description'])) $eventDescription = escape_text($_POST['description']);
if ( isset($_POST['gallery_id']) && is_numeric($_POST['gallery_id']) && $_POST['gallery_id'] >= 0) $eventGallery = $_POST['gallery_id'];
if ( isset($_GET['join']) && is_numeric($_GET['join']) && $_GET['join'] >= 0) $eventJoinId = $_GET['join'];
if ( isset($_GET['unjoin']) && is_numeric($_GET['unjoin']) && $_GET['unjoin'] >= 0) $eventUnjoinId = $_GET['unjoin'];


switch (true)
{
/** Validation Error */
	case (!empty($error)):
		/** If $error break switch() instantly */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Error: %s', __FILE__, __LINE__, $error));
		break;


/** Add new Event */
	case ($_POST['action'] === 'new'):
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> New Event: %s', __FILE__, __LINE__, $eventName));
		try {
			$sql = 'INSERT INTO 
						events
							(name, location, link, description, startdate, enddate, gallery_id, reportedby_id, reportedon_date, review_url) 
						VALUES 
							(
								"'.$eventName.'"
								, "'.$eventLocation.'"
								, "'.$eventLink.'"
								, "'.$eventDescription.'"
								, "'.$_POST['startYear'].'-'.$_POST['startMonth'].'-'.$_POST['startDay'].' '.$_POST['startHour'].':00"
								, "'.$_POST['endYear'].'-'.$_POST['endMonth'].'-'.$_POST['endDay'].' '.$_POST['endHour'].':00"
								, '.$eventGallery.'
								, '.$user->id.'
								, now()
								, "'.$eventReviewlink.'"
							)';
			$db->query($sql, __FILE__, __LINE__);

			$idNewEvent = mysql_insert_id();
			$redirect_url .= '&event_id='.$idNewEvent;

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, 'hat den Event <a href="'.$redirect_url.'">'.$eventName.'</a> erstellt.<br/><br/>', 'ev');

		} catch (Exception $e) {
			$error = 'Error: ' . $e->getMessage();
		}
		break;


/** Save updated Event details */
	case ($_POST['action'] === 'edit'):
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Update Event: %d "%s"', __FILE__, __LINE__, $eventId, $eventName));
		try {		
			$sql = 'UPDATE events 
				 	SET 
						name = "'.$eventName.'"
						, location = "'.$eventLocation.'"
						, link = "'.$eventLink.'"
						, description = "'.$eventDescription.'"
						, startdate = "'.$_POST['startYear'].'-'.$_POST['startMonth'].'-'.$_POST['startDay'].' '.$_POST['startHour'].':00"
				 		, enddate = "'.$_POST['endYear'].'-'.$_POST['endMonth'].'-'.$_POST['endDay'].' '.$_POST['endHour'].':00"
				 		, gallery_id = '.$eventGallery.'
				 		, review_url = "'.$eventReviewlink.'"
					WHERE id = '.$eventId
					;
			if (!$db->query($sql, __FILE__, __LINE__)) $error = 'Error updating Event ID "' . $eventId . '"';

		} catch (Exception $e) {
			$error = 'Error: ' . $e->getMessage();
		}
		break;


/** Join User to Event */
	case (isset($eventJoinId) && is_numeric($eventJoinId)):
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Join Event: %d', __FILE__, __LINE__, $eventJoinId));
		$redirect_url .= '&event_id='.$eventJoinId;
		try {
			$sql = 'INSERT INTO events_to_user VALUES('.$user->id.', "'.$eventJoinId.'")"';
			if (!$db->query($sql,__FILE__, __LINE__)) $error = 'Cannot join Event ID ' . $eventJoinId;

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, 'nimmt an <a href="'.$redirect_url.'">'.Events::getEventName($eventJoinId).'</a> teil.', 'ev');
			
		} catch (Exception $e) {
			$error = 'Error: ' . $e->getMessage();
		}
		break;


/** Unjoin User from Event */
	case (isset($eventUnjoinId) && is_numeric($eventUnjoinId)):
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Unjoin Event: %d', __FILE__, __LINE__, $eventUnjoinId));
		$redirect_url .= '&event_id='.$eventUnjoinId;
		try {
			$sql = 'DELTE FOM events_to_user WHERE user_id = '.$user->id.'" AND event_id = '.$eventUnjoinId;
			if (!$db->query($sql,__FILE__, __LINE__)) $error = 'Cannot unjoin Event ID ' . $eventUnjoinId;
		} catch (Exception $e) {
			$error = 'Error: ' . $e->getMessage();
		}
		break;


/** Post Event to Twitter */
	case ($_POST['action'] === 'tweet'):
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Tweet Event: %s', __FILE__, __LINE__, $redirect_url));

		/**
		 * Load Twitter Class & Grab the Twitter API Keys
		 * @include twitter.class.php Include Twitter API PHP-Class and Methods
		 * @include twitterapis_key.inc.php Include an Array containing valid Twitter API Keys
		 * @see Twitter::send()
		 */
		require_once __DIR__ .'/../includes/twitter-php/twitter.class.php';
		$twitterApiKeysFile = __DIR__ .'/../includes/twitterapis_key.inc.php';
		if (file_exists($twitterApiKeysFile))
		{
			$twitterApiKeys = require_once($twitterApiKeysFile);
			$twitterApiKey = (DEVELOPMENT ? $twitterApiKeys['DEVELOPMENT'] : $twitterApiKeys['PRODUCTION']);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Twitter API Keys found: %s', __FILE__, __LINE__, print_r($twitterApiKeys, true)));

			/** Instantiate new Twitter Class */
			$twitter = new Twitter($twitterApiKey['key'], $twitterApiKey['secret'], $twitterApiKey['token'], $twitterApiKey['tokensecret']);

			/**
			 * Send Tweet
			 * @TODO you can add $imagePath or array of image paths as second argument
			 */
			try {
				$eventURL = SITE_URL . '/event/' . date('Y/m/d/', $_POST['date']) . $eventId;
				$tweet = $twitter->send(sprintf('[Event] %s findet am %s statt%s%s (geteilt durch %s)', Events::getEventName($eventId), datename($_POST['date']), "\n", $eventURL, $user->id2user($user->id)));
			} catch (TwitterException $e) {
				$error = 'Error: ' . $e->getMessage();
				break;
			}

			if (!empty($tweet))
			{
				/** Update Event-Entry in the Database */
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Tweet "%s" sent => https://twitter.com/%s/status/%s', __FILE__, __LINE__, $tweet->text, TWITTER_NAME, $tweet->id_str));
				try {
					$sql = 'UPDATE events SET tweet = "'.$tweet->id_str.'" WHERE id = '.$eventId;
					if (!$db->query($sql,__FILE__, __LINE__)) $error = 'Cannot update Tweet-Status for Event ID ' . $eventId;
				} catch (Exception $e) {
					$error = 'Error: ' . $e->getMessage();
				}
			} else {
				$error = 'Twitter API: $twitter->send() ERROR';
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> %s', __FILE__, __LINE__, $error));
			}

		} else {
			$errormsg = 'Twitter API Keys: ERROR - file missing';
			$error = $errormsg;
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ' . $errormsg, __FILE__, __LINE__));
		}
		break;

} // end switch()

/** Redirect request */
if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Redirecting to %s' . $redirect_url . $error, __FILE__, __LINE__));
header('Location: ' . $redirect_url . ( !empty($error) ? '&error='.rawurlencode($error) : '') );
exit;
