<?php
/**
 * Frets on zorg MVC Model
 *
 * @author IneX
 * @package zorg\Games\Fretsonzorg
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Fretsonzorg extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Frets on zorg';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/fretsonzorg.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = '«Frets on Fire» meets zorg: zorg Player Highscores and FoF score upload how-to';

		/** Image teaser ist für alle Seiten gleich */
		$this->page_image = SITE_URL.IMAGES_DIR.'pose.png';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'games', 'fretsonzorg' ];
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
