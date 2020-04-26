<?php
/**
 * APOD
 *
 * Holt und speichert die Astronomy Pictures of the Day (APOD)
 *
 * @author [z]biko
 * @date 01.01.2004
 * @package zorg\APOD
 */
/**
 * File includes
 * @include config.inc.php	Include required global site configurations
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 * @include	forum.inc.php 	Forum and Commenting Functions
 * @include	gallery.inc.php Gallery and Pic functions
 * @include util.inc.php 	Various Helper Functions
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';
require_once INCLUDES_DIR.'gallery.inc.php';
require_once INCLUDES_DIR.'util.inc.php';

/**
 * Astronomy Picture of the Day (APOD)
 *
 * Holt und speichert das neus Astronomy Pic of the Day (APOD).
 * APOD Bild wird via Funktion createPic() nach /data/gallery/41/ kopiert!
 * (kann also aus dem APOD Temp img-Ordner gelöscht werden danach)
 * 
 * API Description: concept_tags are now disabled in this service. Also, an optional return parameter copyright is returned if the image is not public domain.
 * 	QUERY PARAMETERS:
 * 	Parameter	| Type			| Default	| Description
 * 	date		| YYYY-MM-DD	| today		| The date of the APOD image to retrieve
 * 	hd			| bool			| False		| Retrieve the URL for the high resolution image
 * 	api_key		| string		| DEMO_KEY	| api.nasa.gov key for expanded usage
 *
 * @author [z]biko
 * @author IneX
 * @version 4.0
 * @since 1.0 `01.01.2004` function added
 * @since 2.0 `06.08.2018` function refactored to use NASA APOD API
 * @since 3.0 `09.08.2018` enhanced function so an APOD date can be passed
 * @since 4.0 `14.09.2018` added processing of videos & website links passed from the APOD API
 *
 * @uses APOD_API
 * @uses APOD_TEMP_IMGPATH
 * @uses APOD_GALLERY_ID
 * @var $MAX_PIC_SIZE
 * @uses cURLfetchJSON()
 * @uses createPic()
 * @uses getYoutubeVideoThumbnail()
 * @uses getVimeoVideoThumbnail()
 * @uses Comment::post()
 * @param string $apod_date (Optional) A valid date after June 16 1995, formatted as: yyyy-mm-dd (2018-08-06)
 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
 * @global	array	$MAX_PIC_SIZE	Globales Array im Scope von gallery.inc.php mit den Image-Width & -Height Grössen für Pics und Thumbnails
 * @return boolean Returns true or false, depening on if the function was processed successfully or not
 */
