<?php
/**
 * Sitemap MVC Controller
 *
 * @author IneX
 * @package zorg\Sitemap
 */
namespace MVC\Controller;

/**
 * File includes
 * @include core.model.php Required
 */
require_once dirname(__FILE__).'/../models/core.model.php';
use MVC; // Fix namespace reference compatibility for MVC Model

/**
 * Class representing the MVC Controller
 */
class Sitemap extends Controller
{
	/**
	 * @var integer $sitemapCacheTime Overall Cache lifetime for the Sitemap Template
	 * @var array $apodPages
	 * @var array $bookPages
	 * @var array $bugPages
	 * @var array $eventPages
	 * @var array $galleryPages
	 * @var array $contentPages
	 * @var array $rezeptPages
	 * @var array $tauschartikelPages
	 * @var array $templatePages
	 * @var array $threadPages
	 * @var array $userPages
	 * @var array $wettenPages
	 * @var array $filesList
	 */
	private $sitemapCacheTime;
	private $apodPages;
	private $bookPages;
	private $bugPages;
	private $eventPages;
	private $galleryPages;
	private $contentPages;
	private $rezeptPages;
	private $tauschartikelPages;
	private $templatePages;
	private $threadPages;
	private $userPages;
	private $wettenPages;
	private $filesList;

	/**
	 * Class Constructor
	 *
	 * @version 1.0
	 * @since 1.0 `07.12.2019` `IneX` method added
	 */
	public function __construct($cacheTime = 14400)
	{
		/** Set the Sitemap Template cache_lifetime in seconds. Default: 4 hours */
		$this->sitemapCacheTime = $cacheTime;

		/** Initialise MVC Model */
		$model = new MVC\Sitemap();

		/** Fetch Sitemap Contents from MVC Model */
		$this->apodPages = $this->process_apods($model->load_apods());
		$this->bookPages = $this->process_books($model->load_books());
		$this->bugPages = $this->process_bugs($model->load_bugs());
		$this->eventPages = $this->process_events($model->load_events());
		//$this->galleryPages = $this->process_gallerys($model->load_gallerys());
		$this->contentPages = $this->process_pages($model->load_pages());
		$this->rezeptPages = $this->process_recipies($model->load_recipies());
		$this->tauschartikelPages = $this->process_tauschartikel($model->load_tauschartikel());
		$this->templatePages = $this->process_templates($model->load_templates());
		$this->threadPages = $this->process_threads($model->load_threads());
		$this->userPages = $this->process_users($model->load_users());
		$this->wettenPages = $this->process_wetten($model->load_wetten());
		$this->filesList = $this->process_files($model->load_files());
	}

	/**
	 * Build a single URL element array
	 *
	 * @version 1.0
	 * @since 1.0 `07.12.2019` `IneX` method added
	 *
	 * @param string $url URL of the page. Must begin with the protocol (http/https) and end with a trailing slash if required. Must be less than 2,048 characters.
	 * @param string $lastmod (Optional) Use: YYYY-MM-DD - The date of last modification. Should be in W3C Datetime format (allows to omit time portion)
	 * @param string $changefreq (Optional) How frequently the page is likely to change. Valid values are: hourly, daily, weekly, monthly, yearly, never, always
	 * @return array
	 */
	private function sitemap_element($url, $lastmod = null, $changefreq = null)
	{
		if (strlen($lastmod) < 10) $lastmod = null;
		return ['url' => $url, 'lastmod' => $lastmod, 'changefreq' => $changefreq];
	}

	/**
	 * Encode String according to RFC 3986
	 *
	 * "all URLs (including the URL of your Sitemap) must be URL-escaped and encoded for readability by the web server on which they are located."
	 * @link https://www.php.net/rawurlencode Encode URL according to RFC 3986
	 *
	 * @version 1.0
	 * @since 1.0 `07.12.2019` `IneX` method added
	 *
	 * @param string $urlPath URL to encode
	 * @return string
	 */
	private function string_encode($urlPath)
	{
		return rawurlencode($urlPath);
	}

