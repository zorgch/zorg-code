<?php
/**
 * Event Actions
 *
 * @package zorg\Events
 */

/**
 * File includes
 * @include config.inc.php
 * @include main.inc.php Includes the Main Zorg Configs and Methods
 * @include events.inc.php Includes the Event Class and Methods
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'/../includes/main.inc.php';
require_once INCLUDES_DIR.'events.inc.php';

/** Validate $_GET & $_POST variables */
$error = NULL;
if (isset($_POST['url'])) $redirect_url = preg_replace('/([?&])error=[^&]+(&|$)/', '$1', base64url_decode($_POST['url'])); // preg_replace = entfernt $error Param
if (isset($_GET['url'])) $redirect_url = preg_replace('/([?&])error=[^&]+(&|$)/', '$1', base64url_decode($_GET['url'])); // preg_replace = entfernt $error Param
if (empty($redirect_url) || !isset($redirect_url)) $redirect_url = '/events'; // /events = Events page, tpl=158 (Fallback)

/** Validate & escape event fields for new or edit an event */
if ( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] >= 0) $eventId = $_POST['id'];
if ( isset($_POST['name']) && !empty($_POST['name'])) $eventName = sanitize_userinput($_POST['name']);
if ( !empty($_POST['location'])) $eventLocation = sanitize_userinput($_POST['location']);
if ( !empty($_POST['link'])) $eventLink = (filter_var($_POST['link'], FILTER_VALIDATE_URL)===false?(filter_var(SITE_PROTOCOL.$_POST['link'], FILTER_VALIDATE_URL)!==false?SITE_PROTOCOL.$_POST['link']:$error='Ungültiger Event-Link'):$_POST['link']);
if ( !empty($_POST['review_url'])) $eventReviewlink = (filter_var($_POST['review_url'], FILTER_VALIDATE_URL)===false?(filter_var(SITE_PROTOCOL.$_POST['review_url'], FILTER_VALIDATE_URL)!==false?SITE_PROTOCOL.$_POST['review_url']:$error='Ungültige Review-URL'):$_POST['review_url']);
if ( !empty($_POST['description'])) $eventDescription = htmlspecialchars_decode($_POST['description'], ENT_COMPAT | ENT_SUBSTITUTE);
if ( isset($_POST['gallery_id']) && is_numeric($_POST['gallery_id']) && $_POST['gallery_id'] >= 0) $eventGallery = $_POST['gallery_id'];
if ( isset($_GET['join']) && is_numeric($_GET['join']) && $_GET['join'] >= 0) $eventJoinId = $_GET['join'];
if ( isset($_GET['unjoin']) && is_numeric($_GET['unjoin']) && $_GET['unjoin'] >= 0) $eventUnjoinId = $_GET['unjoin'];


