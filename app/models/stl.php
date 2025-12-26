<?php
/**
 * Shoot the Lamber MVC Model
 *
 * @author IneX
 * @package zorg\Games\STL
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class STL extends Model
{
	public function __construct()
	{
		/** Page Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Shoot the Lamber';

		/** URL Basis ist für alle Seiten gleich */
		$this->page_link = '/stl.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Shoot the Lamber ist quasi der Multiplayer-Mode von «Schiffe versenken» (engl. Battleship) wo zwei Mannschaften gegeneinander batteln.';

		/** Image teaser ist für alle Seiten gleich */
		$this->page_image = SITE_URL.'/files/117/stl_logo.jpg';

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

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showGame(&$smarty, $game_id, $game_title)
	{
		$this->page_title = (!empty($game_title) ? sprintf('STL Game «%s»', $game_title) : sprintf('STL Game #%d', $game_id));
		$this->page_link = $this->page_link . '?do=game&game_id=' . $game_id;

		$this->assign_model_to_smarty($smarty);
	}
}
