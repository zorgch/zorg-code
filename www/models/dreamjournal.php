<?php
/**
 * Dreamjournal MVC Model
 *
 * @author IneX
 * @package zorg\Dreamjournal
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Dreamjournal extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Dreamjournal';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/dreamjournal.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Dream journal for the way to reach lucid dreams';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'user' ];
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showOverview(&$smarty)
	{
		$this->assign_model_to_smarty($smarty);
	}
}
