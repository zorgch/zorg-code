<?php
/**
 * Geo2IP Lookup Service
 *
 * Integriert mit der Vendor Library maxmind-db/reader und
 * ermöglicht das Auslesen von Geolocation Informationen
 * eines Users mittels dessen IP-Adresse. Benutzt die standalone
 * MaxMind GeoLite2 Datenbank (Client IP wird via apache2 an $_SERVER übergeben)
 *
 * @link https://www.maxmind.com/en/geoip2-city MaxMind GeoIp2-City database download
 * @link https://github.com/maxmind/GeoIP2-php#readme MaxMind PHP-implementation
 * @link https://dev.maxmind.com/geoip/docs/databases/city-and-country/#binary-databases GeoLite2-City structure
 *
 * @author		IneX
 * @package		zorg\Utils\Geo2IP
 */
namespace Utils\User;

/**
 * zorg User IP Infos Lookup Class
 *
 * In dieser Klasse befinden sich alle Funktionen zum Abfragen und Verarbeiten von Geolocation Informationen eins Users.
 *
 * @author		IneX
 * @package		zorg\Utils\Geo2IP
 * @version		2.0
 * @since		1.0 `03.12.2021` `IneX` Initial integration
 * @since		2.0 `28.05.2022` `IneX` Deprecated and removed IPinfo.io; replaced with MaxMind Geo2IP lookup values (passed via apache2)
 */
class IP2Geolocation
{
	/**
	 * Class constants and variables
	 *
	 * @var string $fallback_last_ip Placeholder String for $_SESSION['last_ip'] when IP was not resolvable
	 * @var string $fallback_country Fallback ISO2-Country
	 * @var string $fallback_country_name Fallback Country Name
	 * @var float $fallback_latitude Fallback location latitude St. Gallen, Switzerland (47.426418, 9.376010)
	 * @var float $fallback_longitude Fallback location longitude St. Gallen, Switzerland (47.426418, 9.376010)
	 * @var string $UserIPaddress Stores the IP Address of the User
	 * @var array $UserIPdetailsData User IP location information
	 */
	private string $fallback_last_ip = 'PRIV_OR_RES_RANGE';
	private string $fallback_country = 'CH';
	private string $fallback_country_name = 'Switzerland (fallback)';
	private float $fallback_latitude = 47.426418;
	private float $fallback_longitude = 9.376010;
	private string $UserIPaddress;
	private array $UserIPdetailsData;

	/**
	 * Class Constructor
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `28.05.2022` `IneX` Method added
	 *
	 * @uses self::$UserIPaddress, self::$UserIPdetailsData
	 * @uses self::getRealIPaddress(), self::setMaxmindIPDetails(), self::getDataFromSession(), self::storeUserIPToSession()
	 */
	public function __construct()
	{
		/**
		 * Fetch User's IP-details and resolve it's associated Data
		 * NOTE: Could be empty (null) when IP like 127.0.0.1 / ::1!
		 */
		$realIPaddress = $this->getRealIPaddress();
		$this->UserIPaddress = ($realIPaddress !== $this->fallback_last_ip ? $realIPaddress : $this->fallback_last_ip);

		/** Usersession "Caching" */
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $this->UserIPaddress => %s', __METHOD__, __LINE__, $this->UserIPaddress));
		$weHazDatazAlready = $this->getDataFromSession($this->UserIPaddress);

		/** Cache? No no no... */
		if (false === $weHazDatazAlready)
		{
			/**
			 * Store IP Data Values to Usersession
			 */
			$this->storeUserIPToSession($this->UserIPaddress);
			$this->setMaxmindIPDetails();
		} elseif (DEVELOPMENT === true) {
			error_log(sprintf('[DEBUG] <%s:%d> getDataFromSession(%s): SESSION CACHE HIT!', __METHOD__, __LINE__, $this->UserIPaddress));
		}
	}

