<?php
/**
 * APOD
 * 
 * Holt und speichert die Astronomy Pictures of the Day (APOD)
 *
 * @author [z]biko
 * @date 01.01.2004
 * @package Zorg
 * @subpackage APOD
 */
/**
 * File includes
 * @include config.inc.php	Include required global site configurations
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 * @include	forum.inc.php 	Forum and Commenting Functions
 * @include	gallery.inc.php Gallery and Pic functions
 * @include util.inc.php 	Various Helper Functions
 */
require_once( __DIR__ .'/config.inc.php');
require_once( __DIR__ .'/mysql.inc.php');
require_once( __DIR__ .'/forum.inc.php');
require_once( __DIR__ .'/gallery.inc.php');
require_once( __DIR__ .'/util.inc.php');

/**
* Grab the NASA API Key
* @include nasaapis_key.inc.php Include a String containing a valid NASA API Key
* @const NASA_API_KEY A constant holding the NASA API Key, can be used optionally (!) for requests to NASA's APIs such as the APOD
*/
if (!defined('NASA_API_KEY')) define('NASA_API_KEY', include_once( (file_exists( __DIR__ .'/nasaapis_key.inc.local.php') ? 'nasaapis_key.inc.local.php' : 'nasaapis_key.inc.php') ), true);
if (DEVELOPMENT && !empty(NASA_API_KEY)) error_log(sprintf('[DEBUG] <%s:%d> NASA_API_KEY: found', __FILE__, __LINE__));

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
 * @version 3.0
 * @since 1.0 01.01.2004 function added
 * @since 2.0 06.08.2018 function refactored to use NASA APOD API
 * @since 3.0 09.08.2018 enhanced function so an APOD date can be passed
 * @see APOD_API, APOD_TEMP_IMGPATH, APOD_GALLERY_ID, cURLfetchJSON(), createPic(), $MAX_PIC_SIZE, Comment::post()
 *
 * @param string $apod_date (Optional) A valid date after June 16 1995, formatted as: yyyy-mm-dd (2018-08-06)
 * @return boolean Returns true or false, depening on if the function was processed successfully or not
 */
function get_apod($apod_date_input)
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
		if ( $apod_date_input != NULL ) error_log(sprintf('[DEBUG] <%s:%d> date("ymd",$apod_date_input): %s', __FUNCTION__, __LINE__, date('ymd',strtotime($apod_date_input))));
		if ( $apod_date_input == NULL ) error_log(sprintf('[DEBUG] <%s:%d> date("ymd",strtotime($apod_data[date])): %s', __FUNCTION__, __LINE__, date('ymd',strtotime($apod_data['date']))));
		$new_apod_date = ( $apod_date_input != NULL ? date('ymd',strtotime($apod_date_input)) : date('ymd',strtotime($apod_data['date'])) );
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $new_apod_date: %s', __FUNCTION__, __LINE__, $new_apod_date));
		$new_apod_title = $apod_data['title'];
		$new_apod_explanation = $apod_data['explanation'];
		$new_apod_copyright = $apod_data['copyright'];
		$new_apod_mediatype = $apod_data['media_type'];
		$new_apod_img_small = str_replace('https://apod.nasa.gov/apod/http', 'http', $apod_data['url']); // with fix for malformed url (APOD API issue)
		$new_apod_img_large = str_replace('https://apod.nasa.gov/apod/http', 'http', $apod_data['hdurl']);  // with fix for malformed url (APOD API issue)
		$new_apod_archive_url = APOD_SOURCE . 'ap'.$new_apod_date.'.html'; // E.g.: https://apod.nasa.gov/apod/ap180714.html
		$new_apod_fileext = '.' . pathinfo($new_apod_img_small, PATHINFO_EXTENSION);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> pathinfo(): %s', __FUNCTION__, __LINE__, '.' . pathinfo($new_apod_img_small, PATHINFO_EXTENSION)));
		$new_apod_filename = $apod_data['date'] . $new_apod_fileext;
		$new_apod_temp_filepath = APOD_TEMP_IMGPATH . $new_apod_filename;
		
		/** Check if APOD is an image... */
		if ($new_apod_mediatype === 'image')
		{
			/** Check if APOD is not already fetched... */
			try {
				$sql = 'SELECT id, name, extension FROM gallery_pics WHERE album = '.APOD_GALLERY_ID.' AND DATE(pic_added) = "'.$new_apod_date.'"';
				$checkTodaysAPOD = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__));
			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}
			if (empty($checkTodaysAPOD['name']) || $checkTodaysAPOD['name'] != $new_apod_title)
			{
				/** Save new APOD to the gallery_pics database table */
				try {
					if (!empty($new_apod_title))
					{
						$new_apod_picid = $db->insert('gallery_pics', ['album'=>APOD_GALLERY_ID, 'extension'=>$new_apod_fileext, 'pic_added'=>$new_apod_date], __FILE__, __LINE__, __FUNCTION__);
						$sql = 'UPDATE gallery_pics set name = "'.escape_text($new_apod_title).'" WHERE id = ' . $new_apod_picid;
						$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	
					/** If $new_apod_title is empty, abort */
					} else {
						error_log(sprintf('<%s:%d> $new_apod_title EMPTY: %s', __FUNCTION__, __LINE__, $new_apod_title));
						return false;
					}
				} catch (Exception $e) {
					error_log($e->getMessage());
					return false;
				}

				/** APOD saved to DB successfully */
				if ($result)
				{
					/** Fetch and save the APOD image to APOD_TEMP_IMGPATH */
					cURLfetchUrl($new_apod_img_small, $new_apod_temp_filepath);

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
	
					$new_apod_comment = t('apod-pic-comment', 'apod', [ $new_apod_img_large, $new_apod_title, $new_apod_explanation, $new_apod_archive_url, (!empty($new_apod_copyright) ? $new_apod_copyright : $new_apod_archive_url) ]);

					Comment::post($new_apod_picid, 'i', BARBARA_HARRIS, $new_apod_comment);
					return true;

					/** Goto cleanup: on createPic=FALSE this goto will Cleanup & DELETE DB-Entry */
					cleanup:
						try {
							$sql = 'DELETE FROM gallery_pics WHERE id = ' . $new_apod_picid;
							$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
							return false;
						} catch (Exception $e) {
							error_log($e->getMessage());
							return false;
						}
				}

			/** ...APOD is already fetched! */
			} else {
				error_log(sprintf('<%s:%d> APOD for $new_apod_date already fetched! => "%s" %s', __FUNCTION__, __LINE__, $new_apod_title, picPath(APOD_GALLERY_ID, $checkTodaysAPOD['id'], $checkTodaysAPOD['extension'])));
				return false;
			}

		/** ...APOD is NOT an image */
		} else {
			error_log(sprintf('<%s:%d> APOD is not an image: [%s] %s', __FUNCTION__, __LINE__, $new_apod_mediatype, $new_apod_filepath));
			return false;
		}
	/** APOD_SOURCE URL is INVALID / NOT available */
	} else {
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> cURLfetchJSON(APOD_API) ERROR: %s', __FUNCTION__, __LINE__, print_r($apod_data, true)));
	}
}


/**
 * Aktuelleste APOD Bild-ID
 * 
 * Holt das aktuellste APOD Bild aus der Datenbank
 */
function get_apod_id()
{
	global $db;
	
	try {
		$sql = 'SELECT * FROM gallery_pics WHERE album = '.APOD_GALLERY_ID.' ORDER by id DESC LIMIT 0,1';
		return $db->fetch($db->query($sql));
	} catch (Exception $e) {
		error_log($e->getMessage());
		return false;
	}
}
