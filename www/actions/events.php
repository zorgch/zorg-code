<?php
/**
 * Event Actions
 *
 * @package zorg\Events
 */

/**
 * File includes
 * @include config.inc.php
 * @include events.inc.php Includes the Event Class and Methods
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'events.inc.php';

/** Validate $_GET & $_POST variables */
$error = NULL;
if (isset($_POST['url'])) $redirect_url = preg_replace('/([?&])error=[^&]+(&|$)/', '$1', base64url_decode($_POST['url'])); // preg_replace = entfernt $error Param
if (isset($_GET['url'])) $redirect_url = preg_replace('/([?&])error=[^&]+(&|$)/', '$1', base64url_decode($_GET['url'])); // preg_replace = entfernt $error Param
if (empty($redirect_url) || !isset($redirect_url)) $redirect_url = '/events'; // /events = Events page, tpl=158 (Fallback)

/** Validate & escape event fields for new or edit an event */
if ( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] >= 0) $eventId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? null;
if ( isset($_POST['name']) && !empty($_POST['name'])) $eventName = htmlspecialchars_decode(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS), ENT_COMPAT | ENT_SUBSTITUTE) ?? null;
if ( !empty($_POST['location'])) $eventLocation = sanitize_userinput($_POST['location']);
if ( !empty($_POST['link'])) $eventLink = (filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL)===false?(filter_var(SITE_PROTOCOL.$_POST['link'], FILTER_VALIDATE_URL)!==false?SITE_PROTOCOL.$_POST['link']:$error='Ungültiger Event-Link'):$_POST['link']);
if ( !empty($_POST['review_url'])) $eventReviewlink = (filter_input(INPUT_POST, 'review_url', FILTER_VALIDATE_URL)===false?(filter_var(SITE_PROTOCOL.$_POST['review_url'], FILTER_VALIDATE_URL)!==false?SITE_PROTOCOL.$_POST['review_url']:$error='Ungültige Review-URL'):$_POST['review_url']);
if ( !empty($_POST['description'])) $eventDescription = htmlspecialchars_decode($_POST['description'], ENT_COMPAT | ENT_SUBSTITUTE);
if ( isset($_POST['gallery_id']) && is_numeric($_POST['gallery_id']) && $_POST['gallery_id'] >= 0) $eventGallery = $_POST['gallery_id'];
if ( isset($_GET['join']) && is_numeric($_GET['join']) && $_GET['join'] >= 0) $eventJoinId = filter_input(INPUT_GET, 'join', FILTER_VALIDATE_INT) ?? null;
if ( isset($_GET['unjoin']) && is_numeric($_GET['unjoin']) && $_GET['unjoin'] >= 0) $eventUnjoinId = filter_input(INPUT_GET, 'unjoin', FILTER_VALIDATE_INT) ?? null;
if ( isset($_POST['fromDate']) && !empty($_POST['fromDate']) && isset($_POST['fromTime']) && !empty($_POST['fromTime']) ) {
	$fromDate = explode('-', $_POST['fromDate']);
	$fromTime = explode(':', $_POST['fromTime']);
}
if (isset($_POST['toDate']) && !empty($_POST['toDate']) && isset($_POST['toTime']) && !empty($_POST['toTime']) ) {
	$toDate = explode('-', $_POST['toDate']);
	$toTime = explode(':', $_POST['toTime']);
}