switch (true)
{
	/** Validation Error */
	case (!empty($error)):
		/** If $error break switch() instantly */
		zorgDebugger::me()->warn('Validation Error: %s%s', [$error]);
		break;


	/** Add new Event */
	case ((isset($_POST['action']) && $_POST['action'] === 'new')):
		zorgDebugger::me()->debug('Adding new Event: %s', [$eventName]);
		$startdate = sprintf('%s-%s-%s %s:00', $_POST['startYear'], $_POST['startMonth'], $_POST['startDay'], $_POST['startHour']);
		$enddate = sprintf('%s-%s-%s %s:00', $_POST['endYear'], $_POST['endMonth'], $_POST['endDay'], $_POST['endHour']);
		$values = [
			'name' => $eventName,
			'location' => $eventLocation,
			'link' => $eventLink,
			'description' => $eventDescription,
			'startdate' => $startdate,
			'enddate' => $enddate,
			'gallery_id' => $eventGallery,
			'reportedby_id' => $user->id,
			'reportedon_date' => timestamp(true),
			'review_url' => $eventReviewlink
		];
		$idNewEvent = $db->insert('events', $values, __FILE__, __LINE__, 'INSERT INTO events');

		/** Error */
		if (empty($idNewEvent))
		{
			$error = 'Error: Event konnte nicht gespeichert werden!';
		}
		/** Success */
		else {
			$redirect_url .= '&event_id='.$idNewEvent;

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, 'hat den Event <a href="'.$redirect_url.'">'.$eventName.'</a> erstellt.<br/><br/>', 'ev');
		}
		break;


	/** Save updated Event details */
	case ((isset($_POST['action']) && $_POST['action'] === 'edit')):
		zorgDebugger::me()->debug('Update existing Event: %d «%s»', [$eventId, $eventName]);

		$newStartdate = sprintf('%s-%s-%s %s:00', $_POST['startYear'], $_POST['startMonth'], $_POST['startDay'], $_POST['startHour']);
		$newEnddate = sprintf('%s-%s-%s %s:00', $_POST['endYear'], $_POST['endMonth'], $_POST['endDay'], $_POST['endHour']);
		$sql = 'UPDATE events
			 	SET
					name = "'.$eventName.'"
					, location = "'.$eventLocation.'"
					, link = "'.$eventLink.'"
					, description = "'.$eventDescription.'"
					, startdate = "'.$newStartdate.'"
			 		, enddate = "'.$newEnddate.'"
			 		, gallery_id = '.$eventGallery.'
			 		, review_url = "'.$eventReviewlink.'"
				WHERE id = '.$eventId
				;
		// TODO use $db->update() Method
		$result = $db->query($sql, __FILE__, __LINE__, 'edit');
		if ($result === false) $error = 'Error updating Event ID "' . $eventId . '"';

		break;


	/** Join User to Event */
	case (isset($eventJoinId) && is_numeric($eventJoinId)):
		zorgDebugger::me()->debug('User joins Event: %d', [$eventJoinId]);
		$redirect_url .= '&event_id='.$eventJoinId;

		$insertValues = ['user_id' => $user->id, 'event_id' => $eventJoinId];
		if ($db->insert('events_to_user', $insertValues, __FILE__, __LINE__) === false) {
			$error = 'Cannot join Event ID ' . $eventJoinId;
		} else {
			Activities::addActivity($user->id, 0, 'nimmt an <a href="'.$redirect_url.'">'.Events::getEventName($eventJoinId).'</a> teil.', 'ev');
		}

		break;


	/** Unjoin User from Event */
	case (isset($eventUnjoinId) && is_numeric($eventUnjoinId)):
		zorgDebugger::me()->debug('User unjoins Event: %d', [$eventUnjoinId]);
		$redirect_url .= '&event_id='.$eventUnjoinId;

		$sql = 'DELETE FROM events_to_user WHERE user_id=? AND event_id=?';
		if (!$db->query($sql,__FILE__, __LINE__, 'Event Unjoin', [$user->id, $eventUnjoinId])) $error = 'Cannot unjoin Event ID ' . $eventUnjoinId;

		break;


	/** Post Event to Twitter */
	case ((isset($_POST['action']) && $_POST['action'] === 'tweet')):
		zorgDebugger::me()->debug('Tweet Event: %s', [$redirect_url]);

		/**
		 * Load Twitter Class & Grab the Twitter API Keys
		 *
		 * @include twitter.class.php Include Twitter API PHP-Class and Methods
		 * @see Twitter::send()
		 */
		require_once INCLUDES_DIR.'twitter-php/twitter.class.php';
		$twitterApiKey = ['key' => $_ENV['TWITTER_API_KEY']
						 ,'secret' => $_ENV['TWITTER_API_SECRET']
						 ,'token' => $_ENV['TWITTER_API_TOKEN']
						 ,'tokensecret' => $_ENV['TWITTER_API_TOKENSECRET']
						 ,'callback' => $_ENV['TWITTER_API_CALLBACK_URL']
						];
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Twitter API Keys found: %s', __FILE__, __LINE__, print_r($twitterApiKey, true)));
		if (!empty($twitterApiKey['key']) && !empty($twitterApiKey['secret']) && !empty($twitterApiKey['token']) && !empty($twitterApiKey['tokensecret']))
		{
			/** Instantiate new Twitter Class */
			try {
				$twitter = new Twitter($twitterApiKey['key'], $twitterApiKey['secret'], $twitterApiKey['token'], $twitterApiKey['tokensecret']);
			} catch(Exception $e) {
				error_log(sprintf('[ERROR] Twitter API: could not instantiate new Twitter()-Class Object => %s', __FILE__, __LINE__, $e->getMessage()));
				$error = 'Twitter API: new Twitter() ERROR';
				break;
			}

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

				$sql = 'UPDATE events SET tweet = "'.$tweet->id_str.'" WHERE id = '.$eventId;
				if (!$db->query($sql,__FILE__, __LINE__)) $error = 'Cannot update Tweet-Status for Event ID ' . $eventId;

			} else {
				$error = 'Twitter API: $twitter->send() ERROR';
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> %s', __FILE__, __LINE__, $error));
			}

		} else {
			$errormsg = 'Twitter API Keys: ERROR - file missing';
			$error = $errormsg;
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $errormsg: %s', __FILE__, __LINE__, $errormsg));
		}
		break;

}

/** Redirect request */
$goToUrl = $redirect_url . ( !empty($error) ? '&error='.rawurlencode($error) : '');
zorgDebugger::me()->debug('Redirecting to %s', [$goToUrl]);
header('Location: ' . $goToUrl );
exit;
