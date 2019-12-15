<?php
/**
 * Sitemap MVC Model
 *
 * @author IneX
 * @package zorg\Sitemap
 */
namespace MVC;

/**
 * File includes
 * @include main.inc.php Required
 */
require_once(__DIR__ .'/../includes/main.inc.php');

/**
 * Class representing the MVC Model
 */
class Sitemap extends Model
{
	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 */
	public function __construct() { }

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_apods()
	{
		global $db;

		$sql = 'SELECT id FROM gallery_pics WHERE album = 41 AND zensur != "1"';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['id'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_books()
	{
		global $db;

		$sql = 'SELECT id FROM books';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['id'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_bugs()
	{
		global $db;

		$sql = 'SELECT id FROM bugtracker_bugs';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['id'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_events()
	{
		global $db;

		$sql = 'SELECT id, name, YEAR(startdate) as yyyy, MONTH(startdate) as mm, DAYOFMONTH(startdate) as dd FROM events';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = [
							 'id' => $rs['id']
							,'name' => $rs['name']
							,'year' => $rs['yyyy']
							,'month' => $rs['mm']
							,'day' => $rs['dd']
						];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_galleries() { $this->galleries = null; }

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_pages()
	{
		global $db;

		/** Template Pages */
		$sql = 'SELECT word FROM templates WHERE word != "" AND read_rights <= 0';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = [
							 'type' => 'word'
							,'name' => $rs['word']
						];
		}

		/** Static PHP Pages */
		$dataArray[] = [ 'type' => 'page', 'name' => 'addle' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'books' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'bugtracker' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'forum' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'fretsonzorg' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'gnsimu' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'go' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'hz_dwz' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'join' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'messagesystem' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'peter' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'pimp' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'quotes' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'seti_xml' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'spaceweather' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'stl' ];
		$dataArray[] = [ 'type' => 'page', 'name' => 'wetten' ];

		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_recipies()
	{
		global $db;

		$sql = 'SELECT id FROM rezepte';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['id'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_tauschartikel()
	{
		global $db;

		$sql = 'SELECT id FROM tauschboerse';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['id'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_templates()
	{
		global $db;

		$sql = 'SELECT id FROM templates WHERE word = "" AND read_rights <= 0 ORDER BY id';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['id'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_threads()
	{
		global $db;

		$sql = 'SELECT thread_id FROM comments_threads WHERE board = "f"';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['thread_id'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_users()
	{
		global $db;

		$sql = 'SELECT username FROM user WHERE lastlogin > 0 ORDER BY id';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['username'];
		}
		return $dataArray;
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 07.12.2019 method added
	 *
	 * @return void
	 */
	function load_wetten()
	{
		global $db;

		$sql = 'SELECT id FROM wetten';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while ($rs = $db->fetch($result))
		{
			$dataArray[] = $rs['id'];
		}
		return $dataArray;
	}
}