switch (true)
{
	/** Validation Error */
	case (!empty($error)):
		/** If $error break switch() instantly */
		zorgDebugger::log()->warn('Validation Error: %s%s', [$error]);
		break;

	/** Add new Event */
	case ($_POST['action'] === 'new'):
		if (isset($fromDate) && isset($fromTime)) {
			$startdate = timestamp(true, [
				 'year' => intval($fromDate[0])
				,'month' => intval($fromDate[1])
				,'day' => intval($fromDate[2])
				,'hour' => intval($fromTime[0])
				,'minute' => intval($fromTime[1])
			]);
		}
		if (isset($toDate) && isset($toTime)) {
			$enddate = timestamp(true, [
				 'year' => intval($toDate[0])
				,'month' => intval($toDate[1])
				,'day' => intval($toDate[2])
				,'hour' => intval($toTime[0])
				,'minute' => intval($toTime[1])
			]);
		}
		/** Backwards-compatibility to old individual Date fields */
		if (!isset($startdate)) $startdate = sprintf('%s-%s-%s %s:00', $_POST['startYear'], $_POST['startMonth'], $_POST['startDay'], $_POST['startHour']);
		if (!isset($enddate)) $enddate = sprintf('%s-%s-%s %s:00', $_POST['endYear'], $_POST['endMonth'], $_POST['endDay'], $_POST['endHour']);
		zorgDebugger::log()->debug('Dates: %s --> %s', [$newStartdate, $newEnddate]);

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
		zorgDebugger::log()->debug('Adding new Event: %s', [print_r($values,true)]);
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
	case ($_POST['action'] === 'edit'):
		if (isset($fromDate) && isset($fromTime)) {
			$newStartdate = timestamp(true, [
				 'year' => intval($fromDate[0])
				,'month' => intval($fromDate[1])
				,'day' => intval($fromDate[2])
				,'hour' => intval($fromTime[0])
				,'minute' => intval($fromTime[1])
			]);
		}
		if (isset($toDate) && isset($toTime)) {
			$newEnddate = timestamp(true, [
				 'year' => intval($toDate[0])
				,'month' => intval($toDate[1])
				,'day' => intval($toDate[2])
				,'hour' => intval($toTime[0])
				,'minute' => intval($toTime[1])
			]);
		}
		if (!isset($newStartdate)) $newStartdate = sprintf('%s-%s-%s %s:00', $_POST['startYear'], $_POST['startMonth'], $_POST['startDay'], $_POST['startHour']);
		if (!isset($newEnddate)) $newEnddate = sprintf('%s-%s-%s %s:00', $_POST['endYear'], $_POST['endMonth'], $_POST['endDay'], $_POST['endHour']);
		zorgDebugger::log()->debug('Dates: %s --> %s', [$newStartdate, $newEnddate]);

		$values = [];
		if (isset($eventName)) $values['name'] = $eventName;
		if (isset($eventLocation)) $values['location'] = $eventLocation;
		if (isset($eventLink)) $values['link'] = $eventLink;
		if (isset($eventDescription)) $values['description'] = $eventDescription;
		if (isset($newStartdate)) $values['startdate'] = $newStartdate;
		if (isset($newEnddate)) $values['enddate'] = $newEnddate;
		if (isset($eventGallery)) $values['gallery_id'] = $eventGallery;
		if (isset($eventReviewlink)) $values['review_url'] = $eventReviewlink;
		zorgDebugger::log()->debug('Updating Event %d details: %s', [$eventId, print_r($values,true)]);

		$result = $db->update('events', $eventId, $values, __FILE__, __LINE__, 'UPDATE events');
		if ($result === false) $error = 'Error updating Event ID "' . $eventId . '"';

		break;


	/** Join User to Event */
	case (isset($eventJoinId) && is_numeric($eventJoinId)):
		zorgDebugger::log()->debug('User joins Event: %d', [$eventJoinId]);
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
		zorgDebugger::log()->debug('User unjoins Event: %d', [$eventUnjoinId]);
		$redirect_url .= '&event_id='.$eventUnjoinId;

		$sql = 'DELETE FROM events_to_user WHERE user_id=? AND event_id=?';
		if (!$db->query($sql,__FILE__, __LINE__, 'Event Unjoin', [$user->id, $eventUnjoinId])) $error = 'Cannot unjoin Event ID ' . $eventUnjoinId;

		break;


	/** Post Event to Twitter */
	case ($_POST['action'] === 'tweet'):
		zorgDebugger::log()->debug('Tweet Event: %s', [$redirect_url]);

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
		zorgDebugger::log()->debug('Twitter API Keys found: %s', [print_r($twitterApiKey, true)]);
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
			 * // FIXME you can add $imagePath or array of image paths as second argument
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
				zorgDebugger::log()->debug('Tweet "%s" sent => https://twitter.com/%s/status/%s', [$tweet->text, TWITTER_NAME, $tweet->id_str]);

				/** Update Event-Entry in the Database */
				$sql = 'UPDATE events SET tweet=? WHERE id=?';
				if (!$db->query($sql,__FILE__, __LINE__, 'action --> tweet', [$tweet->id_str, $eventId])) $error = 'Cannot update Tweet-Status for Event ID ' . $eventId;

			} else {
				$error = 'Twitter API: $twitter->send() ERROR';
				zorgDebugger::log()->error('%s', [$error]);
			}

		} else {
			$errormsg = 'Twitter API Keys: ERROR - file missing';
			$error = $errormsg;
			zorgDebugger::log()->error('$errormsg: %s', [$errormsg]);
		}
		break;

}

/** Redirect request */
$goToUrl = $redirect_url . ( !empty($error) ? '&error='.rawurlencode($error) : '');
zorgDebugger::log()->debug('Redirecting to %s', [$goToUrl]);
header('Location: ' . $goToUrl );
exit;
