<?php
/**
 * Google API Integrations
 *
 * Anbindung verschiedener Google APIs
 *
 * @author		IneX
 * @package		zorg\Vendor\Google
 */

/**
 * File includes
 * @include config.inc.php
 * @const GOOGLE_API_KEY A constant holding the Google API Key required for requests to Google's APIs
 */
require_once __DIR__.'/config.inc.php';
if (!defined('GOOGLE_API_KEY') && isset($_ENV['GOOGLE_MAPS_API_KEY'])) define('GOOGLE_API_KEY', $_ENV['GOOGLE_MAPS_API_KEY']);
zorgDebugger::log()->debug('GOOGLE_API_KEY: %s', [(!empty(GOOGLE_API_KEY) ? 'found' : 'MISSING')]);

/**
 * Google Maps API Class
 *
 * In dieser Klasse befinden sich Funktionen für die Kommunikation mit den Google Maps API
 * Folgende Maps API werden unterstützt:
 *     - /maps/api/geocode
 *
 * @author		IneX
 * @version		1.0
 * @since		1.0 `12.06.2018` `IneX` Initial integration
 */
class GoogleMapsApi
{
	/**
	 * Google Maps Geocoding API
	 *
	 * Geocoding is the process of converting addresses (like "1600 Amphitheatre Parkway, Mountain View, CA")
	 * into geographic coordinates (like latitude 37.423021 and longitude -122.083739), which you can
	 * use to place markers on a map, or position the map. (Latitude/Longitude Lookup)
	 *
	 * @author		IneX
	 * @version		1.1
	 * @since		1.0 `12.06.2018` `IneX` Method added
	 * @since		1.1 `29.12.2022` `IneX` Updated to use $_ENV vars for GOOGLE_API_KEY & GOOGLE_MAP_API URL
	 *
	 * @uses GOOGLE_API_KEY, $_ENV
	 * @param string $address	A Postal Address string, which should be resolved to a Google Maps Object using the Geocoding API
	 * @return array|null		Returns either an Array representing the resolved Google Maps Object, or NULL if resolving or API request failed
	 */
	public function geocode($address)
	{
		if (!empty(GOOGLE_API_KEY))
		{
			$googleGeocodingAPIrequest = $_ENV['GOOGLE_MAP_API'].'&address='.urlencode($address);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> %s', __METHOD__, __LINE__, $googleGeocodingAPIrequest));
			$request = file_get_contents($googleGeocodingAPIrequest);
			$response = get_object_vars(json_decode($request));
			if (DEVELOPMENT) error_log(sprintf("[DEBUG] <%s:%d> Google Geocoding API Response JSON:\n\r%s", __METHOD__, __LINE__, print_r($response,true)));
			if ($response['status']=='OK')
			{
				return [
							 'lat' => $response['results'][0]->geometry->location->lat
							,'lng' => $response['results'][0]->geometry->location->lng
						];
			} else {
				error_log(sprintf('[WARN] <%s:%d> Google Geocoding API Response Status: %s', __METHOD__, __LINE__, $response['status']));
				return NULL;
			}
		} else {
			error_log(sprintf('[WARN] <%s:%d> GOOGLE_API_KEY: invalid', __METHOD__, __LINE__));
			return NULL;
		}
	}
}

/** Instantiate new Google Maps API Class-Object */
$googleMapsApi = new GoogleMapsApi();
