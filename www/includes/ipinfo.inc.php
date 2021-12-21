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
 * @version		1.0
 * @since		1.0 `03.12.2021` `IneX` Initial integration
 */
class zorgUserIPinfos
{
	/**
	 * Class constants and variables
	 *
	 * @const CACHE_MAXSIZE If the cache's max size is reached, cache values will be invalidated, starting with the oldest cached value. Default maximum cache size: 4096 bytes
	 * @const CACHE_TTL Time to live (TTL) of the cache means, that values will be cached for the specified duration. Default TTL is 24 hours (in seconds)
	 * @var bool $CacheDisabled It's possible to disable the cache by passing a "cache_disabled" key. By default it's disabled on DEVELOPMENT only.
	 * @var array $IPinfoSettings Static setting values to configure the IPinfo requests
	 * @var object $IPinfoClient IPinfo Object
	 * @var string $UserIPaddress Stores the IP Address of the User
	 * @var object $UserIPdetailsData User IP Infos Object
	 */
	private const CACHE_MAXSIZE = 4096; // Multiples of 2 are recommended to increase efficiency
	private const CACHE_TTL = 86400; // In seconds. Default: 24 hours = 24*60*60 = 86400
	private static $CacheDisabled = (DEVELOPMENT === true ? true : false);
	private $IPinfoSettings;
	private $IPinfoClient;
	private $UserIPaddress;
	private $UserIPdetailsData;

	/**
	 * Class Constructor
	 *
	 * Example of retrieved Details for an IP-Address:
	 *	{
	 *	'asn': {  'asn': 'AS20001',
	 *				'domain': 'twcable.com',
	 *				'name': 'Time Warner Cable Internet LLC',
	 *				'route': '104.172.0.0/14',
	 *				'type': 'isp'},
	 *	'city': 'Los Angeles',
	 *	'company': {   'domain': 'twcable.com',
	 *					'name': 'Time Warner Cable Internet LLC',
	 *					'type': 'isp'},
	 *	'country': 'US',
	 *	'country_name': 'United States',
	 *	'hostname': 'cpe-104-175-221-247.socal.res.rr.com',
	 *	'ip': '104.175.221.247',
	 *	'loc': '34.0293,-118.3570',
	 *	'latitude': '34.0293',
	 *	'longitude': '-118.3570',
	 *	'phone': '323',
	 *	'postal': '90016',
	 *	'region': 'California'
	 *	}
	 *
	 * @uses IPINFO_API_KEY
	 */
	public function __construct()
	{
		try {
			/** Configure & instantiate the IPinfo Client */
			$this->IPinfoSettings = [ 'cache_disabled' => self::$CacheDisabled
									 ,'cache_maxsize' => self::CACHE_MAXSIZE
									 ,'cache_ttl' => self::CACHE_TTL ];
			$this->IPInfoClient = new IPinfo(IPINFO_API_KEY, $this->IPinfoSettings);

			/** Fetch User's IP-detials and resolve it's associated Data */
			$this->UserIPdetailsData = $this->IPInfoClient->getDetails();
			//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> %s', __METHOD__, __LINE__, print_r($this->UserIPdetailsData,true)));
		}
		catch (\Exception $e) {
			error_log(sprintf('[ERROR] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
			//exit;
		}
	}

	/**
	 * (DEPRECATED) Get the real (external) IP address of the User
	 *
	 * @link https://www.benmarshall.me/get-ip-address/
	 *
	 * @DEPRECATED Replaced by IPinfoClient::getDetails()
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `29.09.2019` `IneX` function added
	 * @since 2.0 `03.12.2021` `IneX` function moved from util.inc.php & refactored with more robust code (supports ipv4 + ipv6)
	 *
	 * @global object $user (UNUSED) Globales Class-Object mit den User-Methoden & Variablen
	 * @return string|bool Returns a string containing the Clients real IP address, or false if unknown/missing
	 */
	private function getRealIPaddress()
	{
		//global $user;

		/* @link https://stackoverflow.com/a/23111577/5750030 DEPRECATED
		if ($_SERVER['REMOTE_ADDR'] === '::1' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1') $public_ip = trim(shell_exec('dig +short myip.opendns.com @resolver1.opendns.com'));
		else $public_ip = $_SERVER['REMOTE_ADDR'];
		return (isset($public_ip) && !empty($public_ip) ? $public_ip : null);*/

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

					if ($ip_address !== '::1' && $ip_address !== '127.0.0.1')
					{
						if (false !== filter_var($ip_address, FILTER_VALIDATE_IP,
											 	['flags' => FILTER_FLAG_NO_PRIV_RANGE, FILTER_FLAG_NO_RES_RANGE, FILTER_NULL_ON_FAILURE]
												)
						) {
						// On successful validation
							return $ip_address; // Can also return NULL because of FILTER_NULL_ON_FAILURE
						}
						// On filter error (false)
						else {
							return null;
						}
					}
					// On local IP = error
					else {
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
		/** If $UserIPdetailsData is empty, use Fallback: fixed on Switzerland */
		return (!empty($this->UserIPdetailsData) || false !== $this->UserIPdetailsData ? $this->UserIPdetailsData->country_name : 'Switzerland');
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
		/** If $UserIPdetailsData is empty, use Fallback: St. Gallen, Switzerland (47.426418, 9.376010) */
		$IPinfoLat = (!empty($this->UserIPdetailsData) && false !== $this->UserIPdetailsData && !empty($this->UserIPdetailsData->latitude) ? $this->UserIPdetailsData->latitude : 47.426418);
		$IPinfoLon = (!empty($this->UserIPdetailsData) && false !== $this->UserIPdetailsData && !empty($this->UserIPdetailsData->longitude) ? $this->UserIPdetailsData->longitude : 9.376010);

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
		/** If $UserIPdetailsData is empty, use Fallback: fixed on Switzerland */
		return (!empty($this->UserIPdetailsData) || false !== $this->UserIPdetailsData ? $this->UserIPdetailsData->country : 'CH');
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
}