	/**
	 * XML 1.0 entities encoding
	 *
	 * "any data values (including URLs) must use entity escape codes for the characters listed in the table below."
	 * @link https://www.php.net/manual/en/function.htmlspecialchars.php ENT_XML1 - Handle code as XML 1.
	 *
	 * @version 1.0
	 * @since 1.0 `07.12.2019` `IneX` method added
	 *
	 * @param string $rawString Raw String to encode
	 * @return string
	 */
	private function xml_encode($rawString)
	{
		return htmlspecialchars($rawString, ENT_XML1);
	}

	/**
	 * Cache output with defined lifetime (using Smarty)
	 *
	 * @link https://www.smarty.net/docsv2/en/caching.tpl Doc on Smarty Caching
	 * @link https://www.smarty.net/docsv2/en/caching.multiple.caches.tpl Doc on Multiple Caches Per Page
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 * @FIXME Smarty Tpl-Cache is not working... Recheck after Smarty 3.x upgrade
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/partials/sitemap/url.tpl Template-File für cached Output
	 * @param array $tplData Data Items for Smarty Template
	 * @param string $dataId ID-String to assign to individual Smarty Template Cache
	 * @param integer $cacheTime (Optional) Set specific Cache lifetime for the rendered Smarty Template-Part. Default: 1 day = 86400
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string Fetched Smarty-Template output
	 */
	private function cache($tplData, $dataId, $cacheTime = 86400)
	{
		global $smarty;

		/** Set the Smarty cache_lifetime for the template (in seconds) */
		$smarty->caching = 2; // lifetime is per cache
		$smarty->cache_lifetime = $cacheTime;

		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> sitemap/url.tpl[%s] from cache: %s', __FILE__, __LINE__, $dataId, ($smarty->is_cached('layout/partials/sitemap/url.tpl', $dataId) ? 'TRUE' : 'FALSE')));
		if(!$smarty->is_cached('layout/partials/sitemap/url.tpl', $dataId))
		{
			/** No cached version available */
			$smarty->assign('sitemapItems', $tplData); // Assign the $tplData
		}
		//$smarty->display('file:layout/partials/sitemap/url.tpl', $dataId);
		return $smarty->fetch('file:layout/partials/sitemap/url.tpl', $dataId);
	}

	/**
	 * Merge and display Sitemap content
	 *
	 * @version 1.0
	 * @since 1.0 `07.12.2019` `IneX` method added
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/pages/sitemap.tpl Template Structure used for Sitemap XML
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string Complete Sitemap XML-Content
	 */
	public function output()
	{
		// TODO adapt this once Smarty 3.x Update is done
		//global $smarty;
		//$smarty->display('file:layout/pages/sitemap.tpl');

		/** Smarty 2.x workardound because {include...} not supporting individual "cache_id=..." */
		$xmlOutput = null;
		$xmlOutput .= '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
		$xmlOutput .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
			$xmlOutput .= $this->apodPages;
			$xmlOutput .= $this->bookPages;
			$xmlOutput .= $this->bugPages;
			$xmlOutput .= $this->eventPages;
			//$xmlOutput .= $this->galleryPages;
			$xmlOutput .= $this->contentPages;
			$xmlOutput .= $this->rezeptPages;
			$xmlOutput .= $this->tauschartikelPages;
			$xmlOutput .= $this->templatePages;
			$xmlOutput .= $this->threadPages;
			$xmlOutput .= $this->userPages;
			$xmlOutput .= $this->wettenPages;
			//$xmlOutput .= $this->filesList; DISABLED VORERST
		$xmlOutput .= '</urlset>';
		return $xmlOutput;
	}