	/**
	 * Get the real (external) IP address of the User
	 *
	 * @link https://www.benmarshall.me/get-ip-address/
	 *
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 `29.09.2019` `IneX` function added
	 * @since 2.0 `03.12.2021` `IneX` function moved from util.inc.php & refactored with more robust code (supports ipv4 + ipv6)
	 * @since 3.0 `28.05.2022` `IneX` method enhanced with primary check for IP in $_SERVER[MMDB_ADDR] assigned from apache2
	 *
	 * @uses self::$fallback_last_ip
	 * @uses self::validateIPaddress()
	 * @return string Returns a string containing the Client IP address, or Fallback IP data when no valid IP found
	 */
	private function getRealIPaddress()
	{
		/**
		 * Read all sorts of IP address values within $_SERVER vars
		 */
		foreach(['MMDB_ADDR' // Prio 1: IP assigned by mod_maxminddb.c in apache2
				,'HTTP_CLIENT_IP'
				,'HTTP_X_REAL_IP'
				,'HTTP_X_FORWARDED_FOR'
				,'HTTP_X_FORWARDED'
				,'HTTP_X_CLUSTER_CLIENT_IP'
				,'HTTP_FORWARDED_FOR'
				,'HTTP_FORWARDED'
				,'REMOTE_ADDR'
			] as $ServerVar)
		{
			if (array_key_exists($ServerVar, $_SERVER) === true)
			{
				foreach(explode(',', $_SERVER[$ServerVar]) as $ip_address)
				{
					/** Validate IP-Address from $_SERVER var */
					if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> %s => %s', __METHOD__, __LINE__, $ServerVar, $ip_address));
					$checked_IPaddress = $this->validateIPaddress((string)$ip_address);

					if (!empty($checked_IPaddress) && false !== $checked_IPaddress)
					{
						/** Successful IP validation */
						return $checked_IPaddress;
					}
				}
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> %s => %s', __METHOD__, __LINE__, $ip_address, (empty($checked_IPaddress) ? 'empty' : ($checked_IPaddress === false ? 'false' : $checked_IPaddress))));
			}
		}

		/** Use the Fallback IP Data */
		return $this->fallback_last_ip;
	}

	/**
	 * Validate IP Address
	 *
	 * Checks if valid IPv4 or IPv6 address, and not reserved or private address (like 127.0.0.1 / ::1)
	 *
	 * @param string $IPaddress The IP Address to validate
	 * @return string|bool If valid, (string)$IPaddress is returned - otherwise FALSE
	 */
	private function validateIPaddress(string $IPaddress)
	{
		/** Remove any unwanted white spaces */
		$check_IPaddress = trim($IPaddress);

		/**
		 * Filters explained:
		 * - FILTER_FLAG_NO_PRIV_RANGE: no private IPv4 ranges like 10.0.0.0, 172.16.0.0 & 192.168.0.0/16
		 * - FILTER_FLAG_NO_RES_RANGE: no private IPv4/IPv6 like: 127.0.0.0, ::1, etc.
		 */
		if (false !== filter_var($check_IPaddress, FILTER_VALIDATE_IP, ['flags' => FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE]))
		{
			/** Successful IP validation */
			return $check_IPaddress;
		}

		/** Invalid or denied IP address */
		return false;
	}

	/**
	 * MaxMind GeoLite2 IP Details lookup
	 *
	 * Example of retrieved Details from MaxMind MMDB for an IP-Address:
	 * 	$_SERVER['MMDB_ADDR'] = '104.175.221.247'
	 * 	$_SERVER['MMDB_INFO'] = 'result found'; (Not used)
	 * 	$_SERVER['LATITUDE'] = '34.0293'
	 * 	$_SERVER['LONGITUDE'] = '-118.3570'
	 * 	$_SERVER['REGION_CODE'] = 'SG' (Not used)
	 * 	$_SERVER['COUNTRY_CODE'] = 'CH'
	 * 	$_SERVER['COUNTRY_NAME'] = 'Switzerland'
	 * 	$_SERVER['CITY_NAME'] = 'St. Gallen' (Not used)
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `08.01.2022` `IneX` Method added
	 *
	 * @uses $_SERVER
	 * @uses self::$fallback_country, self::$fallback_country_name, self::$fallback_latitude, self::$fallback_longitude
	 *
	 * @return void
	 */
	private function setMaxmindIPDetails()
	{
		$latitude = (isset($_SERVER['LATITUDE']) && !empty($_SERVER['LATITUDE']) ? $_SERVER['LATITUDE'] : $this->fallback_latitude);
		$longitude = (isset($_SERVER['LONGITUDE']) && !empty($_SERVER['LONGITUDE']) ? $_SERVER['LONGITUDE'] : $this->fallback_longitude);
		$this->UserIPdetailsData = [
			 'ip' => (isset($_SERVER['MMDB_ADDR']) && !empty($_SERVER['MMDB_ADDR']) ? $_SERVER['MMDB_ADDR'] : null)
			,'country' => (isset($_SERVER['COUNTRY_CODE']) && !empty($_SERVER['COUNTRY_CODE']) ? $_SERVER['COUNTRY_CODE'] : $this->fallback_country)
			,'country_name' => (isset($_SERVER['COUNTRY_NAME']) && !empty($_SERVER['COUNTRY_NAME']) ? $_SERVER['COUNTRY_NAME'] : $this->fallback_country_name)
			,'latitude' => (float)$latitude
			,'longitude' => (float)$longitude
			,'loc' => sprintf('%F,%F', $latitude, $longitude)
		];
		$this->storeUserIPDetailsToSession($this->UserIPdetailsData);
	}

