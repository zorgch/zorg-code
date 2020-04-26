<?php
/**
 * Bugtracker MVC Model
 *
 * @author IneX
 * @package zorg\Bugtracker
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Bugtracker extends Model
{
	public function __construct()
	{
		global $user;

		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Bugtracker';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/bugtracker.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'zorg Bug und Feature Requests melden';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg' ];
		if ($user->is_loggedin()) array_push($this->menus, 'utilities');
		if ($user->typ == USER_MEMBER) array_push($this->menus, 'admin');
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
	public function showBug(&$smarty, $bug_id, $bug_resource=null)
	{
		$this->page_title = (!empty($bug_resource) ? sprintf('Bug #%d «%s»', $bug_id, text_width(remove_html($bug_resource['title']), 50, '…', true)) : 'Bug #'.$bug_id);
		$this->meta_description = text_width(remove_html($bug_resource['description']), 150, '…', true, true);
		$this->page_link = '/bug/'.$bug_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showEdit(&$smarty, $bug_id)
	{
		$this->page_title = 'Bug #'.$bug_id.' bearbeiten';
		$this->page_link = $this->page_link . '?do=edit&book_id=' . $book_id;

		$this->assign_model_to_smarty($smarty);
	}
}