	/**
	 * APOD Pics
	 * @link /gallery.php?show=pic&picID=[ID]
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 */
	private function process_apods($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/gallery.php?show=pic&amp;picID=%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Books
	 * @link /books.php?do=show&book_id=[ID]
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 */
	private function process_books($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/books.php?do=show&amp;book_id=%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Bugs
	 * @link /bug/736
	 *
	 * @version 1.0
	 * @since 1.0 `07.12.2019` `IneX` method added
	 *
	 * @param array $data
	 * @return array
	 */
	private function process_bugs($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/bug/%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', $element['reportedDate|assignedDate|resolvedDate|deniedDate']);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Events
	 * @link /event/2001/09/05/236
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 *
	 * @param array $data
	 * @return array
	 */
	private function process_events($dataArray)
	{
		foreach ($dataArray as $element)
		{
			$url = sprintf('%s/event/%s/%s/%s/%d',
								 SITE_URL
								,$this->string_encode($element['year'])
								,$this->string_encode($element['month'])
								,$this->string_encode($element['day'])
								,$this->string_encode($element['id'])
							);
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching
		return $tplOutput;
	}

	/**
	 * Content Pages
	 * @link /page/[Pagename]
	 * @link /[filename].php
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 *
	 * @param array $data
	 * @return array
	 */
	private function process_pages($dataArray)
	{
		$urlElements[] = $this->sitemap_element(SITE_URL); // Add Home root page
		foreach ($dataArray as $pageItem)
		{
			if ($pageItem['type'] === 'word') $url = sprintf('%s/page/%s', SITE_URL, $this->string_encode($pageItem['name']));
			elseif ($pageItem['type'] === 'page') $url = sprintf('%s/%s.php', SITE_URL, $this->string_encode($pageItem['name']));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Rezepte
	 * @link /page/Rezepte?rezept_id=[ID]
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 */
	private function process_recipies($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/page/Rezepte?rezept_id=%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Tauschartikel
	 *
	 * @FIXME link /page/tauschboerse?artikel_id=[ID] <== Redirect auf Artikel tut nicht
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 *
	 * @return string Relative path like: /tpl/191?artikel_id=[ID]
	 */
	private function process_tauschartikel($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/tpl/191?artikel_id=%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Template Pages
	 * @link /tpl/[ID]
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 *
	 * @param array $data
	 * @return array
	 */
	private function process_templates($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/tpl/%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Forum Threads
	 * @link /thread/[Thread-ID]
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 *
	 * @param array $data
	 * @return array
	 */
	private function process_threads($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/thread/%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * User Pages
	 * @link /user/[Username]
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 *
	 * @param array $data
	 * @return array
	 */
	private function process_users($dataArray)
	{
		foreach ($dataArray as $name)
		{
			$url = sprintf('%s/user/%s', SITE_URL, $this->string_encode($name));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Wetten
	 * @link /wetten.php?id=[ID]
	 *
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 */
	private function process_wetten($dataArray)
	{
		foreach ($dataArray as $id)
		{
			$url = sprintf('%s/wetten.php?id=%d', SITE_URL, $this->string_encode($id));
			//$lastmod = date('d.m.Y', ...);
			//$changefreq = ...
			$urlElements[] = $this->sitemap_element($url);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}

	/**
	 * Files
	 * @link /files/[USER-ID]/[FILENAME]
	 *
	 * @version 1.0
	 * @since 1.0 `19.12.2019` `IneX` method added
	 *
	 * @param array $data
	 * @return array
	 */
	private function process_files($dataArray)
	{
		foreach ($dataArray as $element)
		{
			$url = sprintf('%s/files/%d/%s',
								 SITE_URL
								,$this->string_encode($element['userid'])
								,$this->string_encode($element['filename'])
							);
			$lastmod = date('d.m.Y', $element['dateadded']);
			$changefreq = 'monthly';
			$urlElements[] = $this->sitemap_element($url, $lastmod, $changefreq);
		}
		$tplOutput = $this->cache($urlElements, __FUNCTION__); // Pass through Smarty for Caching

		return $tplOutput;
	}
}
