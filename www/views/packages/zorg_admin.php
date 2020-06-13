<?php
/**
 * zorg Admin Scripts
 *
 * @link https://zorg.ch/tpl/93
 */
global $smarty, $db;

/** template force compile action */
if ($_GET['force_compile']) {
	$db->query('UPDATE templates SET force_compile="1"', __FILE__, __LINE__, 'SET force_compile');
	$smarty->assign('admin_state', 'Force recompile executed');
}

/** comment force compile infos */
$handle = opendir(SMARTY_COMPILE);
$comments = 0;
$comments_size = 0;
$tpls = 0;
$tpls_size = 0;
while (false !== ($file = readdir ($handle)))
{
	if (strstr($file, 'comments')) {
		$comments++;
		$comments_size += filesize(SMARTY_COMPILE.$file);
	} else if (strstr($file, 'tpl') || strstr($file, 'word')) {
		$tpls++;
		$tpls_size += filesize(SMARTY_COMPILE.$file);
	}
}
closedir($handle);
$smarty->assign('compiled_comments_found', $comments);
$smarty->assign('compiled_comments_size', $comments_size);
$smarty->assign('compiled_tpls_found', $tpls);
$smarty->assign('compiled_tpls_size', $tpls_size);

$e = $db->query('SELECT unix_timestamp(last_seen) date, count(*) anz
				FROM comments_threads
				WHERE last_seen!="000-00-00"
				GROUP BY last_seen
				ORDER BY last_seen DESC',
				__FILE__, __LINE__, 'SELECT FROM comments_threads'
);
$last_seen = array();
while ($d = $db->fetch($e)) {
	$last_seen[] = $d;
}
$smarty->assign('comments_last_seen', $last_seen);
$smarty->assign('comments_last_seen_del', THREAD_TPL_TIMEOUT);


/** disk quota */
$e = $db->query('SELECT sum(f.size) quota, u.username FROM files f, user u WHERE u.id=f.user GROUP BY user ORDER BY quota DESC', __FILE__, __LINE__, 'SELECT FROM files');
$quota = array();
while ($d = $db->fetch($e)) {
	array_push($quota, $d);
}
$smarty->assign('admin_quota', $quota);


/**
 * Smarty Cache clear
 *
 * You can clear all the cache files with the clearAllCache() function, or individual cache files and groups with the clearCache() function
 * - clear only cache for index.tpl: $smarty->clearCache('index.tpl')
 * - clear all caches with "sports" as the first cache_id group: $smarty->clearCache(null,'sports')
 * - clear the foo.tpl cache file with "sports|basketball" as the cache_id: $smarty->clearCache('foo.tpl','sports|basketball')
 *
 * @TODO Needs a Frontend in [/tpl/93](https://zorg.ch/tpl/93) (zorg Admin Page)! (IneX, 04.05.2020)
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `04.05.2020` `IneX` Function added
 *
 * @uses Smarty::clearCache()
 * @uses Smarty::clearAllCache()
 * @param string $_GET['clear_cache'] Boolean value must be 'true' in order to do something
 * @param string $_GET['(Cache Group)|all'] A string either with a valid Template name, Cache Group ID, or "all" to clear all caches
 * @return void Smarty HTML-Output
 */
if ($_GET['clear_cache'] === 'true' && isset($_GET['for']))
{
	$smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT); // Disable Caching Lifetime temporarily

	if ($_GET['for'] === 'all')
	{
		$smarty->clearAllCache();
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Success!', 'message' => 'All Smarty Caches cleared']);
	}
	elseif (isset($_GET['for']) && is_string($_GET['for']))
	{
		$smarty->clearCache((isset($_GET['tpl_name']) ? (string)$_GET['tpl_name'] : null), $_GET['for']);
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Success!', 'message' => 'Smarty Caches cleared for '.$_GET['for'].(isset($_GET['tpl_name']) ? ' of '.(string)$_GET['tpl_name'] : null)]);
	}
	elseif (isset($_GET['tpl_name']) && is_string($_GET['tpl_name']))
	{
		$smarty->clearCache((string)$_GET['tpl_name']);
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Success!', 'message' => 'Smarty Caches cleared for '.(string)$_GET['tpl_name']]);
	}
	else
	{
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'No Caches cleared', 'message' => 'Missing one or more parameters in order to clear any Template Caches. Try again.']);
	}

	$smarty->display('file:layout/elements/block_error.tpl');
}


/**
 * APOD manuell fetchen
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `17.03.2020` `IneX` Function added
 *
 * @uses get_apod()
 * @include apod.inc.php
 * @param string $_GET['apod_fetch'] Boolean value must be 'true' in order to do something
 * @param string $_GET['apod_date'] A valid date after June 16 1995, formatted as: yyyy-mm-dd (2018-08-06)
 * @return void Smarty HTML-Output
 */
if ($_GET['apod_fetch'] === 'true')
{
	require_once dirname(__FILE__).'/../includes/apod.inc.php';
	$fetch_date = (isset($_GET['apod_date']) && false !== strtotime($_GET['apod_date']) ? date('Y-m-d',strtotime($_GET['apod_date'])) : null);
	$fetch_result = get_apod($fetch_date);
	if ($fetch_result === true)
	{
		$newest_apod_id = get_apod_id(); // DB Query-Result Array Resource
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Success!', 'message' => 'APOD '.(!empty($fetch_date) ? 'für Datum '.$fetch_date.' ' : '').'fetched! Check it out: <a href="/gallery.php?show=pic&picID='.$newest_apod_id['id'].'">APOD #'.$newest_apod_id['id'].'</a>']);
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'APOD not fetched :(', 'message' => 'Check the error log for details, why the APOD could not be fetched.']);
	}
	$smarty->display('file:layout/elements/block_error.tpl');
}


/**
 * Zorg Code Doku neu generieren
 *
 * Ruft das Shell-Script auf, welches den Zorg Code neu parst<br><br>
 * <b>Update: Die Doku sollte NUR via SHELL generiert werden:</b>
 * cd /zooomclan/phpdocumentor
 * ./phpdocu -c zorgcode.ini
 *
 * @author IneX
 * @date 27.05.2009
 *
 * @deprecated
 */
//if ($_GET[doku_generieren]) (shell_exec('/phpdocumentor/scripts/zorgcode_parse.sh')) ? $smarty->assign("admin_state", "Zorg Code Doku neu generiert") : $smarty->assign("admin_state", "FEHLER!");
