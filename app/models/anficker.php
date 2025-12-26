<?php
/**
 * Anficker Game MVC Model
 *
 * @author IneX
 * @package zorg\Games\Anficker
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Anficker extends Model
{
	public function __construct()
	{
		/** Page Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Anficker Admin';

		/** URL Basis ist für alle Seiten gleich */
		$this->page_link = '/aklick.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Die krässeste KI im Web: Fick si aa und du chasch der en Psychiater sueche, scheiss grüsigä!';

		/** Image teaser ist für alle Seiten gleich */
		$this->page_image = SITE_URL.'/files/396/aficks.jpg';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'games' ];
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
