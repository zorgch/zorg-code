<?php
/**
 * Forum MVC Model
 *
 * @author IneX
 * @package zorg\Forum
 */
namespace MVC;

/**
 * Class representing the Forum MVC Model
 */
class Forum extends Model
{
	public function __construct()
	{
		/** Menus sind für alle Forum-Seiten gleich */
		$this->menus = [ 'zorg' ];
	}

	/**
	 * Forum Overview
	 *
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showOverview(&$smarty)
	{
		$this->page_title = 'forum';
		$this->page_link = '/forum.php';//$_SERVER['PHP_SELF'];

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * Forum Thread
	 *
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showThread(&$smarty, $thread_id, $thread_title=null)
	{
		$this->page_title = (!empty($thread_title) ? text_width(remove_html($thread_title), 50, '', true, true) : 'thread #'.$thread_id);
		$this->page_link = '/thread/'.$thread_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * Forum Thread not found
	 *
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function threadNotFound(&$smarty)
	{
		$this->page_title = 'Thread not found';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * Edit Comment
	 *
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function editComment(&$smarty)
	{
		$this->page_title = 'commentedit';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * Comment Searchresults
	 *
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showSearch(&$smarty)
	{
		$this->page_title = 'commentsearch';
		$this->page_link = $_SERVER['PHP_SELF'].'?layout=search';

		$this->assign_model_to_smarty($smarty);
	}
}
