<?
/**
 * FILE INCLUDES
 */
if (!require_once PHP_INCLUDES_DIR.'/usersystem.inc.php') die('ERROR: Usersystem could NOT be loaded!');


/**
 * Mobilezorg Chat
 * Before using it, make sure the Setup has been done:
 * /scripts/mobilezorg_v2_setup.php
 * 
 * @author IneX
 * @date 16.01.2016
 * @version 1.0
 * @package Mobilezorg
 * @subpackage Chat
 */
class mobilezChat
{
	/**
	 * Chat Messages
	 * Query and output chat messages
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string {$order} is the column to sort the results
	 * @param integer {$limit} defines the maximum number of results
	 * @global $pdo_db PDO-Database Object, active SQL-Connection
	 * @global $smarty Smarty Class-object, the template engine
 	 */
	function getChatMessages($order = 'date', $limit = 25)
	{
		global $pdo_db, $smarty;
		
		try {
			$query = $pdo_db->query(sprintf('SELECT text, UNIX_TIMESTAMP(date) AS date, user_id FROM %s ORDER BY %s DESC LIMIT 0,%u', DB_CHAT_TABLE, $order, $limit));
			$rows = $query->fetchAll(); // Returns rows or "false"
			if (!$rows) {
				// No query results
				Error_Handler::addError('MySQL DB-Query returned 0 results', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
			} else {
				// If query returned a positive result set
				//$smarty->assign('query_result', $rows);
				return $rows;
			}
		} catch(PDOException $err) {
			Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
		}
	}
	
	
	/**
	 * Save Chat Message
	 * Saves a Chat Message to the Database
	 * 
	 * @ToDo Parse for User @mentioning and generate Notification (e-mail?)
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param integer {$user_id} ID of the user who posted the message
	 * @param string {$message} is the message the user posted
	 * @param integer {$from_mobile} defines if the user posted from a mobile device
	 * @global $pdo_db PDO-Database Object, active SQL-Connection
 	 */
	function postChatMessage($user_id, $message, $from_mobile = 0)
	{
		global $pdo_db;
		
		try {
			$query = $pdo_db->prepare("INSERT INTO chat (user_id, date, from_mobile, text) VALUES (:userid, now(), :frommobile, :message)");
			$query->execute(array(
						    'userid' => $user_id
						    ,'frommobile' => $from_mobile
						    ,'message' => $message
						));
			if (!$query) {
				// No insert
				Error_Handler::addError('MySQL table row Insert failed', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
				return false;
			}/* else {
				// Successfully inserted
				return true;
			}*/
		} catch(PDOException $err) {
			Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
			return false;
		}
	}
	
	
	/**
	 * Get additional Messages
	 * Queries and returns additional Chat Messages from a specific starting point.
	 * This is required for something like a "Load more"-button.
	 * 
	 * @ToDo combine this function into getChatMessages()
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param integer {$start_id} from what record to start from
	 * @param integer {$limit} defines the maximum number of results
	 * @param string {$order} is the column to sort the results
	 * @global $pdo_db PDO-Database Object, active SQL-Connection
 	 */
	function getAdditionalChatMessages($start_date, $limit = 25, $order = 'date')
	{
		global $pdo_db, $user;
		
		$jsonResult = array();
		
		try {
			$query = $pdo_db->query(sprintf('SELECT text, UNIX_TIMESTAMP(date) AS date, user_id FROM %s WHERE UNIX_TIMESTAMP(date) < %u ORDER BY %s DESC LIMIT 0,%u', DB_CHAT_TABLE, $start_date, $order, $limit));
			$rows = $query->fetchAll(); // Returns rows or "false"
			if (!$rows) {
				// No query results
				Error_Handler::addError('MySQL DB-Query returned 0 results', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
			} else {
				// If query returned a positive result set
				foreach ($rows as $row)
				{
					$jsonDataEntry = array(
									 'date' => $row['date']
									,'user_id' => $row['user_id']
									,'user_name' => utf8_encode($user->id2user($row['user_id'], true))
									,'text' => utf8_encode($row['text'])
									//,'userpic' => $user->userImage($row['user_id']) -> disabled because slowing everything down
								);
					
					array_push($jsonResult, $jsonDataEntry);
				}
				return $jsonResult;
			}
		} catch(PDOException $err) {
			Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
		}
	}
	
	
	/**
	 * Save Uploaded Image
	 * Saves an uploaded image file into the user's file directory
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param integer {$user_id} ID of the user who posted the message
	 * @param string {$image_path} contains the path to the uploaded image
	 * @param integer {$image_size} contains the size of the uploaded image
	 * @param string {$image_type} contains the mime type of the uploaded image
	 * @param string {$image_name} contains the name of the uploaded image, if available
	 * @param string {$image_extension} contains the file exteions of the uploaded image, if available
	 * @param integer {$from_mobile} defines if the user posted from a mobile device
	 * @global $pdo_db PDO-Database Object, active SQL-Connection
	 */
	function saveImage($user_id, $image_path, $image_size, $image_type, $image_name = '', $image_extension = '', $from_mobile = 0)
	{
		global $pdo_db;
		
		if (empty($image_extension)) $image_extension = IMAGE_FORMAT;
		$target_dir = usersystem::get_and_create_user_files_dir($user_id);
		$target_dir = USER_FILES_DIR.$user_id.'/';
		$filename  = (!empty($image_name) ? str_replace('.','',str_replace(',','_',str_replace(' ','_',$image_name))) : 'file');
		$filename .= '_'.time().IMG_FULL_SUFFIX.'.'.$image_extension;
		$full_file_savepath = $target_dir.$filename;
		
		// Download and save the image file
		if(@copy($image_path, $full_file_savepath))
		{
			if (chmod($full_file_savepath, 0664))
			{
				try {
					$query = $pdo_db->prepare("INSERT INTO files (user, upload_date, name, size, mime) VALUES (:userid, now(), :filename, :filesize, :mimetype)");
					$query->execute(array(
								    'userid' => $user_id
								    ,'filename' => $filename
								    ,'filesize' => $image_size
								    ,'mimetype' => $image_type
								));
					if (!$query)
					{
						// No insert
						Error_Handler::addError('MySQL table row Insert failed', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
						return false;
					} else {
						// Successfully inserted
						$saved_file_path = FILES_DIR.$user_id.'/'.$filename;
						// Make a Thumbnail, too
						//$saved_thumb_path = self::saveImageThumbnail($user_id, $image_path);
						$saved_thumb_path = self::saveImageThumbnail($user_id, $full_file_savepath, $image_name);
						if (!empty($saved_file_path) && !empty($saved_thumb_path))
						{
							$message = sprintf('<a href="%1$s" target="_blank"><img name="%2$s" id="%2$s" class="" src="%3$s"></a>', $saved_file_path, $filename, $saved_thumb_path);
							mobilezChat::postChatMessage($user_id, $message, $from_mobile);
						} else {
							Error_Handler::addError('Function saveImage() failed', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
							return false;
						}
					}
				} catch(PDOException $err) {
					Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
					return false;
				}
		    }
		} else {
			Error_Handler::addError('File copy Error', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
			return false;
		}
	}
	
	
	/**
	 * Save Geolocation as Chat Message
	 * Saves a Google Maps URL as Chat Message to the Database
	 * additionally it triggers a download of a Google Maps Image file
 	 * Example URL: http://maps.googleapis.com/maps/api/staticmap?size=640x480&scale=1&zoom=15&markers=color:red%7Csize:mid%7C47.39220630060216,9.366854022435746
	 * @link https://developers.google.com/maps/documentation/static-maps/ Google Static Maps Developer Guide
 	 * 
 	 * @ToDo Save Google Staticmap in 3 sizes: small, medium, large
 	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param integer {$user_id} ID of the user who posted the message
	 * @param string {$latlng} are the latitude & longitude coordinates the user posted
	 * @param integer {$from_mobile} defines if the user posted from a mobile device
	 */
	function postGoogleMapsLocation($user_id, $latlng, $from_mobile = 0)
	{
		$googlemaps_staticmap_url = sprintf(
										'http://maps.googleapis.com/maps/api/staticmap?format=%s&size=%s&scale=%u&zoom=%u&markers=%s%s'
										,IMAGE_FORMAT	// Image MIME-Type
										,'320x180'		// max: 640x480
										,'1'			// 1 or 2
										,'17'			// 0 = entire earth, 25 = single building
										,'color:red%7Csize:mid%7C' // marker settings
										,$latlng
									);
		error_log('[DEBUG] '.$googlemaps_staticmap_url);//debug
		$googlemaps_link_url = 'https://www.google.com/maps/place/' . $latlng;
		
		$saved_image_url = mobilezChat::saveGoogleMapsImage($user_id, $googlemaps_staticmap_url);
		if ($saved_image_url != false)
		{
			$message = sprintf('<a href="%1$s" target="_blank"><img name="%2$s" id="%2$s" class="" src="%3$s"></a>', $googlemaps_link_url, $latlng, $saved_image_url);
			mobilezChat::postChatMessage($user_id, $message, $from_mobile);
		} else {
			Error_Handler::addError('Function saveGoogleMapsImage() failed', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
			return false;
		}
	}
	
		/**
		 * Google Maps API - staticmap
		 * Grab and save a Google Maps Snapshot and save image into user's file directory
		 * 
		 * @author IneX
		 * @version 1.0
		 * @since 1.0
		 * 
		 * @param integer {$user_id} ID of the user who posted the message
		 * @param string {$image_url} contains the full URL to the Google Maps staticimage
		 * @global $pdo_db PDO-Database Object, active SQL-Connection
		 */
		private function saveGoogleMapsImage($user_id, $image_url)
		{
			global $pdo_db;
			
			$target_dir = usersystem::get_and_create_user_files_dir($user_id);
			$target_dir = USER_FILES_DIR.$user_id.'/';
			$filename = 'staticmap_'.time().'.'.IMAGE_FORMAT;
			$full_file_savepath = $target_dir.$filename;
			
			// Download and save the Google Maps staticimage file from the web
			if(@copy($image_url, $full_file_savepath))
			{
				if (chmod($full_file_savepath, 0664))
				{
					try {
						$query = $pdo_db->prepare("INSERT INTO files (user, upload_date, name, size, mime) VALUES (:userid, now(), :filename, :filesize, :mimetype)");
						$query->execute(array(
									    'userid' => $user_id
									    ,'filename' => $filename
									    ,'filesize' => filesize($full_file_savepath)
									    ,'mimetype' => IMAGE_FORMAT_MIME
									));
						if (!$query) {
							// No insert
							Error_Handler::addError('MySQL table row Insert failed', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
							return false;
						} else {
							// Successfully inserted
							return FILES_DIR.$user_id.'/'.$filename;
						}
					} catch(PDOException $err) {
						Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
						return false;
					}
			    }
			} else {
				Error_Handler::addError('File copy Error', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
				return false;
			}
		}
	
	
	/**
	 * Handle Chat Message Commands
	 * Deals with various special Commands requested from the Chat Message
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param integer {$user_id} ID of the user who executed the command
	 * @param string {$command} is the extracted '/command' from the Chat Message
	 * @param array {$parameters} are the extracted /command PARAMETERS' from the Chat Message
 	 */
	function execChatMessageCommand($user_id, $command, $parameters)
	{
		$valid_commands = array(
								'anfick' => 'postAnfickMessage'
							);
		foreach ($valid_commands as $command_name => $command_function)
		{
			if ($command_name == $command) {
				mobilezChat::{$command_function}($user_id, $parameters); //Not sure why this works, but it does ;)
			} else {
				Error_Handler::addError('Command function execution failure', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
				return false;
			}
		}
	}
	
		/**
		 * Anfick
		 * Fickt einen User aufs übelste an
		 * 
		 * @param integer {$from_user_id} ID of the user who ficks an
		 * @param integer {$to_user_id} ID of the user who gets angefickt
		 * @global $pdo_db PDO-Database Object, active SQL-Connection
		 */
		private function postAnfickMessage($from_user_id, $to_user)
		{
			global $pdo_db, $user;
			
			try {
				$adj_query = $pdo_db->query('SELECT wort, typ FROM aficks WHERE typ = 1 ORDER BY RAND() LIMIT 1');
				$nom_query = $pdo_db->query('SELECT wort, typ FROM aficks WHERE typ = 2 ORDER BY RAND() LIMIT 1');
				$adjektiv = $adj_query->fetchColumn(); // Returns the first column from the first row or "false"
				$nomen = $nom_query->fetchColumn(); // Returns the first column from the first row or "false"
				if (!$adjektiv || !$nomen) {
					// No query results
					Error_Handler::addError('MySQL DB-Query returned 0 results', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
					return false;
				} else {
					// If query returned a positive result set
					//$anfickender = $user->id2user($from_user_id, false);
					$anfickender = BARBARA; // [z]Barbara Harris *har har*
					$angefickter = $to_user;//$user->id2user($to_user_id, false);
					$anfick =  '@'.$angefickter.' du '.$adjektiv.$nomen;//.' (sait zumindest dä '.$anfickender.')';
					mobilezChat::postChatMessage($anfickender, $anfick);
				}
			} catch(PDOException $err) {
				Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
				return false;
			}
		}
	
	
	/**
	 * Parses a Chat Message before saving
	 * Does various magics to add fancy stuff to a Chat Message before saving it
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string {$message} is the Chat Message text
	 * @global $pdo_db PDO-Database Object, active SQL-Connection
 	 */
	private function parseChatMessage($message)
	{
		global $pdo_db;
	 	
	 	$mention_regex = '/\@(\S+)/i';
	 	$text = strip_tags($message);
	 	$mentions_found = preg_match_all($mention_regex, $text, $mention_matches);
	 	$user_id = usersystem::user2id($mention_matches[0]);
	 	$text = preg_replace($mention_regex, '<a href="/profil.php?user_id='.$user_id.'">$0</a>', $text);
		$text = Markdown::defaultTransform($text);
		return array('text' => $text, 'mentions' => $mention_matches[1]);
	 	
		/*$home_url = 'https://dankest.website/';
		$link_regex = '/\b(https?:\/\/)?(\S+)\.(\S+)\b/i';
		$hashtag_regex = '/\#([^\s\#]+)/i';
		$mention_regex = '/\@(\S+)/i';
		$t = strip_tags($text);
		$links_found = preg_match_all($link_regex, $t, $link_matches);
		$hashtags_found = preg_match_all($hashtag_regex, $t, $hashtag_matches);
		$mentions_found = preg_match_all($mention_regex, $t, $mention_matches);
		$t = preg_replace_callback($link_regex, function($matches) {
			$the_link = trim($matches[0]);
			if (substr($the_link, 0, 4) != 'http') {
				$the_link = 'http://'.$the_link;
			}
			// check for youtube, vimeo, mp4/mov/webm, mp3, jpg/jpeg/png/gif
			if (preg_match('/(?:youtu\.be|youtube\.com)\/(?:embed\/)?(?:watch\?v=)?([^\#\&\?\s]+)/i', $the_link, $youtube_matches)) { // if youtube
				return '<div class="expanded-content"><iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$youtube_matches[1].'" frameborder="0" allowfullscreen></iframe></div>';
			} else if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/i', $the_link, $vimeo_matches)) { // if vimeo
				return '<div class="expanded-content"><iframe width="100%" height="100%" src="https://player.vimeo.com/video/'.$vimeo_matches[1].'" frameborder="0" allowfullscreen></iframe></div>';
			} else if (preg_match('/\.(?:mp4|mov|webm)$/i', $the_link)) { // if mp4/mov/webm
				return '<div class="expanded-content"><video controls="controls" src="'.$the_link.'"></video></div>';
			} else if (preg_match('/\.mp3$/i', $the_link)) { // if mp3
				return '<div class="expanded-content"><audio controls="controls" src="'.$the_link.'"></audio></div>';
			} else if (preg_match('/\.(?:jpg|jpeg|gif|png)$/i', $the_link)) { // if jpg/jpeg/png/gif
				return '<div class="expanded-content"><img src="'.$the_link.'" /></div>';
			} else { // just a link
				return '<a href="'.$the_link.'">'.$matches[0].'</a>';
			}
		}, $t);
		$t = preg_replace($hashtag_regex, '<a href="'.$home_url.'tagged/$1/">$0</a>', $t);
		$t = preg_replace($mention_regex, '<a href="'.$home_url.'by/$1/">$0</a>', $t);
		if (preg_match('/"expanded-content"/i', $t)) {
			$t .= '<div class="clear"></div>'; // .clear should be css = clear: both;
		}
		$t = Markdown::defaultTransform($t);
		return array('text' => $t, 'links' => $link_matches[0], 'mentions' => $mention_matches[1], 'hashtags' => $hashtag_matches[1]);
		*/
 	 }
 	 
 	 
 	/**
	 * Image Thumbnail Download
	 * Download a small preview thumbnail of an image link within a Chat Message for linking
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param integer {$user_id} ID of the user who posted the message
	 * @param string {$image_url} contains the full URL to the image
	 * @global $pdo_db PDO-Database Object, active SQL-Connection
	 * @return string
	 */
	private function saveImageThumbnail($user_id, $image_url, $image_name = '')
	{
		global $pdo_db;
		
		$image_name = (!empty($image_name) ? str_replace('.','',str_replace(',','_',str_replace(' ','_',$image_name))) : 'file' );
		$target_dir = usersystem::get_and_create_user_files_dir($user_id);
		$target_filename = $image_name.IMG_THUMB_SUFFIX.'.'.IMAGE_FORMAT;
		$full_file_savepath = $target_dir.$target_filename;
		
		// Read/download and save the image file
		if($fcontents = file_get_contents($image_url))
		{
			$img = imagecreatefromstring($fcontents);
			$width = imagesx($img);
			$height = imagesy($img);
			$img_thumb = imagecreatetruecolor(IMG_THUMB_W, IMG_THUMB_H);
			imagecopyresized($img_thumb, $img, 0, 0, 0, 0, IMG_THUMB_W, IMG_THUMB_H, $width, $height);
			imagejpeg($img_thumb, $full_file_savepath); //save image as jpg
			imagedestroy($img_thumb); 
			imagedestroy($img);
			
			if (chmod($full_file_savepath, 0664))
			{
				try {
					$query = $pdo_db->prepare("INSERT INTO files (user, upload_date, name, size, mime) VALUES (:userid, now(), :filename, :filesize, :mimetype)");
					$query->execute(array(
								    'userid' => $user_id
								    ,'filename' => $target_filename
								    ,'filesize' => filesize($full_file_savepath)
								    ,'mimetype' => IMAGE_FORMAT_MIME
								));
					if (!$query) {
						// No insert
						Error_Handler::addError('MySQL table row Insert failed', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
						return false;
					} else {
						// Successfully inserted
						return FILES_DIR.$user_id.'/'.$target_filename;
					}
				} catch(PDOException $err) {
					Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
					return false;
				}
		    }
		} else {
			Error_Handler::addError('File copy Error', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
			return false;
		}
	}
 	
 	
 	/**
	 * Save Bug
	 * Saves a Bug Report to the Database
	 * 
	 * @ToDo Post new Bug Report as Chat message with link?
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param integer {$user_id} ID of the user who posted the message
	 * @param string {$title} is the title of the Bug Report
	 * @param string {$description} is the description of the Bug Report
	 * @param integer {$from_mobile} defines if the user posted from a mobile device
	 * @global $pdo_db PDO-Database Object, active SQL-Connection
 	 */
	function saveBug($user_id, $title, $description, $from_mobile = 0)
	{
		global $pdo_db;
		
		try {
			$query = $pdo_db->prepare("INSERT INTO bugtracker_bugs (category_id, reporter_id, priority, reported_date, title, description) VALUES (:categoryid, :userid, :priority, now(), :title, :description)");
			$query->execute(array(
							 'categoryid' 	=> BUG_CATEGORY_ID
						    ,'userid'		=> $user_id
						    ,'priority'		=> BUG_PRIORITY
						    ,'title'		=> $title
						    ,'description'	=> $description
						));
			$lastInsertId = $pdo_db->lastInsertId();
			error_log('[DEBUG] $lastInsertId: '.$lastInsertId);
			if (!$query) {
				// No insert
				Error_Handler::addError('MySQL table row Insert failed', __FILE__, __LINE__, __FUNCTION__, __CLASS__);
				return false;
			} else {
				// Successfully inserted
				if (is_numeric($lastInsertId))
				{
					$chatMessage = sprintf('%1$s hat einen Bug gemeldet: <a href="/bugtracker.php?bug_id=%2$u" target="_blank">%3$s</a>', usersystem::id2user($user_id, false), $lastInsertId, $title);
					mobilezChat::postChatMessage(BARBARA, $chatMessage);
				}
			}
		} catch(PDOException $err) {
			Error_Handler::addError('Error: '.$err->getMessage(), __FILE__, __LINE__, __FUNCTION__, __CLASS__);
			return false;
		}
	}
	
	
	/**
	 * User Password Reset
	 * Resets a User's password and sends the new one by e-mail
	 * 
	 * @author IneX
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string {$email} E-Mail address of the user to reset the Password
	 * @global $pdo_db Usersystem Object, contains all User methods
 	 */
	function execPwReset($email)
	{
		global $user;
		return $user->new_pass($email);
	}
 	
 	
 	/**
	 * RezaSeyf/twitterHashtags
	 * Twitter-like Hashtag system with Unicode and Hashtag Exporting system Support 
	 *
	 * @author RezaSeyf <reza.safe@icloud.com>
	 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
	 * github: https://github.com/RezaSeyf
	 * blog  : http://1reza.blogspot.com
	 *
	 * @param string $str the input string to be proccess and export the hashtags from it
	 * @param string $outputType using 'null' as default will output the full text with href'ed links for hashtags or 'tagsOnly' to just show the hashtags
	 * @return string Returns the full string with linked hashtags or will just give you the hashtags.
	 */  
	private function tagExtract($str, $outputType = null) 
	{ 
	  	/**
		 * @var hashtagsArray[] 
		 * An array of string objects for storing hashtags inside it. 
		 */
		$hashtagsArray = array(); 
		
		/**
		 *
		 * @var strArray[] 
		 * An array of string objects that will save the words of the string argument.  
		 *
		 */
		$strArray = explode(" ",$str);
		
		/**
		 *
		 * @var string $pattern
		 * regular expression pattern for notes  
		 * don't scare! it works! even with unicode characters!
		 */
		$pattern = '%(\A#(\w|(\p{L}\p{M}?)|-)+\b)|((?<=\s)#(\w|(\p{L}\p{M}?)|-)+\b)|((?<=\[)#.+?(?=\]))%u'; 
		
		 
		foreach ($strArray as $b) 
		{	 
			// match the word with our hashtag pattern
		 	preg_match_all($pattern, ($b), $matches);
		 	
		 	/**
			 *
			 * @var hashtag[] 
			 * An array of string objects that will save the hashtags.
			 *
			 */
			$hashtag	= implode(', ', $matches[0]);	  
			
			// add to array if hashtag is not empty
			if (!empty($hashtag) or $hashtag != "")
				array_push($hashtagsArray, $hashtag); 
		}
		
		// now we have found all hashtags in the string
		// so we have to replace them and built a new string :
		foreach ($hashtagsArray as $c)
		{
			/**
			  *
			  * @var string $hashtagTitle
			  * container for the exported hashtags without # sign (to insert to db or etc) 
			  */
			$hashtagTitle = ltrim($c,"#");
			
			//create links for hashtags
			$str = str_replace($c,'<a href="?lookfor='.$hashtagTitle.'">#'.$hashtagTitle.'</a>',$str);
			
			// uncomment the below line to see the functionality.
			// echo "$hashtagTitle <br>";
		} 
		
		// return fulltext with linked hashtags OR return just the hashtags (with # sign)
		if ($outputType == "tagsOnly") 
			return $listOfHashtags = implode(" ",$hashtagsArray);  
		else
			return $str;	 
	}
}

// Instantiate new mobilezChat Class-object
$mobilezChat = new mobilezChat();