	/**
	 * Check for Data already stored in User's Session
	 *
	 * @param string The User's current IP address, serving as comparison if $_SESSION data is up-to-date
	 * @return bool Returns true if $_SESSION data was re-used, or false if $_SESSION data is outdated
	 */
	private function getDataFromSession($ip_to_compare)
	{
		/** Check if a last_ip entry is in $_SESSION (even if $this->fallback_last_ip) */
		if (session_status() === PHP_SESSION_ACTIVE
			&& isset($_SESSION['last_ip']) && $_SESSION['last_ip'] === $ip_to_compare)
		{
			/** Data is up to date (IP has not changed) */
			if (isset($_SESSION['UserIPdetailsData']) && is_array($_SESSION['UserIPdetailsData']))
			{
				$this->UserIPdetailsData = $_SESSION['UserIPdetailsData'];
				$this->storeUserIPDetailsToSession($_SESSION['UserIPdetailsData']);
				return true;
			}
		}
		/** If IP has changed, or UserIPdetails missing, return false */
		return false;
	}

	/**
	 * Store the User's IP to the Usersession
	 *
	 * Adds the following values:
	 * - $_SESSION['last_ip']
	 *
	 * @param string The IP address to store to $_SESSION
	 * @return void
	 */
	private function storeUserIPToSession(string $IPaddress)
	{
		/** Push Data to $_SESSION */
		$_SESSION['last_ip'] = $IPaddress;
	}

	/**
	 * Store the User's IP-Location Details to the Usersession
	 *
	 * Adds the following values:
	 * - $_SESSION['UserIPdetailsData']
	 *
	 * @param array Array Containing User IP Location data
	 * @return void
	 */
	private function storeUserIPDetailsToSession(array $UserIPdetailsDataToStore)
	{
		/** Push Data to $_SESSION */
		$_SESSION['UserIPdetailsData'] = $UserIPdetailsDataToStore;
	}

	/**
	 * Get the User's Country Location Name ("Switzerland") based on IP-Address
	 *
	 * The response object includes a Details->country_name attribute which includes the country name based on American English.
	 *
	 * @param string $localize (TODO) Locale to use for the language dependent Country name translation
	 * @return string Country name in American English, e.g.: "Switzerland"
	 */
	public function getCountryName($localize=null)
	{
		/** If $UserIPdetailsData is empty, use Fallback */
		return (is_array($this->UserIPdetailsData) && false !== $this->UserIPdetailsData ? $this->UserIPdetailsData['country_name'] : $this->fallback_country_name);
	}

	/**
	 * Get the coordinates associated with IP of User
	 *
	 * Merging coordinates from the latitude+longitude values:
	 * 		'loc': '34.0293,-118.3570',
	 *		'latitude': '34.0293',
	 *		'longitude': '-118.3570',
	 *
	 * @return array Array containing 2 elements: latitude + longitude
	 */
	public function getCoordinates()
	{
		/** If $UserIPdetailsData is empty, use Fallback */
		$IPlatitude = (is_array($this->UserIPdetailsData) && !empty($this->UserIPdetailsData['latitude']) ? $this->UserIPdetailsData['latitude'] : $this->fallback_latitude);
		$IPlongitude = (is_array($this->UserIPdetailsData) && !empty($this->UserIPdetailsData['longitude']) ? $this->UserIPdetailsData['longitude'] : $this->fallback_longitude);
		$coordinates = ['latitude' => (float)$IPlatitude, 'longitude' => (float)$IPlongitude];

		return $coordinates;
	}

	/**
	 * Get the User's Country Location Info (ISO2-Code) based on IP-Address
	 *
	 * @return string 2-char Country ISO Code, e.g.: "CH"
	 */
	public function getGeoCountryIso2Code()
	{
		/** If $UserIPdetailsData is empty, use Fallback */
		return (is_array($this->UserIPdetailsData) && false !== $this->UserIPdetailsData ? $this->UserIPdetailsData['country'] : $this->fallback_country);
	}
}
