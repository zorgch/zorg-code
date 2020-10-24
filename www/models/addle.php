<?php
/**
 * Addle Game MVC Model
 *
 * @author IneX
 * @package zorg\Games\Addle
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Addle extends Model
{
	public function __construct()
	{
		/** Page Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Addle';

		/** URL Basis ist für alle Seiten gleich */
		$this->page_link = '/addle.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = '2 Spieler, abwechslungsweise eine Zahl ziehen: Ziel des Spiels Addle ist es, im 1vs1 möglichst viele Punkte zu erzielen.';

		/** Image teaser ist für alle Seiten gleich */
		$this->page_image = SITE_URL.IMAGES_DIR.'addle.jpg';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'games', 'addle' ];
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
	public function showGame(&$smarty, $game_id)
	{
		$this->page_title = sprintf('%s Game #%d', $this->page_title, $game_id);
		$this->page_link = $this->page_link . '?show=play&id=' . $game_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showHowto(&$smarty)
	{
		$this->page_title = $this->page_title . ' Howto';
		$this->page_link = $this->page_link . '?show=howto';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showHighscore(&$smarty)
	{
		$this->page_title = $this->page_title . ' Highscores';
		$this->page_link = $this->page_link . '?show=highscore';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showDwz(&$smarty)
	{
		$this->page_title = $this->page_title . ' DWZ';
		$this->page_link = $this->page_link . '?show=dwz';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showArchive(&$smarty)
	{
		$this->page_title = $this->page_title . ' Games Archive and Stats';
		$this->page_link = $this->page_link . '?show=archive';

		$this->assign_model_to_smarty($smarty);
	}
}
