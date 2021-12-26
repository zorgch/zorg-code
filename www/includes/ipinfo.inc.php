<?php
/**
 * IPinfo Integration
 *
 * Integriert mit der Vendor Library ipinfo/ipinfo und
 * ermöglicht die Abfrage von Geolocation Informationen
 * eines Users mittels dessen IP-Adresse. Benutzt den Service
 * von IPinfo.io und dazugehörigem IPinfo API Token Key.
 *
 * @link https://github.com/ipinfo/php
 *
 * @author		IneX
 * @package		zorg\Utils\IPinfo
 */
namespace Utils;

/**
 * Load the IPinfo library
 *
 * @include COMPOSER_AUTOLOAD Requires the Composer Autoloader Class
 */
use ipinfo\ipinfo\IPinfo;

if ( file_exists(COMPOSER_AUTOLOAD) ) require_once COMPOSER_AUTOLOAD;


/**
 * zorg User IP Infos Lookup Class
 *
 * In dieser Klasse befinden sich alle Funktionen zum Abfragen und Verarbeiten von Geolocation Informationen eins Users.
 *
 * @author		IneX
 * @package		zorg\Utils\IPinfo
 * @version		1.5
 * @since		1.0 `03.12.2021` `IneX` Initial integration
 * @since		1.5 `25.12.2021` `IneX` Added $_SESSION Caching to greatly reduce number of requests to IPinfo.io
 */
class zorgUserIPinfos
{
	/**
	 * Class constants and variables
	 *
	 * @const CACHE_MAXSIZE If the cache's max size is reached, cache values will be invalidated, starting with the oldest cached value. Default maximum cache size: 4096 bytes
	 * @const CACHE_TTL Time to live (TTL) of the cache means, that values will be cached for the specified duration. Default TTL is 24 hours (in seconds)
	 * @var bool $CacheDisabled It's possible to disable the cache by passing a "cache_disabled" key. By default it's disabled on DEVELOPMENT only.
	 * @var string $fallback_last_ip Placeholder String for $_SESSION['last_ip'] when IP was not resolvable
	 * @var string $fallback_country Fallback ISO2-Country
	 * @var string $fallback_country_name Fallback Country Name
	 * @var float $fallback_latitude Fallback location latitude St. Gallen, Switzerland (47.426418, 9.376010)
	 * @var float $fallback_longitude Fallback location longitude St. Gallen, Switzerland (47.426418, 9.376010)
	 * @var array $IPinfoSettings Static setting values to configure the IPinfo requests
	 * @var object $IPinfoClient IPinfo Object
	 * @var string $UserIPaddress Stores the IP Address of the User
	 * @var object $UserIPdetailsData User IP Infos Object
	 */
	private const CACHE_MAXSIZE = 4096; // Multiples of 2 are recommended to increase efficiency
	private const CACHE_TTL = 86400; // In seconds. Default: 24 hours = 24*60*60 = 86400
	private static $CacheDisabled = (DEVELOPMENT === true ? true : false);
	private $fallback_last_ip = 'PRIV_OR_RES_RANGE';
	private $fallback_country = 'CH';
	private $fallback_country_name = 'Switzerland';
	private $fallback_latitude = 47.426418;
	private $fallback_longitude = 9.376010;
	private $IPinfoSettings;
	private $IPinfoClient;
	private $UserIPaddress;
	private $UserIPdetailsData;

