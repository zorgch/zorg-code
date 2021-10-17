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
	 * @version 1.0
	 * @since 1.0 `19.08.2021` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 * @param integer|null $book_id Book ID that throws notFound error
	 */
	public function notFound(&$smarty, $book_id=null)
	{
		$this->page_title = (!empty($book_id) ? sprintf('Book ID #%d existiert nicht', $book_id) : 'Das gesuchte Buch führen wir nicht');
		$this->meta_description = null;
		$this->page_link = null;

		$this->assign_model_to_smarty($smarty);
	}
}
