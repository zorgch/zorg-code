<?php
/**
 * zorg Sitemap.xml
 *
 *
 * @link https://www.sitemaps.org/protocol.html#sitemapXMLExample
 *
 * @author IneX
 * @package zorg\Sitemap
 * @version 1.0
 * @since 1.0 `07.12.2019` `IneX` File added
 *
 * @TODO add Smarty-Caching for better performance before re-calculating Sitemap.xml
 */

/**
 * File includes
 * @include core.controller.php Required
 */
require_once dirname(__FILE__).'/controller/core.controller.php';

/** [DEBUG] Start execution time measurement (total) */
if (DEVELOPMENT === true) $timerStart = microtime(true);

/**
 * Initialise MVC Controller
 */
$sitemap = new MVC\Controller\Sitemap();

/**
 * Sitemap output
 */
echo $sitemap->output();

/** [DEBUG] Execution time (total) */
if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Sitemap parsed within %g sec', __FILE__, __LINE__, microtime(true)-$timerStart));
