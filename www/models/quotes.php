<?php
/**
 * Quotes MVC Model
 *
 * @author IneX
 * @package zorg\Quotes
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Quotes extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Quotes';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/quotes.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'A quote a day keeps the doctor away! Teile deine Lebensweisheiten mit uns.';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'user', 'quotes' ];
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showOverview(&$smarty, &$user, $user_id, $curr_pagination)
	{
		$quotes_pagesuffix = (!empty($curr_pagination) && $curr_pagination > 0 ? ' (page '.$curr_pagination.')' : '');
		$quotes_usersuffix = (!empty($user_id) ? ' von '.$user->id2user($user_id, true) : '');
		$this->page_title = 'Quotes' . $quotes_pagesuffix . $quotes_usersuffix;
		$this->page_link = $this->page_link . (!empty($curr_pagination) && $curr_pagination > 0 ? '?site='.$curr_pagination : '');

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showAddnew(&$smarty)
	{
		$this->page_title = 'Neuen Quote hinzufügen';
		$this->page_link = $this->page_link . '?do=add';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showDelete(&$smarty, $quote_id)
	{
		$this->page_title = 'Quote #'.$quote_id.' löschen';
		$this->page_link = $this->page_link . '?do=delete&quote_id=' . $quote_id;

		$this->assign_model_to_smarty($smarty);
	}
}