function get_apod($apod_date_input=NULL)
{
	global $db, $MAX_PIC_SIZE;

	/** Validate $apod_date if passed */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $apod_date_input: %s', __FUNCTION__, __LINE__, $apod_date_input));
	if (empty($apod_date_input) || strtotime($apod_date_input) === false) $apod_date_input = NULL;

	/** Retrieve the APOD data from the APOD_API */
	$apod_data = cURLfetchJSON(APOD_API . (!empty($apod_date_input) ? '&date='.$apod_date_input : ''));

	/** If $apod_data is not empty / valid */
	if (!empty($apod_data) && $apod_data !== false)
	{
		/**
		 * Process $apod_data
		 *
		 * Example REST API response:
		 *	 Array
		 *	(
		 *	    [copyright] => Francesco Sferlazza
		 *	    [date] => 2018-08-01
		 *	    [explanation] => Cosmic rays from outer space go through your body every second. Typically, they do you no harm [...]
		 *	    [hdurl] => https://apod.nasa.gov/apod/http://nusoft.fnal.gov/nova/public/img/FD-evt-echo.gif
		 *	    [media_type] => image
		 *	    [service_version] => v1
		 *	    [title] => Live: Cosmic Rays from Minnesota
		 *	    [url] => https://apod.nasa.gov/apod/http://nusoft.fnal.gov/nova/public/img/FD-evt-echo.gif
		 *	)
		 */
		if ( DEVELOPMENT && $apod_date_input != NULL ) error_log(sprintf('[DEBUG] <%s:%d> date("ymd",$apod_date_input): %s', __FUNCTION__, __LINE__, date('ymd',strtotime($apod_date_input))));
		if ( DEVELOPMENT && $apod_date_input == NULL ) error_log(sprintf('[DEBUG] <%s:%d> date("ymd",strtotime($apod_data[date])): %s', __FUNCTION__, __LINE__, date('ymd',strtotime($apod_data['date']))));
		$new_apod_date = ( $apod_date_input != NULL ? date('ymd',strtotime($apod_date_input)) : date('ymd',strtotime($apod_data['date'])) );
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $new_apod_date: %s', __FUNCTION__, __LINE__, $new_apod_date));
		$new_apod_title = $apod_data['title'];
		$new_apod_explanation = $apod_data['explanation'];
		$new_apod_copyright = $apod_data['copyright'];
		$new_apod_mediatype = $apod_data['media_type'];
		$new_apod_img_small = str_replace('https://apod.nasa.gov/apod/http', 'http', $apod_data['url']); // with fix for malformed url (APOD API issue)
		$new_apod_img_large = str_replace('https://apod.nasa.gov/apod/http', 'http', $apod_data['hdurl']);  // with fix for malformed url (APOD API issue)
		$new_apod_archive_url = APOD_SOURCE . 'ap'.$new_apod_date.'.html'; // E.g.: https://apod.nasa.gov/apod/ap180714.html
		$new_apod_urlparts = pathinfo($new_apod_img_small);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> pathinfo(): %s', __FUNCTION__, __LINE__, print_r($new_apod_urlparts,true)));
		$new_apod_fileext = $new_apod_urlparts['extension'];
		$new_apod_filename = $apod_data['date'] . '.' . $new_apod_fileext;
		$new_apod_temp_filepath = APOD_TEMP_IMGPATH . $new_apod_filename;
		if ($new_apod_fileext === 'html') $new_apod_mediatype = 'website';

		/** Check if APOD is not already fetched... */
		try {
			$sql = 'SELECT id, name, extension, pic_added FROM gallery_pics WHERE album = '.APOD_GALLERY_ID.' AND DATE(pic_added) = "'.$new_apod_date.'"';
			$checkTodaysAPOD = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__));
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
		if (empty($checkTodaysAPOD['name']) || strpos($checkTodaysAPOD['name'], $new_apod_title) === false)
		{
			/** Save new APOD to the gallery_pics database table */
			if (!empty($new_apod_title))
			{
				try {
					if ($new_apod_mediatype === 'image') $new_apod_fileext = '.'.$new_apod_fileext;
					$new_apod_picid = $db->insert('gallery_pics', [
																	 'album'=>APOD_GALLERY_ID
																	,'extension'=>$new_apod_fileext
																	,'pic_added'=>$new_apod_date
																	,'name'=>escape_text($new_apod_title.($new_apod_mediatype == 'video' ? ' [video]' : ''))
																  ], __FILE__, __LINE__, __FUNCTION__);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $new_apod_picid: %s', __FUNCTION__, __LINE__, $new_apod_picid));

				} catch (Exception $e) {
					error_log($e->getMessage());
					return false;
				}

			/** If $new_apod_title is empty, abort */
			} else {
				error_log(sprintf('<%s:%d> $new_apod_title EMPTY: %s', __FUNCTION__, __LINE__, $new_apod_title));
				return false;
			}

			/** APOD saved to DB successfully */
			if (!empty($new_apod_picid) && is_numeric($new_apod_picid))
			{
				switch ($new_apod_mediatype)
				{
					/** APOD media_type is 'image'... */
					case 'image':
						/** Fetch and save the APOD image to APOD_TEMP_IMGPATH */
						if (!cURLfetchUrl($new_apod_img_small, $new_apod_temp_filepath)) goto cleanup;

						/** Filepfade zum finalen Speicherort des aktuellen APOD-Bildes (Original & Thumbnail) */
						$new_apod_filepath_pic = picPath(APOD_GALLERY_ID, $new_apod_picid, $new_apod_fileext); // Fix eventual double-slashes in path
						$new_apod_filepath_pic_tn = tnPath(APOD_GALLERY_ID, $new_apod_picid, $new_apod_fileext); // Fix eventual double-slashes in path

						/** Create APOD gallery pic */
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> createPic(): %s', __FUNCTION__, __LINE__, $new_apod_filepath_pic));
						if (!createPic($new_apod_temp_filepath, $new_apod_filepath_pic, $MAX_PIC_SIZE['picWidth'], $MAX_PIC_SIZE['picHeight']))
						{
							error_log(sprintf('<%s:%d> %s createPic() ERROR: %s', __FILE__, __LINE__, __FUNCTION__, $new_apod_filepath_pic));
							/** Goto: cleanup */
							goto cleanup;
						}

						/** Create APOD gallery pic-thumbnail */
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> createPic() thumbnail: %s', __FUNCTION__, __LINE__, $new_apod_filepath_pic_tn));
						if (!createPic($new_apod_temp_filepath, $new_apod_filepath_pic_tn, $MAX_PIC_SIZE['tnWidth'], $MAX_PIC_SIZE['tnHeight']))
						{
							error_log(sprintf('<%s:%d> %s createPic() thumbnail ERROR: %s', __FILE__, __LINE__, __FUNCTION__, $new_apod_filepath_pic_tn));
							/** Goto: cleanup */
							goto cleanup;
						}

						/** Regular cleanup: remove temp-file from APOD_TEMP_IMGPATH */
						if (!unlink($new_apod_temp_filepath)) error_log(sprintf('<%s:%d> unlink($new_apod_temp_filepath) ERROR: %s', __FUNCTION__, __LINE__, $new_apod_temp_filepath));
						break;

					/**
					 * APOD media_type is 'video'
					 * Remark: for media_type="video", the API parameter hdurl="" is NOT AVAILABLE!
					 */
					case 'video':
						/* Find out what 'video'-type exactly we're dealing with... */
						$video_services = [
												 [
													 'service' => 'youtube'
													,'identifier' => 'https://www.youtube.com/embed/'
												 ]
												,[
													 'service' => 'vimeo'
													,'identifier' => 'https://player.vimeo.com/video/'
												 ]
											];
						foreach ($video_services as $service)
						{
							if (strpos($service['identifier'], $new_apod_urlparts['dirname']) !== false)
							{
							    $media_type = $service['service'];
							    /** Video type found, let's exit the foreach{}-loop */
							    if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $service[identifier] found: %s', __FUNCTION__, __LINE__, $media_type));
								break;
							}
						}

						/** No matching $media_type found, let's Goto: cleanup */
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $media_type: %s', __FUNCTION__, __LINE__, print_r($media_type,true)));
						if (empty($media_type) || is_array($media_type))
						{
							/** Goto: cleanup */
							goto cleanup;

						} else {
							/** Get Video-Thumbnail image */
							$new_apod_img_thumbnail = getVideoThumbnail($media_type, $new_apod_urlparts['filename']);
							$new_apod_temp_filepath = $new_apod_temp_filepath.pathinfo($new_apod_img_thumbnail, PATHINFO_EXTENSION);
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> cURLfetchUrl(): %s', __FUNCTION__, __LINE__, $new_apod_temp_filepath));
							if (!cURLfetchUrl($new_apod_img_thumbnail, $new_apod_temp_filepath)) goto cleanup;
	
							/** Create APOD gallery pic-thumbnail for 'video' */
							$new_apod_filepath_pic_tn = tnPath(APOD_GALLERY_ID, $new_apod_picid, '.'.pathinfo($new_apod_img_thumbnail, PATHINFO_EXTENSION));
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> createPic() thumbnail: %s', __FUNCTION__, __LINE__, $new_apod_filepath_pic_tn));
							if (!createPic($new_apod_temp_filepath, $new_apod_filepath_pic_tn, $MAX_PIC_SIZE['tnWidth'], $MAX_PIC_SIZE['tnHeight']))
							{
								error_log(sprintf('<%s:%d> %s createPic() thumbnail ERROR: %s', __FILE__, __LINE__, __FUNCTION__, $new_apod_filepath_pic_tn));
								goto cleanup;
							}
	
							/** Update APOD 'video' entry in gallery_pics table */
							try {
								$result = $db->update('gallery_pics', ['id', $new_apod_picid], ['extension' => $media_type, 'picsize' => $new_apod_img_small], __FILE__, __LINE__, __FUNCTION__);
								if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(gallery_pics): (%s) %s', __FUNCTION__, __LINE__, $result, ($result>0 ? 'true' : 'false')));
								if ($result === 0) goto cleanup;
							} catch (Exception $e) {
								error_log($e->getMessage());
							}
						}
						break;

					/**
					 * APOD is actually a 'website'-type - not a 'video' (overwritten manually)
					 * Remark: for media_type="website", the API parameter hdurl="" is NOT AVAILABLE!
					 */
					case 'website':
						/** Create APOD gallery pic-thumbnail for 'video' or 'website' */
						$new_apod_temp_filepath = PHP_IMAGES_DIR . 'apod/tn_website.png';
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $new_apod_temp_filepath: %s', __FUNCTION__, __LINE__, $new_apod_temp_filepath));
						$new_apod_filepath_pic_tn = tnPath(APOD_GALLERY_ID, $new_apod_picid, '.'.pathinfo($new_apod_temp_filepath, PATHINFO_EXTENSION));
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> createPic() thumbnail: %s', __FUNCTION__, __LINE__, $new_apod_filepath_pic_tn));
						if (!createPic($new_apod_temp_filepath, $new_apod_filepath_pic_tn, $MAX_PIC_SIZE['tnWidth'], $MAX_PIC_SIZE['tnHeight']))
						{
							error_log(sprintf('<%s:%d> %s createPic() thumbnail ERROR: %s', __FILE__, __LINE__, __FUNCTION__, $new_apod_filepath_pic_tn));
							goto cleanup;
						}

						/** Update APOD 'website' entry in gallery_pics table */
						try {
							$result = $db->update('gallery_pics', ['id', $new_apod_picid], ['extension' => $new_apod_mediatype, 'picsize' => $new_apod_img_small], __FILE__, __LINE__, __FUNCTION__);
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(gallery_pics): (%s) %s', __FUNCTION__, __LINE__, $result, ($result>0 ? 'true' : 'false')));
							if ($result === 0) goto cleanup;
						} catch (Exception $e) {
							error_log($e->getMessage());
						}
						break;

					/**
					 * If APOD media_type is unsupported, goto cleanup
					 */
					default:
						error_log(sprintf('<%s:%d> APOD has an unsupported media_type: %s', __FUNCTION__, __LINE__, $new_apod_mediatype));
						goto cleanup;
				}

				/**
				 * Add APOD explanation & links with user [z]Barbara Harris
				 * - for media_type="video", the $new_apod_img_large is not available => therefore fallback to $new_apod_img_small
				 * - the $new_apod_copyright is not always available => therefore fallback to $new_apod_archive_url
				 */
				$new_apod_comment = t('apod-pic-comment', 'apod', [ (!empty($new_apod_img_large) ? $new_apod_img_large : $new_apod_img_small), $new_apod_title, $new_apod_explanation, $new_apod_archive_url, (!empty($new_apod_copyright) ? $new_apod_copyright : $new_apod_archive_url) ]);
				Comment::post($new_apod_picid, 'i', BARBARA_HARRIS, $new_apod_comment);
				return true;

				/** Goto cleanup: on createPic=FALSE this goto will Cleanup & DELETE DB-Entry */
				cleanup:
					try {
						$deleteFromGalleryPics = $db->query('DELETE FROM gallery_pics WHERE id = ' . $new_apod_picid, __FILE__, __LINE__, __FUNCTION__);
						return false;
					} catch (Exception $e) {
						error_log($e->getMessage());
						return false;
					}
			}

		/** ...APOD is already fetched! */
		} else {
			error_log(sprintf('<%s:%d> APOD for $new_apod_date already fetched! => "%s" %s', __FUNCTION__, __LINE__, $new_apod_title, tnPath(APOD_GALLERY_ID, $checkTodaysAPOD['id'], $checkTodaysAPOD['extension'])));
			return false;
		}

	/** APOD_SOURCE URL is INVALID / NOT available */
	} else {
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> cURLfetchJSON(APOD_API) ERROR: %s', __FUNCTION__, __LINE__, print_r($apod_data, true)));
		return false;
	}
}


/**
 * Aktuelleste APOD Bild-ID
 * Holt das aktuellste APOD Bild aus der Datenbank
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return array DB-Query Result als Resource (Array)
 */
function get_apod_id()
{
	global $db;

	$sql = 'SELECT * FROM gallery_pics WHERE album = '.APOD_GALLERY_ID.' ORDER by id DESC LIMIT 0,1';
	return $db->fetch($db->query($sql));
}