	/**
	 * Class Constructor
	 *
	 * Example of retrieved Details for an IP-Address:
	 *	{
	 *		'asn': {     'asn': 'AS20001',
	 *					 'domain': 'twcable.com',
	 *					 'name': 'Time Warner Cable Internet LLC',
	 *					 'route': '104.172.0.0/14',
	 *					 'type': 'isp' },
	 *		'city': 'Los Angeles',
	 *		'company': { 'domain': 'twcable.com',
	 *					 'name': 'Time Warner Cable Internet LLC',
	 *					 'type': 'isp'},
	 *		'country': 'US',
	 *		'country_name': 'United States',
	 *		'hostname': 'cpe-104-175-221-247.socal.res.rr.com',
	 *		'ip': '104.175.221.247',
	 *		'loc': '34.0293,-118.3570',
	 *		'latitude': '34.0293',
	 *		'longitude': '-118.3570',
	 *		'phone': '323',
	 *		'postal': '90016',
	 *		'region': 'California'
	 *	}
	 *
	 * @uses IPINFO_API_KEY
	 */
	public function __construct()
	{
		/**
		 * Fetch User's IP-details and resolve it's associated Data
		 * NOTE: Could be empty (null) when IP like 127.0.0.1 / ::1!
		 */
		$this->UserIPaddress = $this->getRealIPaddress();

		/** Usersession "Caching" check (to not trigger another IPinfo.io request, if not needed) */
		$weHazDatazAlready = $this->getDataFromSession((!empty($this->UserIPaddress) ? $this->UserIPaddress : $this->fallback_last_ip));

		/** Cache? No no no... */
		if ($weHazDatazAlready !== true)
		{
			try {
				/** Configure & instantiate the IPinfo Client */
				$this->IPinfoSettings = [ 'cache_disabled' => self::$CacheDisabled
									 	,'cache_maxsize' => self::CACHE_MAXSIZE
									 	,'cache_ttl' => self::CACHE_TTL ];
				$this->IPInfoClient = new IPinfo(IPINFO_API_KEY, $this->IPinfoSettings);
				$this->UserIPdetailsData = $this->IPInfoClient->getDetails($this->UserIPaddress);

				/** Make sure IPinfo reply is not bogon (e.g. 127.0.0.1 = bogon / missing data) */
				if (!isset($this->UserIPdetailsData->bogon) || $this->UserIPdetailsData->bogon !== 1)
				{
					if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> %s => %s', __METHOD__, __LINE__, $this->UserIPaddress, print_r((array)$this->UserIPdetailsData,true)));

					/** Store IP Data Values to Usersession to reduce additional hits to IPinfo.io */
					$this->storeUserIPToSession();
					$this->storeUserIPDetailsToSession();
				} else {
					throw new Exception('Bogus IP address.');
				}
			}
			catch (\Exception $e) {
				/**
				 * Exceptions usually occur for legit reasons. But still we need to satisfy certain requests / responses
				 */
				switch ($e->getMessage())
				{
					/** HTTP 429 - Quota exceeded */
					case 'IPinfo request quota exceeded.':
						error_log(sprintf('[ERROR] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
						break;

					/** HTTP 400 - Bad request (Referrer limitation) */
					case 'Exception: {"status":400,"reason":"Bad Request"}':
						error_log(sprintf('[ERROR] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
						try {
							error_log(sprintf('[INFO] <%s:%d> %s', __METHOD__, __LINE__, 'Trying to initialize new IPinfo without API Token Key...'));
							$this->IPInfoClient = new IPinfo(null, $this->IPinfoSettings); // Fallback without API Token
						} catch (\Exception $e) {
							error_log(sprintf('[ERROR] <%s:%d> Persistent IPinfo Error: %s', __METHOD__, __LINE__, $e->getMessage($this->UserIPdetailsData)));
						}
						break;

					/** Invalid IP address (not resolvable on IPinfo.io) */
					case 'Bogus IP address.':
						error_log(sprintf('[ERROR] <%s:%d> %s %s %s', __METHOD__, __LINE__, $e->getMessage(), "\n", print_r((array)$this->UserIPdetailsData,true)));
						break;

					/** Any other exception... */
					default:
						error_log(sprintf('[ERROR] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
				}
				//exit;
			}
		} elseif (DEVELOPMENT === true) {
			error_log(sprintf('[DEBUG] <%s:%d> IPinfo getDataFromSession(%s): SESSION CACHE HIT!', __METHOD__, __LINE__, $this->UserIPaddress));
		}
	}

	/**
	 * Get the real (external) IP address of the User
	 *
	 * @link https://www.benmarshall.me/get-ip-address/
	 *
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 `29.09.2019` `IneX` function added
	 * @since 2.0 `03.12.2021` `IneX` function moved from util.inc.php & refactored with more robust code (supports ipv4 + ipv6)
	 *
	 * @global object $user (UNUSED) Globales Class-Object mit den User-Methoden & Variablen
	 * @return string|void Returns a string containing the Clients real IP address, or 'null' to trigger fallback to Server's IP
	 */
	private function getRealIPaddress()
	{
		foreach(['HTTP_CLIENT_IP'
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
					$ip_address = trim($ip_address);

					/**
					 * Filters explained:
					 * - FILTER_FLAG_NO_PRIV_RANGE: no private IPv4 ranges like 10.0.0.0, 172.16.0.0 & 192.168.0.0/16
					 * - FILTER_FLAG_NO_RES_RANGE: no private IPv4/IPv6 like: 127.0.0.0, ::1, etc.
					 */
					if (false !== filter_var($ip_address, FILTER_VALIDATE_IP, ['flags' => FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE]))
					{
						/** On successful validation */
						return $ip_address;
					}
					else {
						/** On filter error (false) */
						return null;
					}
				}
			}
		}
	}

	/**
	 * Get the User's Country Location Name ("Switzerland") based on IP-Address
	 *
	 * The response object includes a Details->country_name attribute which includes the country name based on American English.
	 * It is possible to return the country name in other languages by setting the countries_file keyword argument when creating the IPinfo object.
	 *
	 * @param string $localize (TODO) Locale to use for the language dependent Country name translation
	 * @return string Country name in American English, e.g.: "Switzerland"
	 */
	public function getCountryName($localize=null)
	{
		/** If $UserIPdetailsData is empty, use Fallback */
		return (is_object($this->UserIPdetailsData) && false !== $this->UserIPdetailsData ? $this->UserIPdetailsData->country_name : $this->fallback_country_name);
	}

	/**
	 * Get the coordinates associated with IP of User
	 *
	 * Using the latitude+longitude values from the IPinfo response:
	 * 		'loc': '34.0293,-118.3570',
	 *		'latitude': '34.0293',
	 *		'longitude': '-118.3570',
	 *
	 * @return array Array containing 2 elements: latitude + longitude
	 */
	public function getCoordinates()
	{
		/** If $UserIPdetailsData is empty, use Fallback */
		$IPinfoLat = (is_object($this->UserIPdetailsData) && false !== $this->UserIPdetailsData && !empty($this->UserIPdetailsData->latitude) ? $this->UserIPdetailsData->latitude : $this->fallback_latitude);
		$IPinfoLon = (is_object($this->UserIPdetailsData) && false !== $this->UserIPdetailsData && !empty($this->UserIPdetailsData->longitude) ? $this->UserIPdetailsData->longitude : $this->fallback_longitude);
		$coordinates = ['latitude' => $IPinfoLat, 'longitude' => $IPinfoLon];

		return $coordinates;
	}

	/**
	 * Get the User's Country Location Info (ISO2-Code) based on IP-Address
	 *
	 * @return string 2-char Country ISO Code, e.g.: "CH"
	 */
	public function getCountryIso2Code()
	{
		/** If $UserIPdetailsData is empty, use Fallback */
		return (is_object($this->UserIPdetailsData) && false !== $this->UserIPdetailsData ? $this->UserIPdetailsData->country : $this->fallback_country);
	}

	/**
	 * Convert a Country ISO2-Code to it's corresponding ISO3-Code
	 *
	 * @link http://country.io/data/ (Source of data, converted from JSON to PHP Array)
	 *
	 * @param string $countryIso2Code A valid ISO2 Country Code, e.g. "CH"
	 * @return string|bool 3-char Country ISO Code, e.g.: "CHE"; or false if not found.
	 */
	public function getCountryIso3Code($countryIso2Code)
	{
		if (empty($countryIso2Code) || strlen($countryIso2Code) !== 2 || !is_string($countryIso2Code)) return false;

		$mappingListIso2toIso3 = ["BD" => "BGD", "BE" => "BEL", "BF" => "BFA", "BG" => "BGR", "BA" => "BIH", "BB" => "BRB", "WF" => "WLF", "BL" => "BLM", "BM" => "BMU", "BN" => "BRN", "BO" => "BOL", "BH" => "BHR", "BI" => "BDI", "BJ" => "BEN", "BT" => "BTN", "JM" => "JAM", "BV" => "BVT", "BW" => "BWA", "WS" => "WSM", "BQ" => "BES", "BR" => "BRA", "BS" => "BHS", "JE" => "JEY", "BY" => "BLR", "BZ" => "BLZ", "RU" => "RUS", "RW" => "RWA", "RS" => "SRB", "TL" => "TLS", "RE" => "REU", "TM" => "TKM", "TJ" => "TJK", "RO" => "ROU", "TK" => "TKL", "GW" => "GNB", "GU" => "GUM", "GT" => "GTM", "GS" => "SGS", "GR" => "GRC", "GQ" => "GNQ", "GP" => "GLP", "JP" => "JPN", "GY" => "GUY", "GG" => "GGY", "GF" => "GUF", "GE" => "GEO", "GD" => "GRD", "GB" => "GBR", "GA" => "GAB", "SV" => "SLV", "GN" => "GIN", "GM" => "GMB", "GL" => "GRL", "GI" => "GIB", "GH" => "GHA", "OM" => "OMN", "TN" => "TUN", "JO" => "JOR", "HR" => "HRV", "HT" => "HTI", "HU" => "HUN", "HK" => "HKG", "HN" => "HND", "HM" => "HMD", "VE" => "VEN", "PR" => "PRI", "PS" => "PSE", "PW" => "PLW", "PT" => "PRT", "SJ" => "SJM", "PY" => "PRY", "IQ" => "IRQ", "PA" => "PAN", "PF" => "PYF", "PG" => "PNG", "PE" => "PER", "PK" => "PAK", "PH" => "PHL", "PN" => "PCN", "PL" => "POL", "PM" => "SPM", "ZM" => "ZMB", "EH" => "ESH", "EE" => "EST", "EG" => "EGY", "ZA" => "ZAF", "EC" => "ECU", "IT" => "ITA", "VN" => "VNM", "SB" => "SLB", "ET" => "ETH", "SO" => "SOM", "ZW" => "ZWE", "SA" => "SAU", "ES" => "ESP", "ER" => "ERI", "ME" => "MNE", "MD" => "MDA", "MG" => "MDG", "MF" => "MAF", "MA" => "MAR", "MC" => "MCO", "UZ" => "UZB", "MM" => "MMR", "ML" => "MLI", "MO" => "MAC", "MN" => "MNG", "MH" => "MHL", "MK" => "MKD", "MU" => "MUS", "MT" => "MLT", "MW" => "MWI", "MV" => "MDV", "MQ" => "MTQ", "MP" => "MNP", "MS" => "MSR", "MR" => "MRT", "IM" => "IMN", "UG" => "UGA", "TZ" => "TZA", "MY" => "MYS", "MX" => "MEX", "IL" => "ISR", "FR" => "FRA", "IO" => "IOT", "SH" => "SHN", "FI" => "FIN", "FJ" => "FJI", "FK" => "FLK", "FM" => "FSM", "FO" => "FRO", "NI" => "NIC", "NL" => "NLD", "NO" => "NOR", "NA" => "NAM", "VU" => "VUT", "NC" => "NCL", "NE" => "NER", "NF" => "NFK", "NG" => "NGA", "NZ" => "NZL", "NP" => "NPL", "NR" => "NRU", "NU" => "NIU", "CK" => "COK", "XK" => "XKX", "CI" => "CIV", "CH" => "CHE", "CO" => "COL", "CN" => "CHN", "CM" => "CMR", "CL" => "CHL", "CC" => "CCK", "CA" => "CAN", "CG" => "COG", "CF" => "CAF", "CD" => "COD", "CZ" => "CZE", "CY" => "CYP", "CX" => "CXR", "CR" => "CRI", "CW" => "CUW", "CV" => "CPV", "CU" => "CUB", "SZ" => "SWZ", "SY" => "SYR", "SX" => "SXM", "KG" => "KGZ", "KE" => "KEN", "SS" => "SSD", "SR" => "SUR", "KI" => "KIR", "KH" => "KHM", "KN" => "KNA", "KM" => "COM", "ST" => "STP", "SK" => "SVK", "KR" => "KOR", "SI" => "SVN", "KP" => "PRK", "KW" => "KWT", "SN" => "SEN", "SM" => "SMR", "SL" => "SLE", "SC" => "SYC", "KZ" => "KAZ", "KY" => "CYM", "SG" => "SGP", "SE" => "SWE", "SD" => "SDN", "DO" => "DOM", "DM" => "DMA", "DJ" => "DJI", "DK" => "DNK", "VG" => "VGB", "DE" => "DEU", "YE" => "YEM", "DZ" => "DZA", "US" => "USA", "UY" => "URY", "YT" => "MYT", "UM" => "UMI", "LB" => "LBN", "LC" => "LCA", "LA" => "LAO", "TV" => "TUV", "TW" => "TWN", "TT" => "TTO", "TR" => "TUR", "LK" => "LKA", "LI" => "LIE", "LV" => "LVA", "TO" => "TON", "LT" => "LTU", "LU" => "LUX", "LR" => "LBR", "LS" => "LSO", "TH" => "THA", "TF" => "ATF", "TG" => "TGO", "TD" => "TCD", "TC" => "TCA", "LY" => "LBY", "VA" => "VAT", "VC" => "VCT", "AE" => "ARE", "AD" => "AND", "AG" => "ATG", "AF" => "AFG", "AI" => "AIA", "VI" => "VIR", "IS" => "ISL", "IR" => "IRN", "AM" => "ARM", "AL" => "ALB", "AO" => "AGO", "AQ" => "ATA", "AS" => "ASM", "AR" => "ARG", "AU" => "AUS", "AT" => "AUT", "AW" => "ABW", "IN" => "IND", "AX" => "ALA", "AZ" => "AZE", "IE" => "IRL", "ID" => "IDN", "UA" => "UKR", "QA" => "QAT", "MZ" => "MOZ"];

		/** Search data */
		$iso3CountryCode = (array_key_exists($countryIso2Code, $mappingListIso2toIso3) ? $mappingListIso2toIso3[$countryIso2Code] : false);

		return $iso3CountryCode;
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
	private function storeUserIPToSession()
	{
		/** Push Data to $_SESSION */
		$_SESSION['last_ip'] = (!empty($this->UserIPaddress) ? $this->UserIPaddress : $this->fallback_last_ip);
	}

	/**
	 * Store the User's IP-Location Details to the Usersession
	 *
	 * Adds the following values:
	 * - $_SESSION['UserIPdetailsData']
	 *
	 * @return void
	 */
	private function storeUserIPDetailsToSession()
	{
		/** Convert IPinfo User Details Object to Array */
		$UserIPdetailsDataToStore = (array)$this->UserIPdetailsData;

		/** Push Data to $_SESSION */
		$_SESSION['UserIPdetailsData'] = $UserIPdetailsDataToStore;
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
		if (isset($_SESSION['last_ip']))
		{
			/** Data is up to date (IP has not changed) */
			if ($_SESSION['last_ip'] === $ip_to_compare)
			{
				/** UserIPdetailsData */
				if (isset($_SESSION['UserIPdetailsData']) && is_array($_SESSION['UserIPdetailsData']))
				{
					/**
					* Convert Array from Session to IPinfo User Details Object
					* first cast the array to stdClass, then change the class name
					*/
					/* $className = 'UserIPdetailsData';
					$objData = serialize((object)$_SESSION['UserIPdetailsData']);
					$convertedObjectToClass = str_replace(
						'O:8:"stdClass"',
						'O:'.strlen($className).':"'.$className.'"',
						$objData
					);
					unset($objData);
					$this->UserIPdetailsData = unserialize($convertedObjectToClass);
					*/
					$this->UserIPdetailsData = (object)$_SESSION['UserIPdetailsData'];

					return true;
				}
			}
		}
		/** If IP has changed, or UserIPdetails missing, return false */
		return false;
	}
}
