<?php
/**
 * Layout MVC Controller
 *
 * @author IneX
 * @package zorg\Layout
 */
namespace MVC\Controller;

/**
 * File includes
 * @include core.controller.php Required MCV Controller Base-Class
 * @include sunrise.inc.php Required to calculate Sunrise & Sunset Times
 * @include ipinfo.inc.php Required to retrieve User Geolocation data (Country)
 * @include util.inc.php Includes the Helper Utilities Class and Methods
 */
require_once CONTROLLERS_DIR.'core.controller.php';
require_once INCLUDES_DIR.'sunrise.inc.php';
require_once INCLUDES_DIR.'ipinfo.inc.php';
require_once INCLUDES_DIR.'util.inc.php';

/**
 * Class representing the MVC Controller
 *
 * @uses PHP_IMAGES_DIR
 */
class Layout extends \MVC\Controller
{
	/**
	 * @const string COUNTRY_FLAGICONS_EXTENSION File extension of Country Flag icons
	 * @const string COUNTRY_FLAGICONS_DIR Directory path to the Country Flag icons
	 * @const string COUNTRY_FLAGICONS_DIR_PUBLIC Publicly accessible path to Country Flag icons
	 */
	private const COUNTRY_FLAGICONS_EXTENSION = 'png';
	private const COUNTRY_FLAGICONS_DIR = PHP_IMAGES_DIR.'country/flags';
	private const COUNTRY_FLAGICONS_DIR_PUBLIC = IMAGES_DIR.'country/flags';

	/**
	 * @var object $userLocationClass zorgUserIPinfos() Object containing User's location infos
	 * @var string $country Country code, like "che"
	 * @var string $country_code Country code, like "CHE"
	 * @var string $country_flagicon Public accessible path to Country Flag Icon file
	 * @var string $sunset Time in hh:mm of next sunset
	 * @var string $sunrise Time in hh:mm of next sunrise
	 * @var string $sun Current Sun state: up / down
	 * @var string $layouttype Layout to use (based on $sun): night / day. Default: day
	 */
	private $userLocationClass;
	public $country;
	public $country_code;
	public $country_flagicon;
	public $sunset;
	public $sunrise;
	public $sun;
	public $layouttype = 'day';

