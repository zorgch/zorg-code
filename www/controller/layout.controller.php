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
 * @include usersystem.inc.php Includes the Usersystem Class and Methods
 * @include util.inc.php Includes the Helper Utilities Class and Methods
 * @include sunrise.inc.php Required to calculate Sunrise & Sunset Times
 * @include geo2ip.inc.php Required to retrieve User Geolocation data (IP, Country, Latitude/Longitude, Timezone)
 */
require_once CONTROLLERS_DIR.'core.controller.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'util.inc.php';
require_once INCLUDES_DIR.'sunrise.inc.php';
require_once INCLUDES_DIR.'geo2ip.inc.php';

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
	 * @var string $country Country code, like "che"
	 * @var string $country_code Country code, like "CHE"
	 * @var string $country_flagicon Public accessible path to Country Flag Icon file
	 * @var string $sunset Time in hh:mm of next sunset
	 * @var string $sunrise Time in hh:mm of next sunrise
	 * @var string $sun Current Sun state: up / down. Default: up
	 * @var string $layouttype Layout to use (based on $sun): night / day. Default: day
	 */
	public $country;
	public $country_code;
	public $country_flagicon;
	public $sunset;
	public $sunrise;
	public $sun = 'up';
	public $layouttype = 'day';

	/**
	 * Class Constructor
	 *
	 * @version 1.0
	 * @since 1.0 `03.12.2021` `IneX` method added
	 */
	public function __construct()
	{
		/** Position vom user bestimmen */
		\zorgDebugger::log()->debug('New \Utils\IP2Geolocation()');
		$userLocationData = new \Utils\User\IP2Geolocation();

		/** Assign user location vars */
		$this->country = $userLocationData->getCountryName();
		$this->country_code = \convertToCountryIso3($userLocationData->getGeoCountryIso2Code());
		$this->setSunriseSunsetDayNight($userLocationData->getCoordinates());
		$this->setCountryFlagicon($this->country_code);

		/** Set the Colors of zorg */
		$this->setColors();
	}

	/**
	 * Sunrise, Sunset, Day & Night
	 *
	 * @uses \Utils\User\IP2Geolocation(), Astro_Sunrise()
	 * @param array $LatLonCoordinates Array containing a latitude + longitude element
	 */
	private function setSunriseSunsetDayNight($LatLonCoordinates)
	{
		/** Validate Param */
		if (!is_array($LatLonCoordinates)
				|| !isset($LatLonCoordinates['latitude']) || !isset($LatLonCoordinates['longitude'])
				|| !is_numeric($LatLonCoordinates['latitude']) || !is_numeric($LatLonCoordinates['longitude']))
		{
			/* Fallback: St. Gallen, Switzerland (int)47.426418, (int)9.376010 */
			$LatLonCoordinates = ['latitude' => 47, 'longitude' => 9];
		}

		$suncalc = new \Layout\Astro_Sunrise();
		$suncalc->setCoords((int)$LatLonCoordinates['latitude'], (int)$LatLonCoordinates['longitude']);
		$timezoneOffset = round($LatLonCoordinates['longitude']/15.0+date('I')); // time zones at every 15° (earth has 360° = 24 perfect time zones). + Summer/Winter-Time offset.
		$suncalc->setTimezone((int)$timezoneOffset);
		$suncalc->setTimestamp((int)(time()+(3600*$timezoneOffset)));
		$this->sunrise = $suncalc->getSunrise();
		$this->sunset = $suncalc->getSunset();
		$sunrise_timestamp = strtotime($this->sunrise); // Converts hh:mm time to a UNIX timestamp
		$sunset_timestamp = strtotime($this->sunset); // Converts hh:mm time to a UNIX timestamp
		$cur_timestamp = (int)(time()+(3600*$timezoneOffset)-3600);

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
		\zorgDebugger::log()->debug('Color Layout: %s', [$this->layouttype]);

		/** Background colors */
		if (!defined('BACKGROUNDCOLOR')) define('BACKGROUNDCOLOR', ($this->layouttype === 'day' ? '#F2F2F2' : '#141414'));
		if (!defined('TABLEBACKGROUNDCOLOR')) define('TABLEBACKGROUNDCOLOR', ($this->layouttype === 'day' ? '#DDDDDD' : '#242424'));
		if (!defined('BORDERCOLOR')) define('BORDERCOLOR', ($this->layouttype === 'day' ? '#CCCCCC' : '#CBBA79'));
		if (!defined('HEADERBACKGROUNDCOLOR')) define('HEADERBACKGROUNDCOLOR', ($this->layouttype === 'day' ? '#FFFFFF' : '#000000'));

		/** Forum */
		if (!defined('NEWCOMMENTCOLOR')) define('NEWCOMMENTCOLOR', ($this->layouttype === 'day' ? '#8D9FE5' : '#72601A'));
		if (!defined('OWNCOMMENTCOLOR')) define('OWNCOMMENTCOLOR', ($this->layouttype === 'day' ? '#9FE58D' : '#601A72'));
		if (!defined('FAVCOMMENTCOLOR')) define('FAVCOMMENTCOLOR', ($this->layouttype === 'day' ? '#E58D9F' : '#A2601A'));
		if (!defined('IGNORECOMMENTCOLOR')) define('IGNORECOMMENTCOLOR', ($this->layouttype === 'day' ? '#E55842' : '#C91B12'));

		/** Text colors */
		if (!defined('FONTCOLOR')) define('FONTCOLOR', ($this->layouttype === 'day' ? '#000000' : '#FFFFFF'));
		if (!defined('LINKCOLOR')) define('LINKCOLOR', ($this->layouttype === 'day' ? '#344586' : '#CBBA79'));

		/** Menu Colors */
		if (!defined('MENUCOLOR1')) define('MENUCOLOR1', ($this->layouttype === 'day' ? '#BDCFF5' : '#42300A'));
		if (!defined('MENUCOLOR2')) define('MENUCOLOR2', ($this->layouttype === 'day' ? '#9DAFD5' : '#62502A'));

		/** Form Inputs */
		if (!defined('IFC')) define('IFC', ($this->layouttype === 'day' ? '#000000' : '#FFFFFF'));
		if (!defined('IBG')) define('IBG', ($this->layouttype === 'day' ? '#FFFFFF' : '#000000'));

		/** Table Colors */
		if (!defined('TABLEBGCOLOR')) define('TABLEBGCOLOR', ($this->layouttype === 'day' ? '#E5E5E5' : '#141414'));

		/** hunting z  */
		if (!defined('HZ_BG_COLOR')) define('HZ_BG_COLOR', '#C8C8C8');

		/**
		 * Define generic colors & sizes (used in day & night layouts)
		 */
		if (!defined('BODYBACKGROUNDCOLOR')) define('BODYBACKGROUNDCOLOR', '#000000');
		if (!defined('HIGHLITECOLOR')) define('HIGHLITECOLOR', '#FF0000');
		if (!defined('TABLEBORDERC')) define('TABLEBORDERC', BORDERCOLOR);
		if (!defined('FORUMWIDTH')) define('FORUMWIDTH', '100%');

		/**
		 * Promote some zorg Colors for Smarty
		 *
		 * Allows using these color vars and color HEX-values within Smarty / Smarty Templates.
		 */
		if (!defined('SMARTY_COLORS'))
			define('SMARTY_COLORS', [
					'background'		=> BACKGROUNDCOLOR
					,'tablebackground'	=> TABLEBACKGROUNDCOLOR
					,'tableborder'		=> TABLEBORDERC
					,'border'			=> BORDERCOLOR
					,'font' 			=> FONTCOLOR
					,'header'			=> HEADERBACKGROUNDCOLOR
					,'link'				=> LINKCOLOR
					,'newcomment'		=> NEWCOMMENTCOLOR
					,'owncomment'		=> OWNCOMMENTCOLOR
					,'menu1'			=> MENUCOLOR1
					,'menu2'			=> MENUCOLOR2
			]);
	}
}
