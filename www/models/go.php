<?php
/**
 * Go Game MVC Model
 *
 * @author IneX
 * @package zorg\Games\Go
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Go extends Model
{
	public function __construct()
	{
		/** Page Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Go';

		/** URL Basis ist für alle Seiten gleich */
		$this->page_link = '/go.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Go kann man in 5 Minuten lernen, aber es dauert ein Leben bis man darin zum Meister wird. Zeit zu üben!';

		/** Image teaser ist für alle Seiten gleich */
		$this->page_image = SITE_URL.IMAGES_DIR.'go/zorg_go.jpg';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'games', 'go' ];
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
}
