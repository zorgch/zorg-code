<?php
/**
 * Pimp MVC Model
 *
 * @author IneX
 * @package zorg\Games\Pimp
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Pimp extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'zorg Pimp Name Generator';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/pimp.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Want to be cool? No ideas for a great zorg Username? Pimp up your name here!';

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
