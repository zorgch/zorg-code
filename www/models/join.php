<?php
/**
 * Join zooomclan MVC Model
 *
 * @author IneX
 * @package zorg\zooomclan
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Join extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'zooomclan Beitritts Test';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Der folgende Test, von Illuminati International in Zusammenarbeit mit dem zooomclan, misst die pers&ouml;nliche Bef&auml;higung um dem zooomclan beizutreten.';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/join.php';

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