	/**
	 * Class Constructor
	 *
	 * @version 1.0
	 * @since 1.0 `03.12.2021` `IneX` method added
	 */
	public function __construct()
	{
		try {
			/** Position vom user bestimmen */
			$this->userLocationClass = new \Utils\zorgUserIPinfos();

			/** Assign user location vars */
			$this->country = $this->userLocationClass->getCountryName();
			$this->country_code = $this->userLocationClass->getCountryIso3Code($this->userLocationClass->getCountryIso2Code());
			$this->setSunriseSunsetDayNight($this->userLocationClass->getCoordinates());
			$this->setCountryFlagicon($this->country_code);

			/** Set the Colors of zorg */
			$this->setColors();
		}
		catch (\Exception $e) {
			error_log(sprintf('[ERROR] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
			exit;
		}
	}

	/**
	 * Sunrise, Sunset, Day & Night
	 *
	 * @uses zorgUserIPinfos(), Astro_Sunrise()
	 * @param array $LatLonCoordinates Array containing a latitude + longitude element
	 */
	private function setSunriseSunsetDayNight($LatLonCoordinates)
	{
		/** Validate Param */
		if (!is_array($LatLonCoordinates)
				|| !isset($LatLonCoordinates[0]) || !isset($LatLonCoordinates[1])
				|| !is_float($LatLonCoordinates[0]) || !is_float($LatLonCoordinates[1]))
		{
			/* Fallback: St. Gallen, Switzerland (47.426418, 9.376010) */
			$LatLonCoordinates = ['latitude' => 47.426418, 'longitude' => 9.376010];
		}

		$suncalc = new \Layout\Astro_Sunrise();
		$suncalc->setCoords($LatLonCoordinates['latitude'], $LatLonCoordinates['longitude']);
		$suncalc->setTimezone(round($LatLonCoordinates['longitude']/15.0)+date('I'));
		$suncalc->setTimestamp(time()+(3600*round($LatLonCoordinates['longitude']/15.0)+date('I')));
		$this->sunrise = $suncalc->getSunrise();
		$this->sunset = $suncalc->getSunset();
		$sunrise_timestamp = strtotime($this->sunrise); // Converts hh:mm time to a UNIX timestamp
		$sunset_timestamp = strtotime($this->sunset); // Converts hh:mm time to a UNIX timestamp
		$cur_timestamp = (time()+(3600*round($LatLonCoordinates['longitude']/15.0)+date('I'))-3600);

		if ($cur_timestamp > $sunrise_timestamp)
		{
			$this->sun = 'up';
			$this->layouttype = 'day';
		}
		if ($cur_timestamp > $sunset_timestamp		// 2x IF (not "else if") because
			|| $cur_timestamp < $sunrise_timestamp)	// Sunset/Night must overwrite Sunrise/Day!
		{
			$this->sun = 'down';
			$this->layouttype = 'night';
		}
		return true;
	}

	/**
	 * Country Flag Icon check
	 *
	 * @param string $CountryCode Case insensitive 3-char ISO Country Code, e.g. "CHE". Default: CHE
	 * @return string Public accessible path to Country Flag Icon file
	 */
	private function setCountryFlagicon($countryCode='CHE')
	{
		$imageFileCountryCode = (empty($countryCode) || strlen($countryCode) !== 3 || !is_string($countryCode) ? 'CHE' : $countryCode);
		$imageFileCountryCode = strtoupper($imageFileCountryCode); // Always use uppercase, because filenamess are in uppercase
		$countryFlagIconCheck = fileExists(sprintf('%s/%s.%s', self::COUNTRY_FLAGICONS_DIR, $imageFileCountryCode, self::COUNTRY_FLAGICONS_EXTENSION));

		// Wenn Land nicht ermittelt werden konnte, Fallback zu CHE
		$countryFlagIconPath = sprintf('%s/%s.%s',
										 self::COUNTRY_FLAGICONS_DIR_PUBLIC
										,(false !== $countryFlagIconCheck ? $imageFileCountryCode : 'CHE')
										,self::COUNTRY_FLAGICONS_EXTENSION);

		$this->country_flagicon = $countryFlagIconPath;
		return true;
	}

	/**
	 * Set the Colors of zorg
	 *
	 * @return void
	 */
	private function setColors()
	{
		/** Background colors */
		if (!defined('BACKGROUNDCOLOR')) define('BACKGROUNDCOLOR', ($this->layouttype = 'day' ? '#F2F2F2' : '#141414'));
		if (!defined('TABLEBACKGROUNDCOLOR')) define('TABLEBACKGROUNDCOLOR', ($this->layouttype = 'day' ? '#DDDDDD' : '#242424'));
		if (!defined('BORDERCOLOR')) define('BORDERCOLOR', ($this->layouttype = 'day' ? '#CCCCCC' : '#CBBA79'));
		if (!defined('HEADERBACKGROUNDCOLOR')) define('HEADERBACKGROUNDCOLOR', ($this->layouttype = 'day' ? '#FFFFFF' : '#000000'));

		/** Forum */
		if (!defined('NEWCOMMENTCOLOR')) define('NEWCOMMENTCOLOR', ($this->layouttype = 'day' ? '#8D9FE5' : '#72601A'));
		if (!defined('OWNCOMMENTCOLOR')) define('OWNCOMMENTCOLOR', ($this->layouttype = 'day' ? '#9FE58D' : '#601A72'));
		if (!defined('FAVCOMMENTCOLOR')) define('FAVCOMMENTCOLOR', ($this->layouttype = 'day' ? '#E58D9F' : '#A2601A'));
		if (!defined('IGNORECOMMENTCOLOR')) define('IGNORECOMMENTCOLOR', ($this->layouttype = 'day' ? '#E55842' : '#C91B12'));

		/** Text colors */
		if (!defined('FONTCOLOR')) define('FONTCOLOR', ($this->layouttype = 'day' ? '#000000' : '#FFFFFF'));
		if (!defined('LINKCOLOR')) define('LINKCOLOR', ($this->layouttype = 'day' ? '#344586' : '#CBBA79'));

		/** Menu Colors */
		if (!defined('MENUCOLOR1')) define('MENUCOLOR1', ($this->layouttype = 'day' ? '#BDCFF5' : '#42300A'));
		if (!defined('MENUCOLOR2')) define('MENUCOLOR2', ($this->layouttype = 'day' ? '#9DAFD5' : '#62502A'));

		/** Form Inputs */
		if (!defined('IFC')) define('IFC', ($this->layouttype = 'day' ? '#000000' : '#FFFFFF'));
		if (!defined('IBG')) define('IBG', ($this->layouttype = 'day' ? '#FFFFFF' : '#000000'));

		/** Table Colors */
		if (!defined('TABLEBGCOLOR')) define('TABLEBGCOLOR', ($this->layouttype = 'day' ? '#E5E5E5' : '#141414'));

		/** hunting z  */
		if (!defined('HZ_BG_COLOR')) define('HZ_BG_COLOR', '#C8C8C8');

		/**
		 * Define generic colors & sizes (used in day & night layouts)
		 */
		if (!defined('BODYBACKGROUNDCOLOR')) define('BODYBACKGROUNDCOLOR', '#000000');
		if (!defined('HIGHLITECOLOR')) define('HIGHLITECOLOR', '#FF0000');
		if (!defined('TABLEBORDERC')) define('TABLEBORDERC', BORDERCOLOR);
		if (!defined('FORUMWIDTH')) define('FORUMWIDTH', '100%');
	}
}
