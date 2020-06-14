<?php
/**
 * Books MVC Model
 *
 * @author IneX
 * @package zorg\Books
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Books extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Books';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/books.php';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'user', 'books' ];
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showOverview(&$smarty)
	{
		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showBook(&$smarty, $book_id, $book_title)
	{
		$this->page_title = 'Book «'.$book_title.'»';
		$this->page_link = $this->page_link . '?do=show&book_id=' . $book_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showEdit(&$smarty, $book_id)
	{
		$this->page_title = 'Book #'.$book_id.' bearbeiten';
		$this->page_link = $this->page_link . '?do=edit&book_id=' . $book_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showAddnew(&$smarty)
	{
		$this->page_title = 'Neues Book hinzufügen';
		$this->page_link = $this->page_link . '?do=add';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showDelete(&$smarty, $book_id, $book_title)
	{
		$this->page_title = 'Book «'.$book_title.'» löschen';
		$this->page_link = $this->page_link . '?do=delete&book_id=' . $book_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showUserbooks(&$smarty, &$user, $user_id)
	{
		$this->page_title = 'Books von '.$user->id2user($user_id);
		$this->page_link = $this->page_link . '?do=show&book_id=' . $book_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * Book not found / invalid Book ID
	 *
	 * @version 1.0
	 * @since 1.0 `14.06.2020` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function notfoundBook(&$smarty, $book_id)
	{
		$this->page_title = 'Dieses Book steht nicht im Regal';
		$this->meta_description = null;
		$this->page_link = null;

		$this->assign_model_to_smarty($smarty);
	}
}
